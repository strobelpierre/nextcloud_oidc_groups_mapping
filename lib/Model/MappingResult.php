<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Model;

class MappingResult {

	/**
	 * @param string[] $groups
	 */
	public function __construct(
		private string $ruleId,
		private array $groups,
		private bool $matched,
	) {
	}

	public function getRuleId(): string {
		return $this->ruleId;
	}

	/**
	 * @return string[]
	 */
	public function getGroups(): array {
		return $this->groups;
	}

	public function isMatched(): bool {
		return $this->matched;
	}
}
