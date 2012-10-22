<?php
namespace TYPO3\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

/**
 * A provider that uses a SSO server for authentication
 *
 * TODO Add more description how that works
 */
class SingleSignOnProvider extends \TYPO3\Flow\Security\Authentication\Provider\AbstractProvider {

	/**
	 * Returns the classnames of the tokens this provider is responsible for.
	 *
	 * @return array The classname of the token this provider is responsible for
	 */
	public function getTokenClassNames() {
		return array('TYPO3\SingleSignOn\Client\Security\SingleSignOnToken');
	}

	/**
	 * Tries to authenticate the given token. Sets isAuthenticated to TRUE if authentication succeeded.
	 *
	 * @param \TYPO3\Flow\Security\Authentication\TokenInterface $authenticationToken The token to be authenticated
	 * @return void
	 */
	public function authenticate(\TYPO3\Flow\Security\Authentication\TokenInterface $authenticationToken) {
		// 1. Check for token with parameters
		// 1.a. If token is set
		// 1.b. If token does not exist
	}

}
?>