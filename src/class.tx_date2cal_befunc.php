<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Stefan Galinski (stefan.galinski@gmail.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(t3lib_extMgm::extPath('date2cal') . 'src/class.tx_date2cal_shared.php');

/**
 * Contains a hook for flexform manipulation (adding of the calendar wizard)
 *
 * @author  Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_date2cal_befunc {
	/**
	 * Hook for manipulating flexform fields
	 * It's needed to add the calendar wizard.
	 *
	 * @param array $dataStructArray flexform information
	 * @return void
	 */
	function getFlexFormDS_postProcessDS(&$dataStructArray) {
		if (is_array($dataStructArray['ROOT']) && is_array($dataStructArray['ROOT']['el'])) {
			$this->flexformNoTabs($dataStructArray);
		} elseif (is_array($dataStructArray['sheets'])) {
			$this->flexformTabbed($dataStructArray);
		}
	}

	/**
	 * Manipulates flexforms without tabs...
	 *
	 * @param array $dataStructArray flexform information
	 * @return void
	 */
	function flexformNoTabs(&$dataStructArray) {
		foreach ($dataStructArray['ROOT']['el'] as $field => $fConf) {
			// type check
			$type = tx_date2cal_shared::isDateOrDateTime($fConf['TCEforms']['config']);
			if ($type === false) {
				continue;
			}

			// add wizard
			tx_date2cal_shared::addWizard(
				$dataStructArray['ROOT']['el'][$field]['TCEforms'],
				$type
			);
		}
	}

	/**
	 * Manipulates flexforms with tabs...
	 *
	 * @param array $dataStructArray flexform information
	 * @return void
	 */
	function flexformTabbed(&$dataStructArray) {
		foreach ($dataStructArray['sheets'] as $sheet => $sheetData) {
			list($sheetData, $sheet) = t3lib_div::resolveSheetDefInDS($dataStructArray, $sheet);
			foreach ($sheetData['ROOT']['el'] as $field => $fConf) {
				// type check
				$type = tx_date2cal_shared::isDateOrDateTime($fConf['TCEforms']['config']);
				if ($type === false) {
					continue;
				}

				// add wizard
				tx_date2cal_shared::addWizard(
					$sheetData['ROOT']['el'][$field]['TCEforms'],
					$type
				);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/date2cal/src/class.tx_date2cal_befunc.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/date2cal/src/class.tx_date2cal_befunc.php']);
}

?>
