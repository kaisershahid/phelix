<?php
namespace DinoTech\Phelix\Expressions;

/**
 * Interface StatementInterface
 * @package DinoTech\Phelix\Expressions
 */
interface StatementInterface {
    public function setContext(ContextInterface $context) : StatementInterface;

    public function executeStatement() : StatementInterface;

    public function getResults();

    public function getResultType() : string;
}
