<?php
namespace TYPO3\SingleSignOn\Client\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use \TYPO3\Flow\Http\Uri;

/**
 * SSO server repository
 *
 * Provides access to configured servers.
 */
class SsoServerRepository {

	/**
	 * @var array
	 */
	protected $serverConfigurations = array();

	/**
	 * @Flow\Inject
	 * @var \TYPO3\SingleSignOn\Client\Domain\Factory\SsoServerFactory
	 */
	protected $ssoServerFactory;

	/**
	 * Prepare settings
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		if (isset($settings['server'])) {
			$this->serverConfigurations = $settings['server'];
		}
	}

	/**
	 * @param string $fingerprint
	 * @return \TYPO3\SingleSignOn\Client\Domain\Model\SsoServer
	 */
	public function findByPublicKey($fingerprint) {
		foreach ($this->serverConfigurations as $serverIdentifier => $serverConfiguration) {
			if (isset($serverConfiguration['publicKeyUuid']) && $serverConfiguration['publicKeyUuid'] === $fingerprint) {
				return $this->ssoServerFactory->create($serverIdentifier);
			}
		}
	}

}
?>