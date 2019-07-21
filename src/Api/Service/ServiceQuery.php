<?php
namespace DinoTech\Phelix\Api\Service;

use DinoTech\Phelix\Api\Config\ServiceConfig;
use DinoTech\Phelix\Api\Config\ServiceReference;
use DinoTech\Phelix\Api\Service\Query\SimpleQueryParser;
use DinoTech\Phelix\Api\Service\Query\Statement;
use DinoTech\Phelix\Expressions\StatementInterface;
use DinoTech\StdLib\Collections\ArrayUtils;

/**
 * ```
 * service.interface=name && metadata.type=something && component.label=Some Label
 * ```
 */
class ServiceQuery {
    /** @var string */
    private $query;
    /** @var StatementInterface */
    private $statement;
    /** @var string */
    private $hash;

    public function __construct(string $query, StatementInterface $statement) {
        $this->query = $query;
        $this->statement = $statement;
        $this->hash = md5(json_encode($this->query));
    }

    /**
     * @return string
     */
    public function getHash(): string {
        return $this->hash;
    }

    /**
     * @param ServiceConfig $serviceConfig
     * @return bool
     */
    public function matchByConfig(ServiceConfig $serviceConfig) {
        $cfg = $serviceConfig->jsonSerialize();
        return $this->statement
            ->setContext($cfg)
            ->executeStatement()
            ->getResults();
    }

    public static function fromReference(ServiceReference $ref) : ServiceQuery {
        $query = '';
        if ($ref->getInterface()) {
            $query = 'service.interface == "' . json_encode($ref->getInterface()) . '"';
        }

        if ($ref->getQuery()) {
            if ($query) {
                $query .= ' && (' . $ref->getQuery() . ')';
            } else {
                $query = $ref->getQuery();
            }
        }

        if (empty(trim($query))) {
            throw new \RuntimeException("empty query from reference");
        }

        return new static($query, (new SimpleQueryParser($query))->getStatement());
    }

    public static function fromInterface(string $interface) : ServiceQuery {
        $query = 'service.interface == "' . json_encode($interface) . '"';
        return new static($query, (new SimpleQueryParser($query))->getStatement());
    }
}
