<?php
/**
 * Created by PhpStorm.
 * User: marcusdalgren
 * Date: 2017-10-25
 * Time: 10:04
 */

namespace MetaPic;


class Response {
	/**
	 * @var string
	 */
	private $responseData;

	public function __construct($responseData = "") {
		$this->responseData = $responseData;
	}

	public function getBody() {
		return $this->responseData;
	}
}