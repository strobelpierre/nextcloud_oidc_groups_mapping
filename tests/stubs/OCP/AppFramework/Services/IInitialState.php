<?php

namespace OCP\AppFramework\Services;

interface IInitialState {
    public function provideInitialState(string $key, mixed $data): void;
}
