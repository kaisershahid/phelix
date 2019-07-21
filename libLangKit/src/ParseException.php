<?php
namespace DinoTech\LangKit;

class ParseException extends \Exception {
    /** @var int */
    private $lineNum = -1;
    /** @var int */
    private $colNum = -1;
    /** @var string */
    private $fragment;

    /**
     * @return int
     */
    public function getLineNum(): int {
        return $this->lineNum;
    }

    /**
     * @param int $line
     * @return ParseException
     */
    public function setLineNum(int $lineNum): ParseException {
        $this->lineNum = $lineNum;
        return $this;
    }

    /**
     * @return int
     */
    public function getColNum(): int {
        return $this->colNum;
    }

    /**
     * @param int $colNum
     * @return ParseException
     */
    public function setColNum(int $colNum): ParseException {
        $this->colNum = $colNum;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment(): string {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     * @return ParseException
     */
    public function setFragment(string $fragment): ParseException {
        $this->fragment = $fragment;
        return $this;
    }
}
