<?php

namespace OCP\AppFramework\Http;

class DataResponse {
	private mixed $data;
	private int $status;

	public function __construct(mixed $data = [], int $status = 200, array $headers = []) {
		$this->data = $data;
		$this->status = $status;
	}

	public function getData(): mixed {
		return $this->data;
	}

	public function getStatus(): int {
		return $this->status;
	}
}
