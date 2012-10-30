<?php
namespace TYPO3\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A provider that uses a SSO server for authentication
 *
 * TODO Add more description how that works
 */
class SingleSignOnProvider extends \TYPO3\Flow\Security\Authentication\Provider\AbstractProvider {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Service\UriService
	 */
	protected $uriService;

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
		if (!$authenticationToken instanceof SingleSignOnToken) {
			throw new \TYPO3\Flow\Security\Exception\UnsupportedAuthenticationTokenException('This provider cannot authenticate the given token.', 1351008039);
		}

		if ($authenticationToken->getAuthenticationStatus() === \TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_NEEDED) {
				// Verify signature with server public key
			$credentials = $authenticationToken->getCredentials();
			$signature = $credentials['signature'];
			$accessTokenCipher = $credentials['accessToken'];
			if (!$this->uriService->verifyCallbackSignature($accessTokenCipher, $signature)) {
				throw new \TYPO3\Flow\Exception('Could not verify signature of access token', 1351008742);
			}
			$accessToken = $this->uriService->decryptCallbackAccessToken($accessTokenCipher);
			// TODO Decrypt accessToken

			// TODO Do actual SSO transfer of authentication data
			// TODO Set external session id on token

			// TODO Get / create correct account
			$account = new \TYPO3\Flow\Security\Account();
			$account->setAccountIdentifier('test');
			$account->setAuthenticationProviderName('SingleSignOn');
			$account->setRoles(array('Administrator'));
			$authenticationToken->setAccount($account);

			$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
		} elseif ($authenticationToken->getAuthenticationStatus() !== \TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL) {
			$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::NO_CREDENTIALS_GIVEN);
		}
	}
}

?>