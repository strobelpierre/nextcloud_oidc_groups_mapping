<?php

namespace OCP\AppFramework;

use OCP\IRequest;

abstract class Controller {
	protected string $appName;
	protected IRequest $request;

	public function __construct(string $appName, IRequest $request) {
		$this->appName = $appName;
		$this->request = $request;
	}
}
