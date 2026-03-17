<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Model;

class RuleCollection {

	/**
	 * @param Rule[] $rules
	 */
	private function __construct(
		private int $version,
		private string $mode,
		private array $rules,
	) {
	}

	public static function fromJson(string $json): self {
		if ($json === '') {
			return new self(1, 'additive', []);
		}

		$data = json_decode($json, true);
		if (!is_array($data)) {
			return new self(1, 'additive', []);
		}

		$rules = [];
		foreach ($data['rules'] ?? [] as $ruleData) {
			try {
				$rules[] = Rule::fromArray($ruleData);
			} catch (\InvalidArgumentException) {
				// Skip invalid rules
				continue;
			}
		}

		return new self(
			$data['version'] ?? 1,
			$data['mode'] ?? 'additive',
			$rules,
		);
	}

	/**
	 * @return Rule[]
	 */
	public function getRules(): array {
		return $this->rules;
	}

	/**
	 * @return Rule[]
	 */
	public function getEnabledRules(): array {
		return array_values(array_filter(
			$this->rules,
			fn (Rule $rule) => $rule->isEnabled(),
		));
	}

	public function getMode(): string {
		return $this->mode;
	}

	public function getVersion(): int {
		return $this->version;
	}

	public function toJson(): string {
		return (string)json_encode([
			'version' => $this->version,
			'mode' => $this->mode,
			'rules' => array_map(fn (Rule $r) => $r->toArray(), $this->rules),
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
}
