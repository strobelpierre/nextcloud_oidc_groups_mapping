<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\AppInfo;

use OCA\OidcGroupsMapping\Listener\GroupsMappingListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use Psr\Log\LoggerInterface;

class Application extends App implements IBootstrap {

	public const APP_ID = 'oidc_groups_mapping';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		// Use string literal to avoid triggering autoload during registration.
		// NC will only resolve the class when the event is actually dispatched.
		$context->registerEventListener(
			'OCA\\UserOIDC\\Event\\AttributeMappedEvent',
			GroupsMappingListener::class,
		);
	}

	public function boot(IBootContext $context): void {
		$appManager = $context->getServerContainer()->get(\OCP\App\IAppManager::class);
		if (!$appManager->isEnabledForUser('user_oidc')) {
			$context->getServerContainer()->get(LoggerInterface::class)->warning(
				'oidc_groups_mapping requires user_oidc to be enabled. Group mapping will not work.'
			);
		}
	}
}
