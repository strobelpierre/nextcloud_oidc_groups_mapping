<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 OIDC Groups Mapping Contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OidcGroupsMapping\Model;

class Rule {

    private const VALID_TYPES = ['direct', 'prefix', 'map', 'conditional', 'template'];

    private function __construct(
        private string $id,
        private string $type,
        private bool $enabled,
        private string $claimPath,
        private array $config,
    ) {
    }

    public static function fromArray(array $data): self {
        if (!isset($data['id']) || !is_string($data['id'])) {
            throw new \InvalidArgumentException('Rule must have a string "id"');
        }
        if (!isset($data['type']) || !in_array($data['type'], self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException('Unknown rule type: ' . ($data['type'] ?? 'null'));
        }
        if (!isset($data['claimPath']) || !is_string($data['claimPath'])) {
            throw new \InvalidArgumentException('Rule must have a string "claimPath"');
        }

        return new self(
            $data['id'],
            $data['type'],
            $data['enabled'] ?? true,
            $data['claimPath'],
            $data['config'] ?? [],
        );
    }

    public function getId(): string {
        return $this->id;
    }

    public function getType(): string {
        return $this->type;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function getClaimPath(): string {
        return $this->claimPath;
    }

    public function getConfig(): array {
        return $this->config;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'enabled' => $this->enabled,
            'claimPath' => $this->claimPath,
            'config' => $this->config,
        ];
    }
}
