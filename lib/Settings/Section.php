<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Settings;

use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class Section implements IIconSection {

	public function __construct(
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getID(): string {
		return 'oidc_groups_mapping';
	}

	public function getName(): string {
		return 'OIDC Groups Mapping';
	}

	public function getPriority(): int {
		return 80;
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('core', 'actions/group.svg');
	}
}
