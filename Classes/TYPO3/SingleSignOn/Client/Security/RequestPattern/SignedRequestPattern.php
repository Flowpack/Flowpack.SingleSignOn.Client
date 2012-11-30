<?php
namespace TYPO3\SingleSignOn\Client\Security\RequestPattern;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * FIXME This is rather a "UnsignedRequest" pattern because it doesn't match correctly signed requests.
 */
class SignedRequestPattern implements \TYPO3\Flow\Security\RequestPatternInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Security\RequestSigner
	 */
	protected $requestSigner;

	/**
	 * @var \TYPO3\SingleSignOn\Client\Security\PublicKeyResolverInterface
	 */
	protected $publicKeyResolver;

	/**
	 * @var array
	 */
	protected $patternConfiguration;

	/**
	 * Returns the pattern configuration
	 *
	 * @return string The pattern configuration
	 */
	public function getPattern() {
		return $this->patternConfiguration;
	}

	/**
	 * Sets the pattern (match) configuration
	 *
	 * @param object $patternConfiguration The pattern (match) configuration
	 * @return void
	 */
	public function setPattern($patternConfiguration) {
		$this->patternConfiguration = $patternConfiguration;
		if (isset($patternConfiguration['resolverType'])) {
			$this->publicKeyResolver = $this->objectManager->get($patternConfiguration['resolverType']);
		}
	}

	/**
	 * Matches the current request for an unverified signed request.
	 *
	 * This pattern will return TRUE if the request is not signed or
	 * the signature of the request is invalid.
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request The request that should be matched
	 * @return boolean TRUE if the pattern matched, FALSE otherwise
	 */
	public function matchRequest(\TYPO3\Flow\Mvc\RequestInterface $request) {
		/** @var \TYPO3\Flow\Http\Request $httpRequest */
		$httpRequest = $request->getHttpRequest();
		if ($httpRequest->hasHeader('X-Request-Signature')) {
			$identifierAndSignature = explode(':', $httpRequest->getHeader('X-Request-Signature'), 2);
			if (count($identifierAndSignature) !== 2) {
				throw new \TYPO3\Flow\Exception('Invalid signature header format, expected "identifier:base64(signature)"', 1354287886);
			}
			$identifier = $identifierAndSignature[0];
			$signature = base64_decode($identifierAndSignature[1]);

			$signData = $this->requestSigner->getSignatureContent($httpRequest);

			$publicKeyFingerprint = $this->publicKeyResolver->resolveFingerprintByIdentifier($identifier);
			if ($publicKeyFingerprint === NULL) {
				throw new \TYPO3\Flow\Exception('Cannot resolve identifier "' . $identifier .  '"', 1354288898);
			}

			if ($this->rsaWalletService->verifySignature($signData, $signature, $publicKeyFingerprint)) {
				return FALSE;
			}
		}

		return TRUE;
	}

}
?>