<?php
namespace Flowpack\SingleSignOn\Client\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Command controller for key management
 *
 * @Flow\Scope("singleton")
 */
class SingleSignOnCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \TYPO3\Flow\Security\Cryptography\RsaWalletServiceInterface
	 * @Flow\Inject
	 */
	protected $rsaWalletService;

	/**
	 * Generate key pair command
	 *
	 * Creates a new key pair and imports it into the wallet.
	 * Useful for SSO client and server.
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
	 * @param string $keyPairUuid
	 * @return void
	 */
	public function exportPublicKeyCommand($keyPairUuid) {
		$publicKey = $this->rsaWalletService->getPublicKey($keyPairUuid);
		$this->output($publicKey->getKeyString());
	}

}

?>