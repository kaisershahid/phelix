<?php
namespace DinoTech\Phelix\Api\Event;

/**
 * Baseline event interface.
 */
interface EventInterface {
    public function getTopic() : string;

    public function getPayload();

    public function getPayloadType() : string;
}
