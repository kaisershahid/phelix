<?php
namespace DinoTech\StdLib;

use DinoTech\StdLib\Collections\Collection;
use DinoTech\StdLib\Collections\StandardList;
use DinoTech\StdLib\Collections\StandardMap;
use DinoTech\StdLib\Exceptions\EnumException;

/**
 * Java-style enums. One thing to call out is `__postConstruct()`, which is called
 * at the end of the constructor. Define it to carry out additional decoration
 * for the enum to support complex values (if needed).
 */
abstract class Enum {
    /** @var array list of constants to ignore when processing enums. */
    const ___IGNORE = [];

    private static $enumsCache = [];
    private static $reflectionCache = [];

    public static final function fromName($name) : Enum {
        self::checkAndLoadCache();

        $norm = strtoupper($name);
        $cls = static::class;
        if (!isset(self::$enumsCache[$cls][$norm])) {
            throw EnumException::notFound($cls, $name, array_keys(self::$enumsCache[$cls]));
        }

        return  self::$enumsCache[$cls][$norm];
    }

    public static final function values() : Collection {
        self::checkAndLoadCache();
        // @todo make read-only map impl and cache list
        return new StandardMap(self::$enumsCache[static::class]);
    }

    private static final function checkAndLoadCache() {
        $cls = static::class;
        if (!isset(self::$enumsCache[$cls])) {
            self::initializeEnums();
        }
    }

    private static final function initializeEnums() {
        $cls = static::class;
        $refl = new \ReflectionClass(static::class);
        $consts = $refl->getConstants();
        $ignore = static::___IGNORE;

        $enums = [];
        $order = 0;
        foreach ($consts as $const => $value) {
            if ($const == '___IGNORE' || array_search($const, $ignore) !== false) {
                continue;
            }

            $enums[$const] = new $cls($const, $value, $order);
            $order++;
        }

        self::$enumsCache[$cls] = $enums;
        self::$reflectionCache[$cls] = $refl;
    }

    /**
     * Enables static function calls like `Enum::NAME()` instead of `Enum::fromName('name')`.
     * Here for convenience -- always make sure your subclass provides some sort
     * of type-hinting.
     *
     * @param string $name
     * @param array $arguments
     * @return Enum
     * @throws EnumException
     */
    public static final function __callStatic(string $name, $arguments) : Enum {
        return static::fromName($name);
    }

    private $name;
    private $value;
    private $order;

    private function __construct($name, $value, $order) {
        $this->name = $name;
        $this->value = $value;
        $this->order = $order;
        $this->__postConstruct();
    }

    /**
     * Allows implementation to unpack value further if needed. Treat this as a
     * pure function -- given a particular value, this method always produces the
     * same object state.
     */
    protected function __postConstruct() {
    }

    public final function name() {
        return $this->name;
    }

    public final function value() {
        return $this->value;
    }

    public final function order() {
        return $this->order;
    }

    public final function compareRank(Enum $other) {
        $cls1 = get_class($other);
        $cls2 = static::class;
        if ($cls1 != $cls2) {
            throw new EnumException("can't compare $cls1 to $cls2");
        }

        if ($this->order() < $other->order()) {
            return -1;
        } else if ($this->order() > $other->order()) {
            return 1;
        } else {
            return 0;
        }
    }

    public final function rankedHigherThan(Enum $other) {
        return $this->compareRank($other) == 1;
    }

    public final function rankedLowerTHan(Enum $other) {
        return $this->compareRank($other) == -1;
    }
}
