<?php
namespace DinoTech\LangKit\Utils;

/**
 * Utility class to build up a string from quoted input. Set to conditionally
 * support escaping based on string delimiter (default is to support escape for
 * `"`).
 */
class StringBuilder {
    /** @var string */
    private $delim;
    /** @var string */
    private $buff;
    /** @var bool */
    private $inEscape;
    /** @var string */
    private $escapeChar = '\\';
    /** @var string */
    private $escapeDelim = '"';

    public function reset() {
        $this->delim = null;
        $this->buff = null;
        $this->inEscape = false;
    }

    public function setEscapeChar(string $char) : StringBuilder {
        $this->escapeChar = $char;
        return $this;
    }

    public function setEscapeDelimiter(string $delim) : StringBuilder {
        $this->escapeDelim = $delim;
        return $this;
    }

    public function start(string $delim) : StringBuilder {
        if ($delim === $this->escapeChar) {
            throw new \Exception("attempted to start string with escape char");
        }

        $this->delim = $delim;
        $this->buff = [];
        return $this;
    }

    public function push(string $chars) : StringBuilder {
        if ($this->inEscape) {
            $this->inEscape = false;
            if ($this->delim === $this->escapeDelim || $chars === $this->delim) {
                // @todo support transforming escaped char
                $this->buff[] = $chars;
            } else {
                $this->buff[] = $this->escapeChar;
            }
        } else if ($chars === $this->escapeChar) {
            $this->inEscape = true;
        } else if ($chars === $this->delim) {
            $this->delim = null;
        } else {
            $this->buff[] = $chars;
        }

        return $this;
    }

    public function isStarted() {
        return $this->delim !== null;
    }

    public function isComplete() {
        return $this->delim === null;
    }

    public function getString() : string {
        return implode('', $this->buff);
    }
}
