<?php
/**
 * Created by PhpStorm.
 * User: Marcus Dalgren
 * Date: 2014-09-21
 * Time: 00:41
 */

namespace MetaPic;

use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Request;

class UserApiClient extends ApiClient {
	private $userToken;

	/**
	 * @param string $baseUrl
	 * @param string $userToken
	 */
	public function __construct($baseUrl, $userToken = null) {
		$this->userToken = $userToken;
		parent::__construct($baseUrl);
	}

	/**
	 * @return null|string
	 */
	public function getUserToken() {
		return $this->userToken;
	}

	/**
	 * @param string $userToken
	 */
	public function setUserToken( $userToken ) {
		$this->userToken = $userToken;
	}

	/**
	 * @param string $callMethod
	 * @param string $url
	 * @param array $arguments
	 *
	 * @return Request|EntityEnclosingRequest
	 */
	private function setupRequest($callMethod, $url, array $arguments = []) {
		$request = $this->getApiClient()->$callMethod("/" . $url);
		$request->getQuery()
			->set("user_token", $this->userToken)
			->set("mtpc_timestamp", date("Y-m-d H:i:s"));
		return $request;
	}
} 