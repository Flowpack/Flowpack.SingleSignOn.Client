<?php
namespace Flowpack\SingleSignOn\Client\Command;

/*                                                                               *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client". *
 *                                                                               */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * Command controller for the SSO key management
 *
 * @Flow\Scope("singleton")
 */
class SsoKeyCommandController extends CommandController {

	/**
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 * @Flow\Inject
	 */
	protected $rsaWalletService;

	/**
	 * Generate key pair command
	 *
	 * Creates a new key pair and imports it into the wallet.
	 * Used by SSO client and server.
	 *
	 * @return void
	 */
	public function generateKeyPairCommand() {
		$keyUuid = $this->rsaWalletService->generateNewKeypair();
		$this->outputLine('Created key with uuid: ' . $keyUuid);
	}

	/**
	 * Export a public key
	 *
	 * @param string $publicKeyFingerprint
	 * @return void
	 */
	public function exportPublicKeyCommand($publicKeyFingerprint) {
		$publicKey = $this->rsaWalletService->getPublicKey($publicKeyFingerprint);
		$this->output($publicKey->getKeyString());
	}

}

?>