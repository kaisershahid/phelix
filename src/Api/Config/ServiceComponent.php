<?php
namespace DinoTech\Phelix\Api\Config;

use DinoTech\StdLib\Collections\ArrayUtils;

class ServiceComponent implements \JsonSerializable {
    const KEY_ABSTRACT = 'abstract';
    const KEY_IMMEDIATE = 'immediate';
    const KEY_ENABLED = 'enabled';
    const KEY_LABEL = 'label';
    const KEY_DESCRIPTION = 'description';
    const KEY_ACTIVATE = 'activate';
    const KEY_DEACTIVATE = 'deactivate';

    /** @var bool */
    private $abstract;
    /** @var bool */
    private $immediate;
    /** @var bool */
    private $enabled;
    /** @var string */
    private $label;
    /** @var string */
    private $description;
    /** @var string */
    private $activate;
    /** @var string */
    private $deactivate;

    public function __construct(array $arr) {
        $this->abstract = ArrayUtils::get($arr, self::KEY_ABSTRACT, false);
        $this->immediate = ArrayUtils::get($arr, self::KEY_IMMEDIATE, true);
        $this->enabled = ArrayUtils::get($arr, self::KEY_ENABLED, true);
        $this->label = ArrayUtils::get($arr, self::KEY_LABEL);
        $this->description = ArrayUtils::get($arr, self::KEY_DESCRIPTION);
        $this->activate = ArrayUtils::get($arr, self::KEY_ACTIVATE);
        $this->deactivate = ArrayUtils::get($arr, self::KEY_DEACTIVATE);
    }

    /**
     * @return bool
     */
    public function isAbstract(): bool {
        return $this->abstract;
    }

    /**
     * @return bool
     */
    public function isImmediate(): bool {
        return $this->immediate;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getActivate(): ?string {
        return $this->activate;
    }

    /**
     * @return string
     */
    public function getDeactivate(): ?string {
        return $this->deactivate;
    }

    public function jsonSerialize() {
        return [
            self::KEY_ABSTRACT => $this->abstract,
            self::KEY_IMMEDIATE => $this->immediate,
            self::KEY_ENABLED => $this->enabled,
            self::KEY_ACTIVATE => $this->activate,
            self::KEY_DEACTIVATE => $this->deactivate,
            self::KEY_LABEL => $this->label,
            self::KEY_DESCRIPTION => $this->description
        ];
    }
}
