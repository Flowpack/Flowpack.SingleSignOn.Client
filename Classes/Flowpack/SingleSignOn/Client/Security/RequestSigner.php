<?php
namespace Flowpack\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class RequestSigner {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * @param \TYPO3\Flow\Http\Request $request
	 * @param string $identifier
	 * @param string $publicKeyFingerprint
	 * @return \TYPO3\Flow\Http\Request
	 */
	public function signRequest(\TYPO3\Flow\Http\Request $request, $identifier, $publicKeyFingerprint) {
		$signedRequest = clone $request;
		$signedRequest->setHeader('Date', gmdate(DATE_RFC2822));
		$signData = $this->getSignatureContent($signedRequest);
		$signature = $this->rsaWalletService->sign($signData, $publicKeyFingerprint);
		$signedRequest->setHeader('X-Request-Signature', $identifier . ':' . base64_encode($signature));
		return $signedRequest;
	}

	/**
	 * Get the content for the signature from the given request
	 *
	 * @param \TYPO3\Flow\Http\Request $httpRequest
	 * @return string
	 */
	public function getSignatureContent(\TYPO3\Flow\Http\Request $httpRequest) {
		$date = $httpRequest->getHeader('Date');
		$dateValue = $date instanceof \DateTime ? $date->format(DATE_RFC2822) : '';
		$signData = $httpRequest->getMethod() . chr(10)
			. sha1($httpRequest->getContent()) . chr(10)
			. $httpRequest->getHeader('Content-Type') . chr(10)
			. $dateValue . chr(10)
			. $httpRequest->getUri();
		return $signData;
	}

}

?>