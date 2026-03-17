<?php

namespace OCP;

interface IAppConfig {
    public function getValueString(string $app, string $key, string $default = '', bool $lazy = false): string;
    public function setValueString(string $app, string $key, string $value, bool $lazy = false, bool $sensitive = false): bool;
}
