<?php
namespace DinoTech\LangKit;

/**
 * Interface StatementInterface
 * @package DinoTech\LangKit
 */
interface StatementInterface {
    public function setContext(ContextInterface $context) : StatementInterface;

    public function executeStatement() : StatementInterface;

    public function getResults();

    public function getResultType() : string;
}
