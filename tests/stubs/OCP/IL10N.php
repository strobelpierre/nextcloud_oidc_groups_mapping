<?php

namespace OCP;

interface IL10N {
    public function t(string $text, $parameters = []): string;
}
