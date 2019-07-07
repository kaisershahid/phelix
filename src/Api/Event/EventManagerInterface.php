<?php
namespace DinoTech\Phelix\Api\Event;

interface EventManagerInterface {
    /**
     * @param EventHandlerInterface $handler The handler to register
     * @param string[] $topics A list of topics to register the handler with
     */
    public function registerEventHandler(EventHandlerInterface $handler, array $topics);

    /**
     * @param EventHandlerInterface $handler
     */
    public function unregisterEventHandler(EventHandlerInterface $handler);

    /**
     * Triggers event dispatching on given topic/payload.
     * @param string $topic
     * @param mixed $payload
     * @param string $payloadType
     */
    public function dispatch($topic, $payload = null, $payloadType = null);

    /**
     * Triggers event dispatching on passed-in event object. Manager will wrap
     * parameters in an event object.
     * @param EventInterface $event
     */
    public function dispatchEvent(EventInterface $event);
}


