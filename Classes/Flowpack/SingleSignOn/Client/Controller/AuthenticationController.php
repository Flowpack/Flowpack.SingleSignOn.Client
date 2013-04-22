<?php
namespace Flowpack\SingleSignOn\Client\Controller;

/*                                                                               *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client". *
 *                                                                               */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * Authentication controller
 *
 * Accepts the client-side callback for SSO authentications
 *
 * @Flow\Scope("singleton")
 */
class AuthenticationController extends ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * Receive an SSO authentication callback and trigger authentication
	 * through the SingleSignOnProvider.
	 *
	 * GET /sso/authentication/callback?...
	 *
	 * @param string $callbackUri
	 * @return void
	 */
	public function callbackAction($callbackUri) {
		try {
			$this->authenticationManager->authenticate();
		} catch (\TYPO3\Flow\Security\Exception\AuthenticationRequiredException $exception) {
			$authenticationException = $exception;
		}

		if ($this->authenticationManager->isAuthenticated()) {
			$storedRequest = $this->securityContext->getInterceptedRequest();
			if ($storedRequest !== NULL) {
				$this->securityContext->setInterceptedRequest(NULL);
				$this->redirectToRequest($storedRequest);
			} else {
				// TODO Do we have to check the URI?
				$this->redirectToUri($callbackUri);
			}
		} else {
			throw new \Flowpack\SingleSignOn\Client\Exception('Could not authenticate in callbackAction triggered by the SSO server.', 1366613161, (isset($authenticationException) ? $authenticationException : NULL));
		}
	}

}
?>