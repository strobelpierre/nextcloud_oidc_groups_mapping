<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Service;

use OCA\OidcGroupsMapping\Model\MappingResult;
use OCA\OidcGroupsMapping\Model\Rule;

class RuleEngine {

    public function __construct(
        private ClaimResolver $resolver,
    ) {
    }

    public function apply(Rule $rule, object $claims): MappingResult {
        $claimValue = $this->resolver->resolve($claims, $rule->getClaimPath());

        if ($claimValue === null) {
            return new MappingResult($rule->getId(), [], false);
        }

        $groups = match ($rule->getType()) {
            'direct' => $this->applyDirect($claimValue),
            'prefix' => $this->applyPrefix($claimValue, $rule->getConfig()),
            'map' => $this->applyMap($claimValue, $rule->getConfig()),
            'conditional' => $this->applyConditional($claimValue, $rule->getConfig()),
            'template' => $this->applyTemplate($claimValue, $rule->getConfig()),
            default => [],
        };

        return new MappingResult($rule->getId(), $groups, count($groups) > 0);
    }

    private function applyDirect(mixed $value): array {
        if (is_array($value)) {
            return array_values(array_filter($value, 'is_string'));
        }
        if (is_string($value) && $value !== '') {
            return [$value];
        }
        return [];
    }

    private function applyPrefix(mixed $value, array $config): array {
        $prefix = $config['prefix'] ?? '';
        $values = is_array($value) ? $value : [$value];

        $groups = [];
        foreach ($values as $v) {
            if (is_string($v) && $v !== '') {
                $groups[] = $prefix . $v;
            }
        }
        return $groups;
    }

    private function applyMap(mixed $value, array $config): array {
        $map = $config['values'] ?? [];
        $policy = $config['unmappedPolicy'] ?? 'ignore';
        $values = is_array($value) ? $value : [$value];

        $groups = [];
        foreach ($values as $v) {
            if (!is_string($v)) {
                continue;
            }
            if (isset($map[$v])) {
                $mapped = $map[$v];
                if (is_array($mapped)) {
                    array_push($groups, ...$mapped);
                } else {
                    $groups[] = $mapped;
                }
            } elseif ($policy === 'passthrough') {
                $groups[] = $v;
            }
        }
        return $groups;
    }

    private function applyConditional(mixed $value, array $config): array {
        $operator = $config['operator'] ?? 'equals';
        $expected = $config['value'] ?? '';
        $groups = $config['groups'] ?? [];

        $matched = match ($operator) {
            'equals' => is_string($value) && $value === $expected,
            'contains' => is_array($value) && in_array($expected, $value, true),
            'regex' => is_string($value) && @preg_match($expected, $value) === 1,
            default => false,
        };

        return $matched ? $groups : [];
    }

    private function applyTemplate(mixed $value, array $config): array {
        $template = $config['template'] ?? '{value}';
        $values = is_array($value) ? $value : [$value];

        $groups = [];
        foreach ($values as $v) {
            if (is_string($v) && $v !== '') {
                $groups[] = str_replace('{value}', $v, $template);
            }
        }
        return $groups;
    }
}
