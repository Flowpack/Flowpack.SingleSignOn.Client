<?php
namespace TYPO3\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * SSO token for handling SSO callbacks
 *
 * TODO Add description how that works
 */
class SingleSignOnToken extends \TYPO3\Flow\Security\Authentication\Token\AbstractToken {

	/**
	 * The SSO credentials after callback to client
	 * @var array
	 * @Flow\Transient
	 */
	protected $credentials = array('accessToken' => '', 'signature' => '');

	/**
	 * @var \TYPO3\Flow\Http\Uri
	 * @Flow\Transient
	 */
	protected $callbackUri;

	/**
	 * The global session id when authenticated through the SSO server
	 * @var string
	 */
	protected $globalSessionId;

	/**
	 * Updates the authentication credentials, the authentication manager needs to authenticate this token.
	 * This could be a username/password from a login controller.
	 * This method is called while initializing the security context. By returning TRUE you
	 * make sure that the authentication manager will (re-)authenticate the tokens with the current credentials.
	 * Note: You should not persist the credentials!
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $request The current request instance
	 * @return boolean TRUE if this token needs to be (re-)authenticated
	 */
	public function updateCredentials(\TYPO3\Flow\Mvc\ActionRequest $actionRequest) {
		$httpRequest = $actionRequest->getHttpRequest();
		if ($httpRequest->getMethod() !== 'GET') {
			return;
		}

			// Check if we have a callback request
		$arguments = $httpRequest->getArguments();
		$accessTokenCipher = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($arguments, '__typo3.singlesignon.accessToken');
		$signature = \TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($arguments, '__typo3.singlesignon.signature');

		if (!empty($accessTokenCipher) && !empty($signature)) {
				// Get callback parameters from request
			$this->credentials['accessToken'] = base64_decode($accessTokenCipher);
			$this->credentials['signature'] = base64_decode($signature);

			$this->callbackUri = $actionRequest->getHttpRequest()->getUri();
			$arguments = $this->callbackUri->getArguments();
			unset($arguments['__typo3']);
			$this->callbackUri->setQuery(http_build_query($arguments));

			$this->setAuthenticationStatus(self::AUTHENTICATION_NEEDED);
		}
	}

	/**
	 * @return \TYPO3\Flow\Http\Uri
	 */
	public function getCallbackUri() {
		return $this->callbackUri;
	}

	/**
	 * @param string $globalSessionId
	 */
	public function setGlobalSessionId($globalSessionId) {
		$this->globalSessionId = $globalSessionId;
	}

	/**
	 * @return string
	 */
	public function getGlobalSessionId() {
		return $this->globalSessionId;
	}

}
?>