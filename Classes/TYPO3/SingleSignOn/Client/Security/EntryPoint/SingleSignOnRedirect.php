<?php
namespace TYPO3\SingleSignOn\Client\Security\EntryPoint;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client"  *
 *                                                                            *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;

/**
 * An entry point that redirects to the SSO server endpoint
 */
class SingleSignOnRedirect extends \TYPO3\Flow\Security\Authentication\EntryPoint\AbstractEntryPoint {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Service\UriService
	 */
	protected $uriService;

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
	public function startAuthentication(Request $request, Response $response) {
		$callbackUri = $request->getUri();
		$redirectUri = $this->uriService->buildLoginRedirectUri($callbackUri);
		$response->setStatus(303);
		$response->setHeader('Location', $redirectUri);
	}

}
?>