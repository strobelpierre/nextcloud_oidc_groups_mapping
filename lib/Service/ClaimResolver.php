<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Service;

class ClaimResolver {

	/**
	 * Resolve a claim path in the claims object.
	 *
	 * Supports:
	 * - Direct property names: "department"
	 * - Dot-notation for nested objects: "extended_attributes.domain"
	 * - URL-style claim keys: "https://idp.example.com/claims/domain"
	 * - Mixed: "https://idp.example.com/claims/extended_attributes.auth.permissions"
	 *
	 * Strategy: try the full key first (handles URL claims), then progressively
	 * split on dots from left to right to find the deepest match.
	 *
	 * @param object $claims The token claims object
	 * @param string $path Claim path
	 * @return mixed The resolved value, or null if the path doesn't exist
	 */
	public function resolve(object $claims, string $path): mixed {
		if ($path === '') {
			return null;
		}

		// Try direct property match first (handles URL-style keys with dots)
		if ($this->hasProperty($claims, $path)) {
			return $this->getProperty($claims, $path);
		}

		// Progressive split: try splitting on each dot from left to right
		$dotPositions = $this->findDotPositions($path);
		foreach ($dotPositions as $pos) {
			$head = substr($path, 0, $pos);
			$tail = substr($path, $pos + 1);

			if ($this->hasProperty($claims, $head)) {
				$child = $this->getProperty($claims, $head);
				if ($tail === '') {
					return $child;
				}
				if (is_object($child)) {
					$result = $this->resolve($child, $tail);
					if ($result !== null) {
						return $result;
					}
				}
			}
		}

		return null;
	}

	/**
	 * @return int[] positions of dots in the string
	 */
	private function findDotPositions(string $path): array {
		$positions = [];
		$offset = 0;
		while (($pos = strpos($path, '.', $offset)) !== false) {
			$positions[] = $pos;
			$offset = $pos + 1;
		}
		return $positions;
	}

	private function hasProperty(object|array $current, string $key): bool {
		if (is_object($current)) {
			return property_exists($current, $key);
		}
		return array_key_exists($key, $current);
	}

	private function getProperty(object|array $current, string $key): mixed {
		if (is_object($current)) {
			return $current->$key;
		}
		return $current[$key];
	}
}
