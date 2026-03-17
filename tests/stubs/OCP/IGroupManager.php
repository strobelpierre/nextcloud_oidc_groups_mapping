<?php

namespace OCP;

interface IGroupManager {
    public function groupExists(string $gid): bool;
    public function createGroup(string $gid);
}
