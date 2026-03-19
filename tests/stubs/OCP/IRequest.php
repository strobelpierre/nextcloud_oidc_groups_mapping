<?php

namespace OCP;

interface IRequest {
	public function getParam(string $key, $default = null);
}
