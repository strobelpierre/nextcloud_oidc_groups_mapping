<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Tests\Unit\Service;

use OCA\OidcGroupsMapping\Service\ClaimResolver;
use PHPUnit\Framework\TestCase;

class ClaimResolverTest extends TestCase {

	private ClaimResolver $resolver;

	protected function setUp(): void {
		$this->resolver = new ClaimResolver();
	}

	public function testSimpleClaim(): void {
		$claims = (object)['department' => 'IT'];
		$this->assertSame('IT', $this->resolver->resolve($claims, 'department'));
	}

	public function testNestedClaim(): void {
		$claims = (object)[
			'extended_attributes' => (object)[
				'domain' => 'example.com',
			],
		];
		$this->assertSame('example.com', $this->resolver->resolve($claims, 'extended_attributes.domain'));
	}

	public function testDeepNestedClaim(): void {
		$claims = (object)[
			'a' => (object)[
				'b' => (object)[
					'c' => 'deep_value',
				],
			],
		];
		$this->assertSame('deep_value', $this->resolver->resolve($claims, 'a.b.c'));
	}

	public function testMissingClaimReturnsNull(): void {
		$claims = (object)['department' => 'IT'];
		$this->assertNull($this->resolver->resolve($claims, 'nonexistent'));
	}

	public function testPartialPathReturnsNull(): void {
		$claims = (object)[
			'a' => (object)['b' => 'value'],
		];
		$this->assertNull($this->resolver->resolve($claims, 'a.b.c'));
	}

	public function testArrayClaim(): void {
		$claims = (object)['roles' => ['admin', 'editor']];
		$this->assertSame(['admin', 'editor'], $this->resolver->resolve($claims, 'roles'));
	}

	public function testNestedArrayClaim(): void {
		$claims = (object)[
			'extended_attributes' => (object)[
				'auth' => (object)[
					'permissions' => ['read', 'write'],
				],
			],
		];
		$result = $this->resolver->resolve($claims, 'extended_attributes.auth.permissions');
		$this->assertSame(['read', 'write'], $result);
	}

	public function testEmptyPathReturnsNull(): void {
		$claims = (object)['department' => 'IT'];
		$this->assertNull($this->resolver->resolve($claims, ''));
	}

	public function testObjectReturnedAsIs(): void {
		$inner = (object)['key' => 'val'];
		$claims = (object)['nested' => $inner];
		$this->assertSame($inner, $this->resolver->resolve($claims, 'nested'));
	}

	public function testNullValueInPath(): void {
		$claims = (object)['a' => null];
		$this->assertNull($this->resolver->resolve($claims, 'a.b'));
	}

	public function testNumericValue(): void {
		$claims = (object)['count' => 42];
		$this->assertSame(42, $this->resolver->resolve($claims, 'count'));
	}

	public function testBooleanValue(): void {
		$claims = (object)['active' => true];
		$this->assertTrue($this->resolver->resolve($claims, 'active'));
	}

	// === URL-style claim keys (namespaced IdP claims) ===

	public function testUrlStyleClaimDirect(): void {
		$claims = (object)[
			'https://idp.example.com/claims/domain' => 'org.example.corp',
		];
		$this->assertSame('org.example.corp', $this->resolver->resolve($claims, 'https://idp.example.com/claims/domain'));
	}

	public function testUrlStyleClaimWithNestedObject(): void {
		$claims = (object)[
			'https://idp.example.com/claims/extended_attributes' => (object)[
				'auth.subdomain' => 'CORP',
				'auth.permissions' => 'ADMIN',
				'user.typeOfActor' => 'CORP_OFF',
			],
		];

		$this->assertSame('CORP', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/extended_attributes.auth.subdomain'
		));
		$this->assertSame('ADMIN', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/extended_attributes.auth.permissions'
		));
		$this->assertSame('CORP_OFF', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/extended_attributes.user.typeOfActor'
		));
	}

	public function testUrlStyleClaimMissing(): void {
		$claims = (object)[
			'https://idp.example.com/claims/domain' => 'org.example.corp',
		];
		$this->assertNull($this->resolver->resolve($claims, 'https://idp.example.com/claims/nonexistent'));
	}

	public function testUrlStyleClaimWithSpecialCharacters(): void {
		$claims = (object)[
			'https://idp.example.com/claims/user%20info' => 'encoded-value',
			'https://idp.example.com/claims/special_chars!@#' => 'special',
		];
		$this->assertSame('encoded-value', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/user%20info'
		));
		$this->assertSame('special', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/special_chars!@#'
		));
	}

	public function testFullNamespacedToken(): void {
		// Simulate an IdP token with URL-namespaced claims and nested dot-key objects
		$claims = (object)[
			'sub' => 'testuser',
			'email' => 'test@example.com',
			'https://idp.example.com/claims/domain' => 'org.example.corp',
			'https://idp.example.com/claims/department_number' => 'CORP.ENG.IT',
			'https://idp.example.com/claims/extended_attributes' => (object)[
				'user.typeOfActor' => 'CORP_OFF',
				'auth.subdomain' => 'CORP',
				'auth.permissions' => 'ADMIN',
				'auth.guest' => 'false',
			],
		];

		$this->assertSame('org.example.corp', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/domain'
		));
		$this->assertSame('CORP.ENG.IT', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/department_number'
		));
		$this->assertSame('CORP', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/extended_attributes.auth.subdomain'
		));
		$this->assertSame('ADMIN', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/extended_attributes.auth.permissions'
		));
		$this->assertSame('false', $this->resolver->resolve(
			$claims, 'https://idp.example.com/claims/extended_attributes.auth.guest'
		));
	}
}
