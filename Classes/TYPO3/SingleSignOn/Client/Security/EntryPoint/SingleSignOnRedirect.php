<?php
namespace TYPO3\SingleSignOn\Client\Security\EntryPoint;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * An entry point that redirects to the SSO server endpoint
 */
class SingleSignOnRedirect extends \TYPO3\Flow\Security\Authentication\EntryPoint\AbstractEntryPoint {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Service\UrlService
	 */
	protected $urlService;

	/**
	 * Starts the authentication by redirecting to the SSO endpoint
	 *
	 * The redirect includes the callback URI (the original URI from the given request)
	 * the client identifier and a signature of the arguments with the client private key.
	 *
	 * @param \TYPO3\Flow\Http\Request $request The current request
	 * @param \TYPO3\Flow\Http\Response $response The current response
	 * @return void
	 */
	public function startAuthentication(\TYPO3\Flow\Http\Request $request, \TYPO3\Flow\Http\Response $response) {
		$callbackUrl = $request->getUri();
		$redirectUrl = $this->urlService->buildLoginRedirectUrl($callbackUrl);
		$response->setStatus(303);
		$response->setHeader('Location', $redirectUrl);
	}

}
?>