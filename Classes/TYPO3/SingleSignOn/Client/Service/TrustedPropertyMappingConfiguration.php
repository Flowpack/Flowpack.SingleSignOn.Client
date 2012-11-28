<?php
namespace TYPO3\SingleSignOn\Client\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.SingleSignOn.Client".*
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter;

/**
 * A property mapper for trusted sources
 *
 * Will map all properties and allow creation of new objects.
 */
class TrustedPropertyMappingConfiguration extends \TYPO3\Flow\Property\PropertyMappingConfiguration {

	/**
	 * Map all unknown properties by default
	 *
	 * @var boolean
	 */
	protected $mapUnknownProperties = TRUE;

	/**
	 * Set default type converter options
	 */
	public function __construct() {
		$this->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
	}

	/**
	 * @param string $propertyName
	 * @return \TYPO3\Flow\Property\PropertyMappingConfigurationInterface
	 */
	public function getConfigurationFor($propertyName) {
		if (isset($this->subConfigurationForProperty[$propertyName])) {
			return $this->subConfigurationForProperty[$propertyName];
		} elseif (isset($this->subConfigurationForProperty[self::PROPERTY_PATH_PLACEHOLDER])) {
			return $this->subConfigurationForProperty[self::PROPERTY_PATH_PLACEHOLDER];
		}

		return new self();
	}

}
?>