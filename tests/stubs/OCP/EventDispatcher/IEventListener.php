<?php

namespace OCP\EventDispatcher;

interface IEventListener {
    public function handle(Event $event): void;
}
