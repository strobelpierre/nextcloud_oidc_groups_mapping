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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetRules extends Command {

	public function __construct(
		private IAppConfig $appConfig,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('oidc-groups:set')
			->setDescription('Set OIDC group mapping rules from JSON')
			->addArgument('json', InputArgument::REQUIRED, 'JSON configuration string');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$json = $input->getArgument('json');

		// Validate by parsing
		$collection = RuleCollection::fromJson($json);
		$rules = $collection->getRules();

		$this->appConfig->setValueString('oidc_groups_mapping', 'mapping_rules', $collection->toJson());

		$output->writeln(sprintf('Saved %d rules (mode: %s).', count($rules), $collection->getMode()));
		return 0;
	}
}
