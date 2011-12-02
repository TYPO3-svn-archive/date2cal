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

/**
 * An API for rendering of a jscalendar widget with or without natural language
 * parser support. The class consists methods that handle the complete rendering
 * of the needed input and checkbox fields and also contains some intelligent logic for
 * the language handling. You can use the API inside the frontend or backend.
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 * @package TYPO3
 * @subpackage date2cal
 */
class JSCalendar {
	/* @var $extConfig array date2cal configuration */
	private $extensionConfiguration = array();

	/* @var $config array calendar/naturalLanguageParser configuration */
	private $calendarConfiguration = array();

	/* @var $mainJavascriptSent boolean indicates if the main javascript was already sent */
	private $mainJavascriptSent = false;

	/* @var $lang tslib_fe language object*/
	private $languageHandlingInstance = null;

	/**
	 * This static method returns an instance of this class. It's important to use always this
	 * method instead of a direct initialization.
	 *
	 * @return JSCalendar instance of JSCalendar
	 */
	public static function &getInstance() {
		static $instance;
		if (!isset($instance)) {
			$instance = new JSCalendar();
		}

		return $instance;
	}

	/**
	 * Constructor
	 *
	 * Initializes some internal variables and sets the basic configuration options for the
	 * calendar and the natural language parser based upon the global extension configuration.
	 */
	public function __construct() {
		// add some paths
		$this->calendarConfiguration['backPath'] = $GLOBALS['BACK_PATH'] .
			(TYPO3_MODE == 'BE' ? '../' : '');
		$this->calendarConfiguration['relPath'] = $this->calendarConfiguration['backPath'] .
			t3lib_extMgm::siteRelPath('date2cal');
		$this->calendarConfiguration['absPath'] = t3lib_extMgm::extPath('date2cal');

		// set variable with the language object
		if (TYPO3_MODE == 'FE') {
			$this->languageHandlingInstance = $GLOBALS['TSFE'];
		} else {
			$this->languageHandlingInstance = $GLOBALS['LANG'];
		}

		// read and prepare the global extension configuration
		$this->extensionConfiguration = $this->readGlobalConfig();

		// default initialisation of the calendar
		$this->setNLP($this->extensionConfiguration['natLangParser']);
		$this->setCSS($this->extensionConfiguration['calendarCSS']);
		$this->setLanguage($this->extensionConfiguration['lang']);
		$this->setDateFormat();
		$this->setConfigOption('firstDay', $this->extensionConfiguration['firstDay'], true);
	}

	/**
	 * Reads and prepares the global extension configuration. We are merge any
	 * user/group typoscript inside the namespace tx_date2cal to the configuration if we
	 * are in backend mode.
	 *
	 * @return array global extension configuration with the merged user/group typoscript
	 */
	protected function readGlobalConfig() {
		// unserialize configuration
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['date2cal']);

		// get calendar image
		$extConfig['calendarIcon'] = t3lib_div::getFileAbsFileName($extConfig['calendarIcon']);
		$extConfig['calendarIcon'] = $this->calendarConfiguration['backPath'] .
			substr($extConfig['calendarIcon'], strlen(PATH_site));

		// get help image
		$extConfig['helpIcon'] = t3lib_div::getFileAbsFileName($extConfig['helpIcon']);
		$extConfig['helpIcon'] = $this->calendarConfiguration['backPath'] .
			substr($extConfig['helpIcon'], strlen(PATH_site));

		// user/group settings
		if (TYPO3_MODE == 'BE') {
			$userProperties = $GLOBALS['BE_USER']->getTSConfig('tx_date2cal');
			if (is_array($userProperties['properties'])) {
				$extConfig = array_merge($extConfig, $userProperties['properties']);
			}
		}

		return $extConfig;
	}

	/**
	 * Takes a pair of parameters and returns them as an html attribute string. The last
	 * parameter can be used to append a prefix to the name and id fields before they
	 * are converted into the attributes string.
	 *
	 * @param array $parameters1 less priority parameters
	 * @param array $parameters2 priority parameters
	 * @param string $prefix prefix for the name and id fields if they are available
	 * @return string attributes html string for usage inside an html node
	 */
	protected function parametersArrays2Html($parameters1, $parameters2, $prefix) {
		// merge the parameters
		if (!is_array($parameters1)) {
			$parameters1 = array();
		}

		if (!is_array($parameters2)) {
			$parameters2 = array();
		}
		$parameters = array_merge($parameters1, $parameters2);

		// add the prefix to name and id
		if ($parameters['name'] != '') {
			$parameters['name'] = $parameters['name'] . $prefix;
		}

		if ($parameters['id'] != '') {
			$parameters['id'] = $parameters['id'] . $prefix;
		}

		// transform parameters array to an html string
		$attributes = '';
		foreach ($parameters as $attributeName => $attributeValue) {
			$attributes .= $attributeName . '="' . $attributeValue . '"';
		}

		return $attributes;
	}

	/**
	 * This method renders an whole calendar input element that consists of a checkbox,
	 * an input field and a container for the natural language parser mode. The name and id
	 * of the checkbox will be suffixed with _cb and the id of the input field with _hr.
	 *
	 * User Parameters: You can inject own attribute parameters by using the second parameter.
	 * Each html node has it's own array with attributes. Beware that changing of the id
	 * isn't a really good idea.
	 *
	 * Examples:
	 *
	 * $userParameters['checkboxField']['class'] = 'myClass';
	 * $userParameters['inputField']['id'] = 'mySpecialID';
	 * $userParameters['nlpContainer']['class'] = 'containerClass';
	 * $userParameters['nlpMessage']['onchange'] = 'mySpecialOnClickEvent();';
	 *
	 * @see renderImage()
	 * @see setInputField()
	 *
	 * @param string $value default value of the input field element
	 * @param array|string $userParameters parameters of the different html nodes (see description)
	 * @param string $calendarIcon calendar image (optional; deprecated)
	 * @param string $helpIcon help image (optional; deprecated)
	 *
	 * @return string complete calendar html with fields and buttons
	 */
	public function render($value, $userParameters = array(), $calendarIcon = '', $helpIcon = '') {
		// generates the input field id/name if it not exists
		if (!isset($this->calendarConfiguration['inputField'])) {
			$this->setInputField(md5(microtime()));
		}

		$defaultParameters = array(
			'checkboxField' => array(
				'name' => $this->calendarConfiguration['inputField'],
				'class' => 'jscalendar_cb',
				'id' => $this->calendarConfiguration['inputField']
			),
			'inputField' => array(
				'name' => $this->calendarConfiguration['inputField'],
				'class' => 'jscalendar',
				'id' => $this->calendarConfiguration['inputField']
			)
		);

		// compatibility for an old parameter
		if (!is_array($userParameters)) {
			if ($userParameters != '') {
				$defaultParameters['checkboxField']['name'] = $userParameters;
				$defaultParameters['inputField']['name'] = $userParameters;
			}

			$userParameters = array();
		}

		// set initial value
		if ($value != '') {
			$defaultParameters['checkboxField']['checked'] = 'checked';
			$defaultParameters['inputField']['value'] = $value;
		}

		// render checkbox
		$attributes = $this->parametersArrays2Html(
			$defaultParameters['checkboxField'],
			$userParameters['checkboxField'],
			'_cb'
		);
		$content = '<input type="checkbox" ' . $attributes . ' />';

		// render input field
		$attributes = $this->parametersArrays2Html(
			$defaultParameters['inputField'],
			$userParameters['inputField'],
			'_hr'
		);
		$content .= ' <input type="text" ' . $attributes . ' />';

		// render images
		$content .= $this->renderImages($calendarIcon, $helpIcon, $userParameters);

		// message container for the natural language parser
		if ($this->calendarConfiguration['natLangParser']) {
			$content .= $this->renderNaturalLanguageParser($userParameters);
		}

		return $content;
	}

	/**
	 * This method renders a container for the natural language parser.
	 * You can inject own attribute parameters by using the last parameter.
	 *
	 * Examples:
	 *
	 * $userParameters['nlpContainer']['class'] = 'containerClass';
	 * $userParameters['nlpMessage']['onchange'] = 'mySpecialOnClickEvent();';
	 *
	 * @param array $userParameters
	 * @return string html container for the natural language parser
	 */
	public function renderNaturalLanguageParser($userParameters = array()) {
		$defaultParameters = array(
			'nlpContainer' => array(
				'id' => $this->calendarConfiguration['inputField']
			),
			'nlpMessage' => array(
				'id' => $this->calendarConfiguration['inputField']
			)
		);

		$containerAttributes = $this->parametersArrays2Html(
			$defaultParameters['nlpContainer'],
			$userParameters['nlpContainer'],
			'_msgCnt'
		);

		$messageAttributes = $this->parametersArrays2Html(
			$defaultParameters['nlpMessage'],
			$userParameters['nlpMessage'],
			'_msg'
		);
		$content = '<div ' . $containerAttributes . '>
			<span ' . $messageAttributes . '>&nbsp;</span>
		</div>';

		return $content;
	}

	/**
	 * Just a wrapper that combines the calls to the renderer for the calendar images
	 * and the javascript for single calendar instance. Shouldn't be used anymore and
	 * exists for backwards compatibility.
	 *
	 * @deprecated
	 * @see renderCalendarImages()
	 * @see getConfigJS
	 * @param string $calendarIcon
	 * @param string $helpIcon
	 * @param array $userParameters
	 * @return string
	 */
	public function renderImages($calendarIcon = '', $helpIcon = '', $userParameters = array()) {
		$content = $this->renderCalendarImages($calendarIcon, $helpIcon, $userParameters);
		$content .= $this->getConfigJS(true);
		return $content;
	}

	/**
	 * Renders the image buttons of the calendar and the help popup. The calendar button
	 * will be suffixed with "_trigger" and the help with "_help".
	 *
	 * Note: This method also returns the current calendar javascript configuration. This
	 * behaviour is deprecated and shouldn't be expected in one of the next versions.
	 *
	 * User Parameters: You can inject own attribute parameters by using the last parameter.
	 * Each image has it's own array with attributes.
	 *
	 * Examples:
	 *
	 * $userParameters['calendarImage']['class'] = 'myClass';
	 * $userParameters['helpImage']['id'] = 'mySpecialID';
	 *
	 *
	 * @param string $calendarIcon calendar image (optional)
	 * @param string $helpIcon help image (optional)
	 * @param array $userParameters parameters of the different image nodes (see description)
	 *
	 * @return string generated calendar images
	 */
	public function renderCalendarImages($calendarIcon = '', $helpIcon = '', $userParameters = array()) {
		$defaultParameters = array(
			'calendarImage' => array(
				'style' => 'cursor: pointer; vertical-align: middle;',
				'class' => 'date2cal_img_cal absMiddle',
				'id' => $this->calendarConfiguration['inputField']
			),
			'helpImage' => array(
				'style' => 'cursor: pointer; vertical-align: middle;',
				'class' => 'date2cal_img_help absMiddle',
				'id' => $this->calendarConfiguration['inputField']
			),
		);

		// check images
		$defaultParameters['calendarImage']['src'] = $GLOBALS['TSFE']->absRefPrefix .
			($calendarIcon == '' ? $this->extensionConfiguration['calendarIcon'] : $calendarIcon);
		$defaultParameters['helpImage']['src'] = $GLOBALS['TSFE']->absRefPrefix .
			($helpIcon == '' ? $this->extensionConfiguration['helpIcon'] : $helpIcon);

		// alt/title language labels for the images
		$calendarIconTitle = $this->languageHandlingInstance->sL(
			'LLL:EXT:date2cal/locallang.xml:calendar_wizard'
		);
		$defaultParameters['calendarImage']['title'] = $calendarIconTitle;
		$defaultParameters['calendarImage']['alt'] = $calendarIconTitle;

		$helpIconTitle = $this->languageHandlingInstance->sL(
			'LLL:EXT:date2cal/locallang.xml:help'
		);
		$defaultParameters['helpImage']['title'] = $helpIconTitle;
		$defaultParameters['helpImage']['alt'] = $helpIconTitle;

		// calendar trigger image
		$attributes = $this->parametersArrays2Html(
			$defaultParameters['calendarImage'],
			$userParameters['calendarImage'],
			'_trigger'
		);
		$content = ' <img ' . $attributes . ' />';

		// natural language parse help image
		if ($this->calendarConfiguration['natLangParser']) {
			$attributes = $this->parametersArrays2Html(
				$defaultParameters['helpImage'],
				$userParameters['helpImage'],
				'_help'
			);
			$content .= ' <img ' . $attributes . ' />';
		}

		return $content;
	}

	/**
	 * Sets a config option of the calendar.
	 *
	 * Official documentation of the JSCalendar options:
	 * http://www.dynarch.com/demos/jscalendar/doc/html/reference.html#node_sec_2.3
	 *
	 * @param string $option name of the option
	 * @param string $value value of the option
	 * @param bool $nonString set this option if you want to add an integer or boolean value
	 * @return void
	 */
	public function setConfigOption($option, $value, $nonString = false) {
		$this->calendarConfiguration['calConfig'][$option] =
			(!$nonString ? '\'' . $value . '\'' : $value);
	}

	/**
	 * Returns the value of the calendar option which is defined by the option parameter.
	 *
	 * @param string $option option name
	 * @return mixed option value (without the single quotes around the strings!)
	 */
	public function getConfigOption($option) {
		return str_replace('\'', '', $this->calendarConfiguration['calConfig'][$option]);
	}

	/**
	 * Sets the input field id of the calendar.
	 *
	 * @param string $field input field id
	 * @return void
	 */
	public function setInputField($field) {
		$this->calendarConfiguration['inputField'] = $field;
		$this->setConfigOption('inputField', $field . '_hr');
		$this->setConfigOption('button', $field . '_trigger');
	}

	/**
	 * Returns the input field id.
	 *
	 * @return string input field id
	 */
	public function getInputField() {
		return $this->calendarConfiguration['inputField'];
	}

	/**
	 * Sets the language of the calendar. Includes availability checks and fallback modes
	 * for frontend and backend.
	 *
	 * @param string $language language (let it empty for automatic detection)
	 * @return void
	 */
	public function setLanguage($language = '') {
		// language detection
		if ($language == '') {
			if (TYPO3_MODE == 'FE') {
				$language = $GLOBALS['TSFE']->config['config']['language'];
			} else {
				$language = $GLOBALS['LANG']->lang;
			}
		}

		// check availability of selected languages
		$this->calendarConfiguration['nlpPatternLanguage'] =
			$this->checkExistenceOfNlpPatternFile($language);
		$this->calendarConfiguration['nlpHelpFileLanguage'] =
			$this->checkExistenceOfNlpHelpFile($language);
		$this->calendarConfiguration['lang'] =
			$this->checkExistenceOfCalendarLanguage($language);
	}

	/**
	 * Sets the calendar css. Includes an availability check with a fallback to the aqua theme.
	 *
	 * @param string $calendarCSS calendar css file (default: aqua)
	 * @return void
	 */
	public function setCSS($calendarCSS = 'aqua') {
		$this->calendarConfiguration['calendarCSS'] = $calendarCSS;
		$skinPath = $this->calendarConfiguration['absPath'] . 'resources/jscalendar/skins/';
		if (!is_file($skinPath . $calendarCSS . '/theme.css')) {
			$this->calendarConfiguration['calendarCSS'] = 'aqua';
		}
	}

	/**
	 * Sets the natural language parser mode.
	 *
	 * @param bool $mode set this to true if you want to enable the natural language parser
	 * @return void
	 */
	public function setNLP($mode) {
		$this->calendarConfiguration['natLangParser'] = $mode;
	}

	/**
	 * Returns the current state of the natural language parser mode
	 *
	 * @return bool
	 */
	public function getNLP() {
		return $this->calendarConfiguration['natLangParser'];
	}

	/**
	 * Sets the date format of the calendar. If the format parameter isn't set, then
	 * the default TYPO3 settings are used instead ($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']).
	 *
	 * @param bool $time set this option if you want to define the time
	 * @param string $format the date format which should be used (optional)
	 * @return void
	 */
	public function setDateFormat($time = false, $format = '') {
		if ($format == '') {
			$format = preg_replace(
				'/([a-z])/i',
				'%\1',
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']
			);

			# default format if ddmmyy option is empty
			$format = ($format !== '' ? $format : '%d-%m-%Y');

			# we need to switch month and day for the USdateFormat
			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat']) {
				# contains a small hack with a temporary replacement %#
				$format = str_replace(array('%d', '%m', '%#'), array('%#', '%d', '%m'), $format);
			}
		}
		$jsDate = ($time ? '%H:%M ' : '') . $format;

		$value = ($time ? 'true' : 'false');
		$this->setConfigOption('showsTime', $value, true);
		$this->setConfigOption('time24', $value, true);
		$this->setConfigOption('ifFormat', $jsDate);
	}

	/**
	 * Returns the javascript configuration code for a single calendar instance.
	 *
	 * @param $includeCheckboxJavascript boolean includes the checkbox javascript
	 * @return string javascript code
	 */
	public function getConfigJS($includeCheckboxJavascript = true) {
		// set nlp help file language
		$this->setConfigOption(
			'helpPage',
			t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $this->calendarConfiguration['relPath'] .
				'resources/naturalLanguageParser/help/' .
				$this->calendarConfiguration['nlpHelpFileLanguage'] . '.html'
		);

		// generates the calendar configuration string
		$tmp = array();
		foreach ($this->calendarConfiguration['calConfig'] as $label => $value) {
			$tmp[] = $label . ': ' . ($value ? $value : 'null');
		}
		$config = implode(",\n", $tmp);

		// generates the javascript code for a single instance
		$js = '<script type="text/javascript">';
		if ($this->calendarConfiguration['natLangParser']) {
			$nlpFormat = $this->calendarConfiguration['calConfig']['ifFormat'];
			if ($this->calendarConfiguration['calConfig']['daFormat'] != '') {
				$nlpFormat = $this->calendarConfiguration['calConfig']['daFormat'];
			}

			$js .= '
				function initializeCalendar() {
					NaturalLanguageParser.setup ({
						format: ' . $nlpFormat . ',
						inputName: \'' . $this->calendarConfiguration['inputField'] . '\',
						elementId: \'' . $this->calendarConfiguration['inputField'] . '\',
						calendarOptions: {
							' . $config . '
						}
					});
				}';
		} else {
			$js .= '
				function initializeCalendar() {
					Calendar.setup ({
						' . $config . '
					});
				}';
		}

		$js .= '
			if (window.addEventListener) {
				window.addEventListener("load", initializeCalendar, false);
			} else if (window.attachEvent) {
				window.attachEvent("onload", initializeCalendar);
			}
		';

		// the checkbox javscript (should be moved into an own file...)
		if ($includeCheckboxJavascript) {
			$js .= '
				var checkbox = document.getElementById("' . $this->calendarConfiguration['inputField'] . '_cb");
				var checkboxFunction = function(event) {
					var field = document.getElementById("' . $this->calendarConfiguration['inputField'] . '_hr");
					if (field.value == false) {
						field.value = ' . strftime($this->calendarConfiguration['calConfig']['ifFormat']) . ';
					} else {
						field.value = "";
					}

					// on IE
					if (field.fireEvent) {
						field.fireEvent("onchange");
					}

					// on Gecko based browsers
					if (document.createEvent) {
						var evt = document.createEvent("HTMLEvents");
						if (evt.initEvent) {
							evt.initEvent("change", true, true);
						}
						if (field.dispatchEvent) {
							field.dispatchEvent(evt);
						}
					}
				};
				if (checkbox.addEventListener) {
					checkbox.addEventListener("change", checkboxFunction, false);
				} else if (checkbox.attachEvent) {
					checkbox.attachEvent("onclick", checkboxFunction);
				}

				var inputField = document.getElementById("' . $this->calendarConfiguration['inputField'] . '_hr");
				var inputFieldFunction = function(event) {
					var checkbox = document.getElementById("' . $this->calendarConfiguration['inputField'] . '_cb");
					var inputField = document.getElementById("' . $this->calendarConfiguration['inputField'] . '_hr");
					if (inputField.value != "") {
						checkbox.checked = true;
					} else {
						checkbox.checked = false;
					}
				};
				if (inputField.addEventListener) {
					inputField.addEventListener("change", inputFieldFunction, false);
				} else if (window.attachEvent) {
					inputField.attachEvent("onchange", inputFieldFunction);
				}
			';
		}

		return $js . '</script>';
	}

	/**
	 * Returns the shared javascript/css code for all calendar instances. The function can only be
	 * called once!
	 *
	 * @return string javascript code
	 */
	public function getMainJS() {
		// can only be called once
		if ($this->mainJavascriptSent) {
			return '';
		}
		$this->mainJavascriptSent = true;

		// jscalendar inclusion (javascript, languages and css)
		$relPath = $this->calendarConfiguration['relPath'] . 'resources/';
		$javascriptFiles = array(
			$relPath . 'jscalendar/calendar.js',
			$relPath . 'jscalendar/lang/calendar-en.js',
			$relPath . 'jscalendar/calendar-setup.js'
		);

		// include another language (english must always be loaded)
		if ($this->calendarConfiguration['lang'] != 'en') {
			$javascriptFiles[] = $relPath . 'jscalendar/lang/calendar-' .
				$this->calendarConfiguration['lang'] . '.js';
		}

		// natural language parser scripts
		if ($this->calendarConfiguration['natLangParser']) {
			$javascriptFiles[] = $relPath . 'naturalLanguageParser/naturalLanguageParser.js';
			$javascriptFiles[] = $relPath . 'naturalLanguageParser/patterns/' .
				$this->calendarConfiguration['nlpPatternLanguage'] . '.js';
		}

		// build html code
		$scripts = '';
		foreach ($javascriptFiles as $file) {
			$scripts .= '<script type="text/javascript" ' .
				'src="' . $GLOBALS['TSFE']->absRefPrefix . $file . '"></script>' . "\n";
		}
		$scripts .= '<link rel="stylesheet" type="text/css" ' .
			'href="' . $GLOBALS['TSFE']->absRefPrefix . $relPath . 'jscalendar/skins/' .
			$this->calendarConfiguration['calendarCSS'] . '/theme.css" />' . "\n";

		return $scripts;
	}

	/**
	 * Checks the availability of a pattern file for the natural language parser and
	 * returns the given language or the fallback "en".
	 *
	 * @param string $language language
	 * @return string "en" fallback or the input parameter
	 */
	protected function checkExistenceOfNlpPatternFile($language) {
		// convert language into an iso code
		if (array_key_exists($language, $this->languageHandlingInstance->csConvObj->isoArray)) {
			$language = $this->languageHandlingInstance->csConvObj->isoArray[$language];
		}

		// check availability
		$absolutePath = $this->calendarConfiguration['absPath'] . 'resources/naturalLanguageParser/';

		if (!is_file($absolutePath . '/patterns/' . $language . '.js')) {
			return 'en';
		}

		return $language;
	}

	/**
	 * Checks the availability of an help file for the natural language parser and
	 * returns the given language or the fallback "en".
	 *
	 * @param string $language language
	 * @return string "en" fallback or the input parameter
	 */
	protected function checkExistenceOfNlpHelpFile($language) {
		// convert language into an iso code
		if (array_key_exists($language, $this->languageHandlingInstance->csConvObj->isoArray)) {
			$language = $this->languageHandlingInstance->csConvObj->isoArray[$language];
		}

		// check availability
		$absolutePath = $this->calendarConfiguration['absPath'] . 'resources/naturalLanguageParser/';

		if (!is_file($absolutePath . 'help/' . $language . '.html')) {
			return 'en';
		}

		return $language;
	}

	/**
	 * Checks the availability of a language file for the jscalendar. The method takes
	 * the utf8 mode of the backend into account and tries to use the utf8 encoded version
	 * for the frontend.
	 *
	 * @param string $language language code
	 * @return string language (appended with -utf8, "en" fallback or same as input)
	 */
	protected function checkExistenceOfCalendarLanguage($language) {
		// convert language into an iso code
		if (array_key_exists($language, $this->languageHandlingInstance->csConvObj->isoArray)) {
			$language = $this->languageHandlingInstance->csConvObj->isoArray[$language];
		}

		// check availability of utf8 encoding for the frontend or an utf8 backend
		$absPath = $this->calendarConfiguration['absPath'] . 'resources/jscalendar/';
		if (($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] == 'utf-8'
			|| TYPO3_MODE == 'FE')
			&& is_file($absPath . 'lang/calendar-' . $language . '-utf8.js')
		) {
			return $language . '-utf8';
		}

		// check availability of a non-utf8 encoding
		if (!is_file($absPath . 'lang/calendar-' . $language . '.js')) {
			return 'en';
		}

		return $language;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/date2cal/src/class.jscalendar.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/date2cal/src/class.jscalendar.php']);
}

?>
