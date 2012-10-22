<?php
namespace TYPO3\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

/**
 * SSO token for handling SSO callbacks
 *
 * TODO Add description how that works
 */
class SingleSignOnToken extends \TYPO3\Flow\Security\Authentication\Token\AbstractToken {

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
		// TODO Check if we have a callback request
		// TODO Get callback parameters from request
		// TODO Verify signature with server public key
		// TODO Decrypt and store accessToken
	}

}
?>