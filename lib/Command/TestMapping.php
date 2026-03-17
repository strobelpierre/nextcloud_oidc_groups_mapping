<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Command;

use OCA\OidcGroupsMapping\Service\MappingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestMapping extends Command {

	public function __construct(
		private MappingService $mappingService,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('oidc-groups:test')
			->setDescription('Test mapping rules against a sample token')
			->addOption('token', 't', InputOption::VALUE_REQUIRED, 'JSON token claims')
			->addOption('existing', 'e', InputOption::VALUE_OPTIONAL, 'JSON array of existing groups', '[]');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$tokenJson = $input->getOption('token');
		if ($tokenJson === null) {
			$output->writeln('<error>--token is required</error>');
			return 1;
		}

		$claims = json_decode($tokenJson);
		if (!is_object($claims)) {
			$output->writeln('<error>Invalid JSON token</error>');
			return 1;
		}

		$existing = json_decode($input->getOption('existing') ?? '[]', true);
		if (!is_array($existing)) {
			$existing = [];
		}

		$result = $this->mappingService->process($claims, $existing);

		if ($result === null) {
			$output->writeln('No rules matched. Groups unchanged.');
			return 0;
		}

		$output->writeln('Resulting groups:');
		foreach ($result as $group) {
			$output->writeln('  - ' . $group);
		}
		return 0;
	}
}
