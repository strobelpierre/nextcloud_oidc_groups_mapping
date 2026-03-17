<?php

namespace OCP\AppFramework;

class App {
    public function __construct(string $appName, array $urlParams = []) {
    }

    public function getContainer() {
        return new class {
            public function get(string $class) {
                return null;
            }
        };
    }
}
