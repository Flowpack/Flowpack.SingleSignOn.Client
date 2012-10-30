<?php
namespace TYPO3\SingleSignOn\Client\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * SSO server
 *
 * Will be configured using settings.
 */
class SsoServer implements SsoInstanceInterface {

	/**
	 * The public key
	 * @var string
	 */
	protected $publicKey;

	/**
	 * The private key
	 * @var string
	 */
	protected $privateKey;

	/**
	 * The SSO endpoint uri
	 * @var string
	 */
	protected $endpointUri;

	/**
	 * The SSO service base uri
	 * @var string
	 */
	protected $serviceBaseUri;


	/**
	 * Get the Sso server's public key
	 *
	 * @return string The Sso server's public key
	 */
	public function getPublicKey() {
		return $this->publicKey;
	}

	/**
	 * Sets this Sso server's public key
	 *
	 * @param string $publicKey The Sso server's public key
	 * @return void
	 */
	public function setPublicKey($publicKey) {
		$this->publicKey = $publicKey;
	}

	/**
	 * Get the Sso server's private key
	 *
	 * @return string The Sso server's private key
	 */
	public function getPrivateKey() {
		return $this->privateKey;
	}

	/**
	 * Sets this Sso server's private key
	 *
	 * @param string $privateKey The Sso server's private key
	 * @return void
	 */
	public function setPrivateKey($privateKey) {
		$this->privateKey = $privateKey;
	}

	/**
	 * Get the Sso server's endpoint uri
	 *
	 * @return string The Sso server's endpoint uri
	 */
	public function getEndpointUri() {
		return $this->endpointUri;
	}

	/**
	 * Sets this Sso server's endpoint uri
	 *
	 * @param string $endpointUri The Sso server's endpoint uri
	 * @return void
	 */
	public function setEndpointUri($endpointUri) {
		$this->endpointUri = $endpointUri;
	}

	/**
	 * Get the Sso server's service base uri
	 *
	 * @return string The Sso server's service base uri
	 */
	public function getServiceBaseUri() {
		return $this->serviceBaseUri;
	}

	/**
	 * Sets this Sso server's service base uri
	 *
	 * @param string $serviceBaseUri The Sso server's service base uri
	 * @return void
	 */
	public function setServiceBaseUri($serviceBaseUri) {
		$this->serviceBaseUri = $serviceBaseUri;
	}

}
?>