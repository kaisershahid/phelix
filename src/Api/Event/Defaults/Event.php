<?php
namespace DinoTech\Phelix\Api\Event\Defaults;

use DinoTech\Phelix\Api\Event\EventInterface;

class Event implements EventInterface {
    private $topic;
    private $payload;
    private $payloadType;

    public function __construct($topic, $payload = null, $payloadType = null) {
        $this->topic = $topic;
        $this->payload = $payload;
        $this->setPayloadType($payloadType);
    }

    protected function setPayloadType($payloadType) {
        if ($payloadType !== null) {
            $this->payloadType = $payloadType;
            return;
        }

        $this->payloadType = gettype($payloadType);
    }

    public function getTopic(): string {
        return $this->topic;
    }

    public function getPayload() {
        return $this->payload;
    }

    public function getPayloadType(): string {
        return $this->payloadType;
    }

}
