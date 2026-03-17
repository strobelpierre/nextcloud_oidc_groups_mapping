<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Settings;

use OCA\OidcGroupsMapping\Model\RuleCollection;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {

	public function __construct(
		private IAppConfig $appConfig,
	) {
	}

	public function getForm(): TemplateResponse {
		$json = $this->appConfig->getValueString('oidc_groups_mapping', 'mapping_rules', '');
		$collection = RuleCollection::fromJson($json);

		return new TemplateResponse('oidc_groups_mapping', 'admin', [
			'rules_json' => $collection->toJson(),
			'mode' => $collection->getMode(),
			'rules_count' => count($collection->getRules()),
		]);
	}

	public function getSection(): string {
		return 'oidc_groups_mapping';
	}

	public function getPriority(): int {
		return 50;
	}
}
