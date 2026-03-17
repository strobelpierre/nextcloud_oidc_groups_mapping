<?php

namespace OCP\AppFramework\Bootstrap;

interface IRegistrationContext {
    public function registerEventListener(string $event, string $listener, int $priority = 0): void;
}
