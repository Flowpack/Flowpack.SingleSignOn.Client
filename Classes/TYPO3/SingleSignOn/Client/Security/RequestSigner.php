<?php
namespace TYPO3\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
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
	 * @param string $keyPairFingerprint
	 * @return \TYPO3\Flow\Http\Request
	 */
	public function signRequest(\TYPO3\Flow\Http\Request $request, $identifier, $keyPairFingerprint) {
		$signData = $this->getSignatureContent($request);
		$signature = $this->rsaWalletService->sign($signData, $keyPairFingerprint);
		$signedRequest = clone $request;
		$signedRequest->setHeader('X-Request-Signature', $identifier . ':' . base64_encode($signature));
		$signedRequest->setHeader('Date', gmdate('D, d M Y H:i:s') . ' GMT');
		return $signedRequest;
	}

	/**
	 * Get the content for the signature from the given request
	 *
	 * @param $httpRequest
	 * @return string
	 */
	public function getSignatureContent($httpRequest) {
		$signData = $httpRequest->getMethod() . chr(10)
			. sha1($httpRequest->getContent()) . chr(10)
			. $httpRequest->getHeader('Content-Type') . chr(10)
			. $httpRequest->getHeader('Date') . chr(10)
			. $httpRequest->getUri();
		return $signData;
	}

}

?>