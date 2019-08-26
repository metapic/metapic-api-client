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
	private $responseCode;

	public function __construct($responseData = "",$responseCode = 200) {
		$this->responseData = $responseData;
        $this->responseCode = $responseCode;
	}

	public function getBody() {
		return $this->responseData;
	}
	public function getStatusCode(){
        return $this->responseCode;
    }

}