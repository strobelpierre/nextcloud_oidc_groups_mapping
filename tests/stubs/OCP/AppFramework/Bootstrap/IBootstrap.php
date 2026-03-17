<?php

namespace OCP\AppFramework\Bootstrap;

interface IBootstrap {
    public function register(IRegistrationContext $context): void;
    public function boot(IBootContext $context): void;
}
