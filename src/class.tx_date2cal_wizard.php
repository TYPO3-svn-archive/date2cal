<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 - 2008 Stefan Galinski (stefan.galinski@gmail.com)
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

require_once('class.jscalendar.php');

/**
 * Calendar Integration Wizard Class
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_date2cal_wizard {
	/** array contains extension configuration */
	private $extConfig = array();

	/**
	 * Reads and prepareas the extension configuration.
	 *
	 * @return void
	 */
	protected function prepareExtConfig() {
		// unserialize configuration
		$this->extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['date2cal']);

		// user/group settings
		$userProperties = $GLOBALS['BE_USER']->getTSConfig('tx_date2cal');
		if (is_array($userProperties['properties'])) {
			$this->extConfig = array_merge($this->extConfig, $userProperties['properties']);
		}
	}

	/**
	 * Activates the secondary options palette
	 *
	 * @return void
	 */
	protected function secOptionsOn() {
		$GLOBALS['BE_USER']->pushModuleData('xMOD_alt_doc.php', array('showPalettes' => 1));
	}

	/**
	 * renders date/datetime fields
	 *
	 * @param array $params TCA informations about date/datetime field
	 * @param object $pObj TCE forms object (not needed)
	 * @return empty string
	 */
	public function renderWizard($params, &$pObj) {
		// load extension configuration
		$this->prepareExtConfig();

		// enabling of secondary options
		if ($this->extConfig['secOptionsAlwaysOn']) {
			$this->secOptionsOn();
		}

		// add id attributes
		$inputId = 'data_' . $params['md5ID'];
		$params['item'] = str_replace(
			'<input type="checkbox"',
			'<input type="checkbox" id="' . $inputId . '_cb"',
			$params['item']
		);
		$params['item'] = str_replace(
			'<input type="text"',
			'<input type="text" id="' . $inputId . '_hr"',
			$params['item']
		);

		// get rid of the default calendar image in 4.3 and above
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
			$params['item'] = preg_replace('(<img[^>]+>)', '', $params['item']);
		}

		// init jscalendar class
		$JSCalendar = JSCalendar::getInstance();
		$JSCalendar->setInputField($inputId);

		// datetime format
		$format = ($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? '%m-%d-%Y' : '%d-%m-%Y');
		$JSCalendar->setDateFormat(($params['wConf']['evalValue'] == 'datetime'), $format);

		// render calendar images
		$params['item'] .= $JSCalendar->renderCalendarImages();

		// render the natural language parser
		if ($JSCalendar->getNLP()) {
			$params['item'] .= $JSCalendar->renderNaturalLanguageParser();
		}

		// get javascript for a single instance
		$params['item'] .= $JSCalendar->getConfigJS(false);

		// get initialisation code of the calendar
		$jsCode = $JSCalendar->getMainJS();
		if ($jsCode == '') {
			return '';
		}

		// get rid of the default calendar javascript in 4.3 and above
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4003000) {
			$jsCode .= '<script type="text/javascript">
				TYPO3.TCEFORMS.convertDateFieldsToDatePicker = function() {};
				</script>';
		}

		// set initialisation code
		$script = basename(PATH_thisScript);
		if (TYPO3_MODE == 'FE') { // frontend mode
			$params['item'] = $jsCode . $params['item'];
		} elseif (t3lib_div::int_from_ver(TYPO3_version) >= 4000000 || $script == 'db_layout.php') {
			// common for typo3 4.x and quick edit mode
			$GLOBALS['SOBE']->doc->JScode .= $jsCode;
		} elseif (is_object($GLOBALS['SOBE']->tceforms)) { // common for typo3 3.x
			$GLOBALS['SOBE']->tceforms->additionalCode_pre['date2cal'] = $jsCode;
		} else { // palette (doesnt work with php4)
			$pObj->additionalCode_pre['date2cal'] = $jsCode;
		}

		return '';
	}
}

?>
