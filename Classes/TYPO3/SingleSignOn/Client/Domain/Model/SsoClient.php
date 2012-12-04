<?php
namespace TYPO3\SingleSignOn\Client\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * SSO client
 *
 * Will be configured using settings.
 */
class SsoClient {

	/**
	 * The key pair uuid
	 * @var string
	 */
	protected $keyPairUuid;

	/**
	 * The client identifier
	 * @var string
	 */
	protected $baseUri;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 */
	protected $rsaWalletService;

	/**
	 * Decrypt the access token cipher on callback to the client
	 *
	 * @param string $accessTokenCipher The access token ciphertext from the callback URI arguments
	 * @return string The decrypted access token or an empty string if the access token could not be decrypted
	 */
	public function decryptCallbackAccessToken($accessTokenCipher) {
		return $this->rsaWalletService->decrypt($accessTokenCipher, $this->keyPairUuid);
	}

	/**
	 * @param string $identifier
	 */
	public function setBaseUri($identifier) {
		$this->baseUri = $identifier;
	}

	/**
	 * @return string
	 */
	public function getBaseUri() {
		return $this->baseUri;
	}

	/**
	 * @param string $keyPairUuid
	 */
	public function setKeyPairUuid($keyPairUuid) {
		$this->keyPairUuid = $keyPairUuid;
	}

	/**
	 * @return string
	 */
	public function getKeyPairUuid() {
		return $this->keyPairUuid;
	}

}
?>