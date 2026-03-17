<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Tests\Unit\Service;

use OCA\OidcGroupsMapping\Service\ClaimResolver;
use OCA\OidcGroupsMapping\Service\MappingService;
use OCA\OidcGroupsMapping\Service\RuleEngine;
use OCP\IAppConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MappingServiceTest extends TestCase {

	private MappingService $service;
	private IAppConfig $appConfig;
	private LoggerInterface $logger;

	protected function setUp(): void {
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$resolver = new ClaimResolver();
		$engine = new RuleEngine($resolver);

		$this->service = new MappingService($this->appConfig, $engine, $this->logger);
	}

	private function configureRules(string $json): void {
		$this->appConfig->method('getValueString')
			->with('oidc_groups_mapping', 'mapping_rules', '')
			->willReturn($json);
	}

	public function testAdditiveModeMergesWithExisting(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				['id' => 'dept', 'type' => 'direct', 'enabled' => true, 'claimPath' => 'department', 'config' => []],
			],
		]));

		$claims = (object)['department' => 'IT'];
		$existing = ['staff', 'users'];

		$result = $this->service->process($claims, $existing);
		$this->assertNotNull($result);
		$this->assertContains('staff', $result);
		$this->assertContains('users', $result);
		$this->assertContains('IT', $result);
	}

	public function testReplaceModeReplacesExisting(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'replace',
			'rules' => [
				['id' => 'dept', 'type' => 'direct', 'enabled' => true, 'claimPath' => 'department', 'config' => []],
			],
		]));

		$claims = (object)['department' => 'IT'];
		$existing = ['staff', 'users'];

		$result = $this->service->process($claims, $existing);
		$this->assertNotNull($result);
		$this->assertContains('IT', $result);
		$this->assertNotContains('staff', $result);
		$this->assertNotContains('users', $result);
	}

	public function testReplaceModeEmptyFallbackToExisting(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'replace',
			'rules' => [
				['id' => 'dept', 'type' => 'direct', 'enabled' => true, 'claimPath' => 'missing_claim', 'config' => []],
			],
		]));

		$claims = (object)['department' => 'IT'];
		$existing = ['staff', 'users'];

		$result = $this->service->process($claims, $existing);
		// Empty result in replace mode → fallback to existing
		$this->assertNotNull($result);
		$this->assertSame(['staff', 'users'], $result);
	}

	public function testNoRulesReturnsNull(): void {
		$this->configureRules('');

		$claims = (object)['department' => 'IT'];
		$result = $this->service->process($claims, []);
		$this->assertNull($result);
	}

	public function testNoEnabledRulesReturnsNull(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				['id' => 'dept', 'type' => 'direct', 'enabled' => false, 'claimPath' => 'department', 'config' => []],
			],
		]));

		$claims = (object)['department' => 'IT'];
		$result = $this->service->process($claims, []);
		$this->assertNull($result);
	}

	public function testDisabledRulesSkipped(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				['id' => 'dept', 'type' => 'direct', 'enabled' => true, 'claimPath' => 'department', 'config' => []],
				['id' => 'roles', 'type' => 'prefix', 'enabled' => false, 'claimPath' => 'roles', 'config' => ['prefix' => 'role_']],
			],
		]));

		$claims = (object)['department' => 'IT', 'roles' => ['admin']];
		$result = $this->service->process($claims, []);
		$this->assertNotNull($result);
		$this->assertContains('IT', $result);
		$this->assertNotContains('role_admin', $result);
	}

	public function testDeduplication(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				['id' => 'rule1', 'type' => 'direct', 'enabled' => true, 'claimPath' => 'groups1', 'config' => []],
				['id' => 'rule2', 'type' => 'direct', 'enabled' => true, 'claimPath' => 'groups2', 'config' => []],
			],
		]));

		$claims = (object)['groups1' => ['staff', 'admin'], 'groups2' => ['admin', 'editors']];
		$result = $this->service->process($claims, ['staff']);
		$this->assertNotNull($result);
		// Should contain each group only once
		$this->assertSame(array_unique($result), $result);
		$this->assertContains('staff', $result);
		$this->assertContains('admin', $result);
		$this->assertContains('editors', $result);
	}

	public function testInvalidJsonGracefulDegradation(): void {
		$this->configureRules('not valid json {{{');

		$claims = (object)['department' => 'IT'];
		$result = $this->service->process($claims, ['staff']);
		$this->assertNull($result);
	}

	public function testMultipleRulesCombined(): void {
		$this->configureRules(json_encode([
			'version' => 1,
			'mode' => 'additive',
			'rules' => [
				['id' => 'dept', 'type' => 'template', 'enabled' => true, 'claimPath' => 'department', 'config' => ['template' => 'dept_{value}']],
				['id' => 'roles', 'type' => 'prefix', 'enabled' => true, 'claimPath' => 'roles', 'config' => ['prefix' => 'role_']],
			],
		]));

		$claims = (object)['department' => 'IT', 'roles' => ['admin']];
		$result = $this->service->process($claims, []);
		$this->assertNotNull($result);
		$this->assertContains('dept_IT', $result);
		$this->assertContains('role_admin', $result);
	}
}
