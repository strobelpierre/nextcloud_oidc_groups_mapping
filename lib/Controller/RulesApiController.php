<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Controller;

use OCA\OidcGroupsMapping\Model\RuleCollection;
use OCA\OidcGroupsMapping\Service\RuleEngine;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IAppConfig;
use OCP\IRequest;

class RulesApiController extends OCSController {

	public function __construct(
		IRequest $request,
		private IAppConfig $appConfig,
		private RuleEngine $ruleEngine,
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

	/**
	 * Simulate mapping rules against a sample token.
	 */
	public function simulate(string $token, string $existing = '[]'): DataResponse {
		$claims = json_decode($token);
		if (!is_object($claims)) {
			return new DataResponse(
				['message' => 'Invalid JSON token'],
				Http::STATUS_BAD_REQUEST,
			);
		}

		$existingGroups = json_decode($existing, true);
		if (!is_array($existingGroups)) {
			$existingGroups = [];
		}

		$rulesJson = $this->appConfig->getValueString('oidc_groups_mapping', 'mapping_rules', '');
		$collection = RuleCollection::fromJson($rulesJson);
		$enabledRules = $collection->getEnabledRules();

		$ruleResults = [];
		$producedGroups = [];

		foreach ($enabledRules as $rule) {
			$result = $this->ruleEngine->apply($rule, $claims);
			$ruleResults[] = [
				'ruleId' => $result->getRuleId(),
				'matched' => $result->isMatched(),
				'groups' => $result->getGroups(),
			];
			if ($result->isMatched()) {
				array_push($producedGroups, ...$result->getGroups());
			}
		}

		$mode = $collection->getMode();
		if ($mode === 'replace') {
			$finalGroups = count($producedGroups) === 0
				? $existingGroups
				: array_values(array_unique($producedGroups));
		} else {
			$finalGroups = array_values(array_unique(array_merge($existingGroups, $producedGroups)));
		}

		return new DataResponse([
			'mode' => $mode,
			'ruleResults' => $ruleResults,
			'producedGroups' => array_values(array_unique($producedGroups)),
			'existingGroups' => $existingGroups,
			'finalGroups' => $finalGroups,
		]);
	}
}
