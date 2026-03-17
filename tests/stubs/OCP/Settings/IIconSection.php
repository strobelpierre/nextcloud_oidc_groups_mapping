<?php

namespace OCP\Settings;

interface IIconSection {
    public function getID(): string;
    public function getName(): string;
    public function getPriority(): int;
    public function getIcon(): string;
}
