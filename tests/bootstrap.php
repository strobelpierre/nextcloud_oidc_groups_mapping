<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Register stub autoloader for OCP/OCA classes not provided by composer
spl_autoload_register(function (string $class): void {
    $stubDir = __DIR__ . '/stubs/';
    $file = $stubDir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
