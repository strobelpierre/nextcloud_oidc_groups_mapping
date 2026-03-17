<?php

namespace OCP;

interface IURLGenerator {
    public function linkToRoute(string $routeName, array $arguments = []): string;
    public function linkTo(string $appName, string $file, array $args = []): string;
    public function imagePath(string $appName, string $image): string;
    public function getAbsoluteURL(string $url): string;
}
