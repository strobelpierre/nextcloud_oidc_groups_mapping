<?php

namespace OCA\UserOIDC\Event;

use OCP\EventDispatcher\Event;

class AttributeMappedEvent extends Event {
    private ?string $value;

    public function __construct(
        private string $attribute,
        private object $claims,
        ?string $default = null,
    ) {
        parent::__construct();
        $this->value = $default;
    }

    public function getAttribute(): string {
        return $this->attribute;
    }

    public function getClaims(): object {
        return $this->claims;
    }

    public function hasValue(): bool {
        return ($this->value != null);
    }

    public function getValue(): ?string {
        return $this->value;
    }

    public function setValue(?string $value): void {
        $this->value = $value;
    }
}
