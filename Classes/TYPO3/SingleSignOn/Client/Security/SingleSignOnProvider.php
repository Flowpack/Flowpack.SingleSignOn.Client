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
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Session\SessionInterface
	 */
	protected $session;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Log\SecurityLoggerInterface
	 */
	protected $securityLogger;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\SignalSlot\Dispatcher
	 */
	protected $signalSlotDispatcher;

	/**
	 * @var string
	 */
	protected $globalSessionTouchInterval = 60;

	/**
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, array $options = array()) {
		parent::__construct($name, $options);
		if (isset($options['globalSessionTouchInterval'])) {
			$this->globalSessionTouchInterval = (integer)$options['globalSessionTouchInterval'];
		}
	}

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

			$globalSessionId = $authenticationData['sessionId'];
			$this->session->addTag('TYPO3_SingleSignOn_Client-' . $globalSessionId);
			$authenticationToken->setGlobalSessionId($globalSessionId);
			$authenticationToken->setAccount($account);

			$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
		} elseif ($authenticationToken->getAuthenticationStatus() !== \TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL) {
			$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::NO_CREDENTIALS_GIVEN);
		}
	}

	/**
	 * This method is overridden to touch the global session if needed
	 *
	 * The method canAuthenticate will be called by the AuthenticationProviderManager on every
	 * request that calls authenticate (which is done through the PolicyEnforcement interceptor),
	 * so we can touch the global session regularly.
	 *
	 * @param \TYPO3\Flow\Security\Authentication\TokenInterface $authenticationToken
	 * @return boolean
	 */
	public function canAuthenticate(\TYPO3\Flow\Security\Authentication\TokenInterface $authenticationToken) {
		$canAuthenticate = parent::canAuthenticate($authenticationToken);
		if ($canAuthenticate && $authenticationToken->isAuthenticated()) {
			try {
				$this->touchSessionIfNeeded($authenticationToken);
			} catch (\TYPO3\SingleSignOn\Client\Exception\SessionNotFoundException $exception) {
				// FIXME Is there another way to unauthenticate the token?
				$authenticationToken->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::WRONG_CREDENTIALS);
			}
		}
		return $canAuthenticate;
	}

	/**
	 * Touches the global session on the server to synchronize expiration between
	 * clients and the server
	 *
	 * This is only done in a configurable minimal interval to limit the number of calls.
	 *
	 * @param \TYPO3\SingleSignOn\Client\Security\SingleSignOnToken $token
	 * @return void
	 * @throws \TYPO3\SingleSignOn\Client\Exception\SessionNotFoundException
	 */
	protected function touchSessionIfNeeded(SingleSignOnToken $token) {
		$currentTime = time();
		if ($currentTime - $token->getLastTouchTimestamp() > $this->globalSessionTouchInterval) {
			$ssoClient = $this->ssoClientFactory->create();
			$ssoServer = $this->createSsoServer();
			$sessionId = $token->getGlobalSessionId();

			$ssoServer->touchSession($ssoClient, $sessionId);
			$token->setLastTouchTimestamp(time());
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