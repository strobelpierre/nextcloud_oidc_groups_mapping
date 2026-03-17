<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Command;

use OCA\OidcGroupsMapping\Model\RuleCollection;
use OCP\IAppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListRules extends Command {

    public function __construct(
        private IAppConfig $appConfig,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('oidc-groups:list')
            ->setDescription('List all configured OIDC group mapping rules');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $json = $this->appConfig->getValueString('oidc_groups_mapping', 'mapping_rules', '');
        $collection = RuleCollection::fromJson($json);

        $rules = $collection->getRules();
        if (count($rules) === 0) {
            $output->writeln('No rules configured.');
            return 0;
        }

        $output->writeln('Mode: ' . $collection->getMode());
        $output->writeln('');

        foreach ($rules as $rule) {
            $status = $rule->isEnabled() ? 'enabled' : 'disabled';
            $output->writeln(sprintf(
                '  [%s] %s (%s) — claim: %s — config: %s',
                $status,
                $rule->getId(),
                $rule->getType(),
                $rule->getClaimPath(),
                json_encode($rule->getConfig()),
            ));
        }

        return 0;
    }
}
