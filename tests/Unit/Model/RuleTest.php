<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Tests\Unit\Model;

use OCA\OidcGroupsMapping\Model\Rule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase {

	public function testConstructValidDirect(): void {
		$rule = Rule::fromArray([
			'id' => 'test-rule',
			'type' => 'direct',
			'enabled' => true,
			'claimPath' => 'department',
			'config' => [],
		]);

		$this->assertSame('test-rule', $rule->getId());
		$this->assertSame('direct', $rule->getType());
		$this->assertTrue($rule->isEnabled());
		$this->assertSame('department', $rule->getClaimPath());
		$this->assertSame([], $rule->getConfig());
	}

	public function testConstructValidPrefix(): void {
		$rule = Rule::fromArray([
			'id' => 'prefix-rule',
			'type' => 'prefix',
			'enabled' => true,
			'claimPath' => 'roles',
			'config' => ['prefix' => 'role_'],
		]);

		$this->assertSame('prefix', $rule->getType());
		$this->assertSame(['prefix' => 'role_'], $rule->getConfig());
	}

	public function testConstructValidMap(): void {
		$rule = Rule::fromArray([
			'id' => 'map-rule',
			'type' => 'map',
			'enabled' => true,
			'claimPath' => 'domain',
			'config' => [
				'values' => ['example.com' => 'Staff'],
				'unmappedPolicy' => 'ignore',
			],
		]);

		$this->assertSame('map', $rule->getType());
	}

	public function testConstructValidConditional(): void {
		$rule = Rule::fromArray([
			'id' => 'cond-rule',
			'type' => 'conditional',
			'enabled' => true,
			'claimPath' => 'userType',
			'config' => [
				'operator' => 'equals',
				'value' => 'EXTERNAL',
				'groups' => ['External-Users'],
			],
		]);

		$this->assertSame('conditional', $rule->getType());
	}

	public function testConstructValidTemplate(): void {
		$rule = Rule::fromArray([
			'id' => 'tpl-rule',
			'type' => 'template',
			'enabled' => true,
			'claimPath' => 'department',
			'config' => ['template' => 'dept_{value}'],
		]);

		$this->assertSame('template', $rule->getType());
	}

	public function testUnknownTypeThrowsException(): void {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Unknown rule type');

		Rule::fromArray([
			'id' => 'bad',
			'type' => 'unknown_type',
			'enabled' => true,
			'claimPath' => 'foo',
			'config' => [],
		]);
	}

	public function testMissingIdThrowsException(): void {
		$this->expectException(\InvalidArgumentException::class);

		Rule::fromArray([
			'type' => 'direct',
			'enabled' => true,
			'claimPath' => 'foo',
			'config' => [],
		]);
	}

	public function testMissingClaimPathThrowsException(): void {
		$this->expectException(\InvalidArgumentException::class);

		Rule::fromArray([
			'id' => 'test',
			'type' => 'direct',
			'enabled' => true,
			'config' => [],
		]);
	}

	public function testDisabledPreserved(): void {
		$rule = Rule::fromArray([
			'id' => 'disabled-rule',
			'type' => 'direct',
			'enabled' => false,
			'claimPath' => 'department',
			'config' => [],
		]);

		$this->assertFalse($rule->isEnabled());
	}

	public function testDefaultEnabledIsTrue(): void {
		$rule = Rule::fromArray([
			'id' => 'default-enabled',
			'type' => 'direct',
			'claimPath' => 'department',
			'config' => [],
		]);

		$this->assertTrue($rule->isEnabled());
	}

	public function testToArray(): void {
		$data = [
			'id' => 'test',
			'type' => 'direct',
			'enabled' => true,
			'claimPath' => 'department',
			'config' => [],
		];
		$rule = Rule::fromArray($data);
		$this->assertSame($data, $rule->toArray());
	}
}
