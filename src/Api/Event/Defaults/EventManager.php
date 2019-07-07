<?php
namespace DinoTech\Phelix\Api\Event\Defaults;

use DinoTech\Phelix\Api\Event\EventHandlerInterface;
use DinoTech\Phelix\Api\Event\EventInterface;
use DinoTech\Phelix\Api\Event\EventManagerInterface;
use DinoTech\StdLib\Collections\ArrayUtils;
use DinoTech\StdLib\Collections\SetCollection;
use DinoTech\StdLib\Collections\StandardSet;
use DinoTech\StdLib\KeyValue;

/**
 * Boilerplate event manager. Use it when you need to drop in simple event management.
 */
class EventManager implements EventManagerInterface {
    /** @var SetCollection[] */
    private $topicsMap;

    public function __construct() {
        $this->topicsMap = [];
    }

    public function registerEventHandler(EventHandlerInterface $handler, array $topics) {
        foreach ($topics as $topic) {
            $topicSet = $this->getOrMakeTopic($topic);
            $topicSet->add($handler);
        }
    }

    protected function getOrMakeTopic($topic): SetCollection {
        if (!isset($this->topicsMap[$topic])) {
            $this->topicsMap[$topic] = (new StandardSet())->setKeyValueClass(EventKeyValue::class);
        }

        return $this->topicsMap[$topic];
    }

    public function unregisterEventHandler(EventHandlerInterface $handler) {
        foreach ($this->topicsMap as $handlers) {
            $handlers->remove($handler);
        }
    }

    public function dispatchEvent(EventInterface $event) {
        /** @var SetCollection $handlers */
        $handlers = ArrayUtils::get($this->topicsMap, $event->getTopic());
        if ($handlers === null) {
            return;
        }

        $handlers->traverse(function (EventKeyValue $kv) use ($event) {
            $kv->value()->handleEvent($event);
        });
    }

    public function dispatch($topic, $payload = null, $payloadType = null) {
        $this->dispatchEvent(new Event($topic, $payload, $payloadType));
    }
}
