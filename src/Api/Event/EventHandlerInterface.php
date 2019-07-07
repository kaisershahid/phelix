<?php
namespace DinoTech\Phelix\Api\Event;

interface EventHandlerInterface {
    /**
     * @param EventInterface $event
     */
    public function handleEvent(EventInterface $event);
}
