<?php
namespace DinoTech\Phelix\Api\Event;

interface EventInterface {
    public function getTopic();

    public function getPayload();

    public function getPayloadType();
}
