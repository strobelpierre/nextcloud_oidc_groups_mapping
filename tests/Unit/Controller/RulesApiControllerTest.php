<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Tests\Unit\Controller;

use OCA\OidcGroupsMapping\Controller\RulesApiController;
use OCP\IAppConfig;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class RulesApiControllerTest extends TestCase {

	private RulesApiController $controller;
	private IAppConfig $appConfig;

	protected function setUp(): void {
		$this->appConfig = $this->createMock(IAppConfig::class);
		$request = $this->createMock(IRequest::class);
		$this->controller = new RulesApiController($request, $this->appConfig);
	}

	public function testIndexReturnsEmptyCollection(): void {
		$this->appConfig->method('getValueString')
			->willReturn('');

		$response = $this->controller->index();

		$data = $response->getData();
		self::assertSame(1, $data['version']);
		self::assertSame('additive', $data['mode']);
		self::assertSame([], $data['rules']);
		self::assertSame(200, $response->getStatus());
	}

	public function testIndexReturnsExistingRules(): void {
		$json = json_encode([
			'version' => 1,
			'mode' => 'replace',
			'rules' => [
				[
					'id' => 'r1',
					'type' => 'direct',
					'enabled' => true,
					'claimPath' => 'department',
					'config' => [],
				],
			],
		]);

		$this->appConfig->method('getValueString')
			->willReturn($json);

		$response = $this->controller->index();
		$data = $response->getData();

		self::assertSame(1, $data['version']);
		self::assertSame('replace', $data['mode']);
		self::assertCount(1, $data['rules']);
		self::assertSame('r1', $data['rules'][0]['id']);
		self::assertSame('direct', $data['rules'][0]['type']);
	}

	public function testUpdateSavesValidRules(): void {
		$rules = json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				[
					'id' => 'test',
					'type' => 'prefix',
					'enabled' => true,
					'claimPath' => 'roles',
					'config' => ['prefix' => 'role_'],
				],
			],
		]);

		$this->appConfig->expects(self::once())
			->method('setValueString')
			->with('oidc_groups_mapping', 'mapping_rules', self::isType('string'));

		$response = $this->controller->update($rules);
		$data = $response->getData();

		self::assertSame(200, $response->getStatus());
		self::assertSame('additive', $data['mode']);
		self::assertCount(1, $data['rules']);
		self::assertSame(1, $data['rules_count']);
		self::assertSame(1, $data['enabled_count']);
	}

	public function testUpdateRejectsInvalidJson(): void {
		$response = $this->controller->update('not json{{{');

		self::assertSame(400, $response->getStatus());
		self::assertSame('Invalid JSON', $response->getData()['message']);
	}

	public function testUpdateSkipsInvalidRules(): void {
		$rules = json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				[
					'id' => 'valid',
					'type' => 'direct',
					'enabled' => true,
					'claimPath' => 'dept',
					'config' => [],
				],
				[
					'type' => 'unknown_type',
					'claimPath' => 'x',
				],
			],
		]);

		$this->appConfig->expects(self::once())
			->method('setValueString');

		$response = $this->controller->update($rules);
		$data = $response->getData();

		self::assertSame(200, $response->getStatus());
		self::assertCount(1, $data['rules']);
		self::assertSame('valid', $data['rules'][0]['id']);
	}

	public function testUpdateWithDisabledRule(): void {
		$rules = json_encode([
			'version' => 1,
			'mode' => 'replace',
			'rules' => [
				[
					'id' => 'r1',
					'type' => 'direct',
					'enabled' => true,
					'claimPath' => 'a',
					'config' => [],
				],
				[
					'id' => 'r2',
					'type' => 'prefix',
					'enabled' => false,
					'claimPath' => 'b',
					'config' => ['prefix' => 'p_'],
				],
			],
		]);

		$this->appConfig->expects(self::once())
			->method('setValueString');

		$response = $this->controller->update($rules);
		$data = $response->getData();

		self::assertSame(2, $data['rules_count']);
		self::assertSame(1, $data['enabled_count']);
	}
}
