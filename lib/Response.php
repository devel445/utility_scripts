<?php
namespace PowernLib;

class Response {
	private $code = 400;
	private $data = "Unknown request";
	private $json = false;

	public function __construct() {

	}

	public function setCode($code) {
		if ($code) {
			$this->code = $code;
		}
	}

	public function setData($data) {
		if ($data) {
			$this->data = $data;
		}
	}

	public function setJsonResponse() {
		$this->json = true;
	}

	public function send() {
		if ($this->json) {
			header("Content-type: application/json");
		}

		if ($this->code) {
			header("Status: " . $this->code);
		}

		if ($this->json) {
			echo json_encode($this->data);
		} else {
			echo $this->data;
		}
	}
}
