<?php
namespace TYPO3\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\SingleSignOn\Client\Exception;

/**
 * A provider that uses a SSO server for authentication
 *
 * TODO Add more description how that works
 */
class SingleSignOnProvider extends \TYPO3\Flow\Security\Authentication\Provider\AbstractProvider {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Factory\SsoServerFactory
	 */
	protected $ssoServerFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Factory\SsoClientFactory
	 */
	protected $ssoClientFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Service\GlobalAccountMapperInterface
	 */
	protected $globalAccountMapper;

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
	 * @Flow\Session(autoStart=true)
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

			$ssoServer = $this->createSsoServer();
			if (!$ssoServer->verifyCallbackSignature($accessTokenCipher, $signature)) {
				throw new Exception('Could not verify signature of access token', 1351008742);
			}

			$ssoClient = $this->ssoClientFactory->create();
			$accessToken = $ssoClient->decryptCallbackAccessToken($accessTokenCipher);
			if ($accessToken === '') {
				throw new Exception('Could not decrypt access token', 1351690950);
			}

			$authenticationData = $ssoServer->redeemAccessToken($ssoClient, $accessToken);
			$account = $this->globalAccountMapper->getAccount($ssoClient, $authenticationData['account']);

			$authenticationToken->setGlobalSessionId($authenticationData['sessionId']);
			$authenticationToken->setAccount($account);

			$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
		} elseif ($authenticationToken->getAuthenticationStatus() !== \TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL) {
			$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::NO_CREDENTIALS_GIVEN);
		}
	}

	/**
	 * Create an SSO server instance from the provider options
	 *
	 * @return \TYPO3\SingleSignOn\Client\Domain\Model\SsoServer
	 */
	protected function createSsoServer() {
		if (!isset($this->options['server'])) {
			throw new Exception('Missing "server" option for SingleSignOnProvider authentication provider "' . $this->name . '". Please specifiy one using the providerOptions setting.', 1351690847);
		}
		$ssoServer = $this->ssoServerFactory->create($this->options['server']);
		return $ssoServer;
	}

}
?>