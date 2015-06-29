<?php
namespace SKYFILLERS\SfFilecollectionGallery\Hooks;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hooks for DataHandler
 *
 * Idea to use Hook for flexform settings: http://www.derhansen.de/2014/06/typo3-how-to-prevent-empty-flexform.html
 *
 * @author Jöran Kurschatke <j.kurschatke@skyfillers.com>
 */
class DataHandlerHooks {

	/**
	 * Checks if the fields defined in $checkFields are set in the data-array of pi_flexform. If a field is
	 * present and contains an empty value, the field is unset.
	 *
	 * Structure of the checkFields array:
	 *
	 * array('sheet' => array('field1', 'field2'));
	 *
	 * @param string $status
	 * @param string $table
	 * @param string $id
	 * @param array $fieldArray
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 *
	 * @return void
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference) {
		if ($table === 'tt_content' && $status == 'update' && isset($fieldArray['pi_flexform'])) {
			$checkFields = array(
				'sDEF' => array(
					'settings.imagesPerPage',
					'settings.numberOfPages'
				),
			);

			$flexformData =  GeneralUtility::xml2array($fieldArray['pi_flexform']);

			foreach ($checkFields as $sheet => $fields) {
				foreach($fields as $field) {
					if (isset($flexformData['data'][$sheet]['lDEF'][$field]['vDEF']) &&
						$flexformData['data'][$sheet]['lDEF'][$field]['vDEF'] === '') {
						unset($flexformData['data'][$sheet]['lDEF'][$field]);
					}
				}

				// If remaining sheet does not contain fields, then remove the sheet
				if (isset($flexformData['data'][$sheet]['lDEF']) && $flexformData['data'][$sheet]['lDEF'] === array()) {
					unset($flexformData['data'][$sheet]);
				}
			}

			/** @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools $flexFormTools */
			$flexFormTools = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Configuration\\FlexForm\\FlexFormTools');
			$fieldArray['pi_flexform'] = $flexFormTools->flexArray2Xml($flexformData, TRUE);
		}
	}

}