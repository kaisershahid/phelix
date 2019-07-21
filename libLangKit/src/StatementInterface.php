<?php
namespace DinoTech\LangKit;

/**
 * Executes a compiled statement.
 */
interface StatementInterface {
    public function setContext(ContextInterface $context) : StatementInterface;

    public function executeStatement() : StatementInterface;

    public function getResults();

    public function getResultType() : string;
}
