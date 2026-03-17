<?php

namespace OCP\AppFramework\Http;

class TemplateResponse {
    public function __construct(string $appName, string $templateName, array $params = [], string $renderAs = 'blank') {
    }
}
