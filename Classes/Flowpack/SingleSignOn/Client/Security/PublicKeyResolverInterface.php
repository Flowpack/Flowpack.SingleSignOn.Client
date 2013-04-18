<?php
namespace Flowpack\SingleSignOn\Client\Security;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Flowpack.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 *
 */
interface PublicKeyResolverInterface {

	/**
	 * @param string $identifier The identifier for looking up the public key
	 * @return string The public key fingerprint or NULL if no public key was found for the identifier
	 */
	public function resolveFingerprintByIdentifier($identifier);

}

?>