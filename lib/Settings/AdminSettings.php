<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Settings;

use OCA\OidcGroupsMapping\Model\RuleCollection;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IAppConfig;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {

	public function __construct(
		private IAppConfig $appConfig,
		private IInitialState $initialState,
	) {
	}

	public function getForm(): TemplateResponse {
		Util::addScript('oidc_groups_mapping', 'oidc_groups_mapping-main');

		$json = $this->appConfig->getValueString('oidc_groups_mapping', 'mapping_rules', '');
		$collection = RuleCollection::fromJson($json);

		$this->initialState->provideInitialState('rules', $collection->toJson());
		$this->initialState->provideInitialState('mode', $collection->getMode());

		return new TemplateResponse('oidc_groups_mapping', 'admin', []);
	}

	public function getSection(): string {
		return 'oidc_groups_mapping';
	}

	public function getPriority(): int {
		return 50;
	}
}
