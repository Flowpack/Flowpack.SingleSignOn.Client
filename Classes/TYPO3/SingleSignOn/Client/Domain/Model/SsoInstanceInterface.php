<?php
namespace TYPO3\SingleSignOn\Client\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * SSO instance (client or server)
 */
interface SsoInstanceInterface {

	/**
	 * Get the instance public key
	 *
	 * @return string The instance public key
	 */
	public function getPublicKey();

	/**
	 * Get the instance private key
	 *
	 * @return string The instance private key
	 */
	public function getPrivateKey();

}
?>