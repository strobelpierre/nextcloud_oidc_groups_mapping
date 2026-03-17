<?php

namespace OCP\EventDispatcher;

class Event {
    private bool $propagationStopped = false;

    public function __construct() {
    }

    public function stopPropagation(): void {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool {
        return $this->propagationStopped;
    }
}
