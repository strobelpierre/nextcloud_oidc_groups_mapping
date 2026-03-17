<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Listener;

use OCA\OidcGroupsMapping\Service\MappingService;
use OCA\UserOIDC\Event\AttributeMappedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Psr\Log\LoggerInterface;

class GroupsMappingListener implements IEventListener {

	public function __construct(
		private MappingService $mappingService,
		private LoggerInterface $logger,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof AttributeMappedEvent)) {
			return;
		}

		if ($event->getAttribute() !== 'mappingGroups') {
			return;
		}

		$existingValue = $event->getValue();
		$existingGroups = [];
		if ($existingValue !== null && $existingValue !== '') {
			$decoded = json_decode($existingValue, true);
			if (is_array($decoded)) {
				$existingGroups = $decoded;
			}
		}

		$claims = $event->getClaims();
		$result = $this->mappingService->process($claims, $existingGroups);

		if ($result === null) {
			// No rules matched — don't touch the event
			return;
		}

		$this->logger->debug('OIDC groups mapping produced {count} groups', [
			'count' => count($result),
		]);

		$event->setValue(json_encode(array_values($result)));
		$event->stopPropagation();
	}
}
