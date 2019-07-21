<?php
namespace DinoTech\Phelix\Api\Service\Query;

use DinoTech\LangKit\ContextInterface;
use DinoTech\LangKit\PredicateInterface;
use DinoTech\LangKit\StatementInterface;

class Statement implements StatementInterface {
    /** @var PredicateInterface */
    private $predicate;
    /** @var ContextInterface */
    private $context;
    /** @var boolean */
    private $result;

    public function __construct(PredicateInterface $predicate) {
        $this->predicate = $predicate;
    }

    /**
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context) : StatementInterface {
        $this->context = $context;
        return $this;
    }

    public function executeStatement(): StatementInterface {
        $this->result = $this->predicate->executePredicate($this->context);
        return $this;
    }

    /**
     * @return bool
     */
    public function getResults() {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getResultType(): string {
        return 'bool';
    }

}
