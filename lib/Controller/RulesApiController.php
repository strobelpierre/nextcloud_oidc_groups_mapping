<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Controller;

use OCA\OidcGroupsMapping\Model\RuleCollection;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IAppConfig;
use OCP\IRequest;

class RulesApiController extends OCSController {

	public function __construct(
		IRequest $request,
		private IAppConfig $appConfig,
	) {
		parent::__construct('oidc_groups_mapping', $request);
	}

	/**
	 * Get the current mapping rules.
	 */
	public function index(): DataResponse {
		$json = $this->appConfig->getValueString('oidc_groups_mapping', 'mapping_rules', '');
		$collection = RuleCollection::fromJson($json);

		return new DataResponse([
			'version' => $collection->getVersion(),
			'mode' => $collection->getMode(),
			'rules' => array_map(fn ($r) => $r->toArray(), $collection->getRules()),
		]);
	}

	/**
	 * Save mapping rules.
	 */
	public function update(string $rules): DataResponse {
		$decoded = json_decode($rules, true);
		if (!is_array($decoded)) {
			return new DataResponse(
				['message' => 'Invalid JSON'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$collection = RuleCollection::fromJson($rules);
		$canonical = $collection->toJson();

		$this->appConfig->setValueString('oidc_groups_mapping', 'mapping_rules', $canonical);

		return new DataResponse([
			'version' => $collection->getVersion(),
			'mode' => $collection->getMode(),
			'rules' => array_map(fn ($r) => $r->toArray(), $collection->getRules()),
			'rules_count' => count($collection->getRules()),
			'enabled_count' => count($collection->getEnabledRules()),
		]);
	}
}
