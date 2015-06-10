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
	private $userClientId;
	private $userSecretKey;

	/**
	 * @param string $baseUrl
	 * @param null   $clientId
	 * @param null   $secretKey
	 *
	 * @internal param string $userToken
	 */
	public function __construct($baseUrl, $clientId = null, $secretKey = null) {
		$this->userClientId = $clientId;
		$this->userSecretKey = $secretKey;
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

	public function getUser() {
		$request = $this->setupRequest("get", "users", []);
		$response = $this->sendRequest($request);
		return array_shift($response);
	}



	/**
	 * @param string $callMethod
	 * @param string $url
	 * @param array $arguments
	 *
	 * @return Request|EntityEnclosingRequest
	 */
	protected function setupRequest($callMethod, $url, array $arguments = []) {
		$request = $this->getApiClient()->$callMethod("/" . $url);
		$arguments["user_client_id"] = $this->userClientId;
		$timeStamp = date("Y-m-d H:i:s");
		$authToken = $this->getAuthToken($arguments, $this->userSecretKey, $timeStamp);

		$request->getQuery()
			->set("access_token", $authToken)
			->set("mtpc_timestamp", $timeStamp)
			->set("user_client_id", $this->userClientId);
		return $request;
	}
} 