<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Service;

use OCA\OidcGroupsMapping\Model\RuleCollection;
use OCP\IAppConfig;
use Psr\Log\LoggerInterface;

class MappingService {

    public function __construct(
        private IAppConfig $appConfig,
        private RuleEngine $engine,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Process claims and produce group list.
     *
     * @param object $claims The OIDC token claims
     * @param string[] $existingGroups Currently assigned groups from the original claim
     * @return string[]|null New group list, or null if no rules applied (let default handling proceed)
     */
    public function process(object $claims, array $existingGroups): ?array {
        $json = $this->appConfig->getValueString('oidc_groups_mapping', 'mapping_rules', '');
        $collection = RuleCollection::fromJson($json);

        $enabledRules = $collection->getEnabledRules();
        if (count($enabledRules) === 0) {
            return null;
        }

        $producedGroups = [];
        foreach ($enabledRules as $rule) {
            $result = $this->engine->apply($rule, $claims);
            if ($result->isMatched()) {
                $this->logger->debug('Rule "{ruleId}" matched, produced groups: {groups}', [
                    'ruleId' => $result->getRuleId(),
                    'groups' => implode(', ', $result->getGroups()),
                ]);
                array_push($producedGroups, ...$result->getGroups());
            }
        }

        $mode = $collection->getMode();

        if ($mode === 'replace') {
            // Safety: if all rules produced nothing, fallback to existing
            if (count($producedGroups) === 0) {
                $this->logger->warning('Replace mode produced empty result, falling back to existing groups');
                return $existingGroups;
            }
            return array_values(array_unique($producedGroups));
        }

        // Additive mode: merge with existing
        $merged = array_merge($existingGroups, $producedGroups);
        return array_values(array_unique($merged));
    }
}
