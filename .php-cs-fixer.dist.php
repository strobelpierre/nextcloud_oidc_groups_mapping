<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

require_once './vendor-bin/cs-fixer/vendor/autoload.php';

use Nextcloud\CodingStandard\Config;

$config = new Config();
$config->getFinder()
	->ignoreVCSIgnored(true)
	->notPath('vendor')
	->notPath('tests/stubs')
	->in(__DIR__);

return $config;
