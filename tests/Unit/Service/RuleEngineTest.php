<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Tests\Unit\Service;

use OCA\OidcGroupsMapping\Model\Rule;
use OCA\OidcGroupsMapping\Service\ClaimResolver;
use OCA\OidcGroupsMapping\Service\RuleEngine;
use PHPUnit\Framework\TestCase;

class RuleEngineTest extends TestCase {

	private RuleEngine $engine;
	private ClaimResolver $resolver;

	protected function setUp(): void {
		$this->resolver = new ClaimResolver();
		$this->engine = new RuleEngine($this->resolver);
	}

	// === Direct ===

	public function testDirectString(): void {
		$rule = Rule::fromArray([
			'id' => 'dept', 'type' => 'direct', 'enabled' => true,
			'claimPath' => 'department', 'config' => [],
		]);
		$claims = (object)['department' => 'IT'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['IT'], $result->getGroups());
	}

	public function testDirectArray(): void {
		$rule = Rule::fromArray([
			'id' => 'groups', 'type' => 'direct', 'enabled' => true,
			'claimPath' => 'groups', 'config' => [],
		]);
		$claims = (object)['groups' => ['staff', 'admin']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['staff', 'admin'], $result->getGroups());
	}

	public function testDirectNull(): void {
		$rule = Rule::fromArray([
			'id' => 'missing', 'type' => 'direct', 'enabled' => true,
			'claimPath' => 'nonexistent', 'config' => [],
		]);
		$claims = (object)['department' => 'IT'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	// === Prefix ===

	public function testPrefixArray(): void {
		$rule = Rule::fromArray([
			'id' => 'roles', 'type' => 'prefix', 'enabled' => true,
			'claimPath' => 'roles', 'config' => ['prefix' => 'role_'],
		]);
		$claims = (object)['roles' => ['admin', 'editor']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['role_admin', 'role_editor'], $result->getGroups());
	}

	public function testPrefixString(): void {
		$rule = Rule::fromArray([
			'id' => 'dept', 'type' => 'prefix', 'enabled' => true,
			'claimPath' => 'department', 'config' => ['prefix' => 'dept_'],
		]);
		$claims = (object)['department' => 'IT'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['dept_IT'], $result->getGroups());
	}

	public function testPrefixNull(): void {
		$rule = Rule::fromArray([
			'id' => 'roles', 'type' => 'prefix', 'enabled' => true,
			'claimPath' => 'missing', 'config' => ['prefix' => 'role_'],
		]);
		$claims = (object)[];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	// === Map ===

	public function testMapKnownValue(): void {
		$rule = Rule::fromArray([
			'id' => 'org', 'type' => 'map', 'enabled' => true,
			'claimPath' => 'domain',
			'config' => [
				'values' => ['example.com' => 'Staff', 'partner.com' => 'Partners'],
				'unmappedPolicy' => 'ignore',
			],
		]);
		$claims = (object)['domain' => 'example.com'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['Staff'], $result->getGroups());
	}

	public function testMapUnknownIgnore(): void {
		$rule = Rule::fromArray([
			'id' => 'org', 'type' => 'map', 'enabled' => true,
			'claimPath' => 'domain',
			'config' => [
				'values' => ['example.com' => 'Staff'],
				'unmappedPolicy' => 'ignore',
			],
		]);
		$claims = (object)['domain' => 'unknown.com'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	public function testMapUnknownPassthrough(): void {
		$rule = Rule::fromArray([
			'id' => 'org', 'type' => 'map', 'enabled' => true,
			'claimPath' => 'domain',
			'config' => [
				'values' => ['example.com' => 'Staff'],
				'unmappedPolicy' => 'passthrough',
			],
		]);
		$claims = (object)['domain' => 'unknown.com'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['unknown.com'], $result->getGroups());
	}

	public function testMapArrayValues(): void {
		$rule = Rule::fromArray([
			'id' => 'org', 'type' => 'map', 'enabled' => true,
			'claimPath' => 'domains',
			'config' => [
				'values' => ['a.com' => 'GroupA', 'b.com' => 'GroupB'],
				'unmappedPolicy' => 'ignore',
			],
		]);
		$claims = (object)['domains' => ['a.com', 'b.com', 'c.com']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['GroupA', 'GroupB'], $result->getGroups());
	}

	// === Conditional ===

	public function testConditionalEqualsMatch(): void {
		$rule = Rule::fromArray([
			'id' => 'ext', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'userType',
			'config' => [
				'operator' => 'equals',
				'value' => 'EXTERNAL',
				'groups' => ['External-Users'],
			],
		]);
		$claims = (object)['userType' => 'EXTERNAL'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['External-Users'], $result->getGroups());
	}

	public function testConditionalEqualsNoMatch(): void {
		$rule = Rule::fromArray([
			'id' => 'ext', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'userType',
			'config' => [
				'operator' => 'equals',
				'value' => 'EXTERNAL',
				'groups' => ['External-Users'],
			],
		]);
		$claims = (object)['userType' => 'INTERNAL'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	public function testConditionalContainsMatch(): void {
		$rule = Rule::fromArray([
			'id' => 'admin', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'roles',
			'config' => [
				'operator' => 'contains',
				'value' => 'admin',
				'groups' => ['Administrators'],
			],
		]);
		$claims = (object)['roles' => ['admin', 'editor']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['Administrators'], $result->getGroups());
	}

	public function testConditionalContainsNoMatch(): void {
		$rule = Rule::fromArray([
			'id' => 'admin', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'roles',
			'config' => [
				'operator' => 'contains',
				'value' => 'admin',
				'groups' => ['Administrators'],
			],
		]);
		$claims = (object)['roles' => ['editor', 'viewer']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	public function testConditionalRegexMatch(): void {
		$rule = Rule::fromArray([
			'id' => 'domain', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'email',
			'config' => [
				'operator' => 'regex',
				'value' => '/@example\\.com$/',
				'groups' => ['Example-Staff'],
			],
		]);
		$claims = (object)['email' => 'alice@example.com'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['Example-Staff'], $result->getGroups());
	}

	public function testConditionalRegexNoMatch(): void {
		$rule = Rule::fromArray([
			'id' => 'domain', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'email',
			'config' => [
				'operator' => 'regex',
				'value' => '/@example\\.com$/',
				'groups' => ['Example-Staff'],
			],
		]);
		$claims = (object)['email' => 'alice@other.com'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	// === Template ===

	public function testTemplateSimple(): void {
		$rule = Rule::fromArray([
			'id' => 'dept', 'type' => 'template', 'enabled' => true,
			'claimPath' => 'department',
			'config' => ['template' => 'dept_{value}'],
		]);
		$claims = (object)['department' => 'IT'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['dept_IT'], $result->getGroups());
	}

	public function testTemplateArray(): void {
		$rule = Rule::fromArray([
			'id' => 'roles', 'type' => 'template', 'enabled' => true,
			'claimPath' => 'roles',
			'config' => ['template' => 'role-{value}'],
		]);
		$claims = (object)['roles' => ['admin', 'editor']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['role-admin', 'role-editor'], $result->getGroups());
	}

	public function testTemplateNull(): void {
		$rule = Rule::fromArray([
			'id' => 'dept', 'type' => 'template', 'enabled' => true,
			'claimPath' => 'missing',
			'config' => ['template' => 'dept_{value}'],
		]);
		$claims = (object)[];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	// === Edge cases ===

	public function testConditionalMalformedRegexDoesNotCrash(): void {
		$rule = Rule::fromArray([
			'id' => 'bad-regex', 'type' => 'conditional', 'enabled' => true,
			'claimPath' => 'email',
			'config' => [
				'operator' => 'regex',
				'value' => '/[invalid regex(',
				'groups' => ['Should-Not-Match'],
			],
		]);
		$claims = (object)['email' => 'alice@example.com'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	public function testDirectEmptyString(): void {
		$rule = Rule::fromArray([
			'id' => 'empty', 'type' => 'direct', 'enabled' => true,
			'claimPath' => 'department', 'config' => [],
		]);
		$claims = (object)['department' => ''];

		$result = $this->engine->apply($rule, $claims);
		$this->assertFalse($result->isMatched());
		$this->assertSame([], $result->getGroups());
	}

	public function testDirectArrayFiltersNonStrings(): void {
		$rule = Rule::fromArray([
			'id' => 'mixed', 'type' => 'direct', 'enabled' => true,
			'claimPath' => 'groups', 'config' => [],
		]);
		$claims = (object)['groups' => ['valid', 42, null, 'also-valid']];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['valid', 'also-valid'], $result->getGroups());
	}

	public function testMapMultipleGroupsForOneValue(): void {
		$rule = Rule::fromArray([
			'id' => 'multi', 'type' => 'map', 'enabled' => true,
			'claimPath' => 'role',
			'config' => [
				'values' => ['admin' => ['Admins', 'Superusers']],
				'unmappedPolicy' => 'ignore',
			],
		]);
		$claims = (object)['role' => 'admin'];

		$result = $this->engine->apply($rule, $claims);
		$this->assertTrue($result->isMatched());
		$this->assertSame(['Admins', 'Superusers'], $result->getGroups());
	}
}
