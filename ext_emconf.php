<?php

########################################################################
# Extension Manager/Repository config file for ext "date2cal".
#
# Auto generated 06-12-2009 19:42
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Date2Calendar',
	'description' => 'Extends all backend date/datetime fields with a calendar in relation with a natural language parser. The usage in other extension is supported by an API.',
	'category' => 'be',
	'shy' => 0,
	'version' => '7.2.0',
	'dependencies' => '',
	'conflicts' => 'erotea_date2cal,kj_becalendar',
	'priority' => 'bottom',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Stefan Galinski',
	'author_email' => 'stefan.galinski@gmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.0.0-5.3.99',
			'typo3' => '4.0.0-4.3.99',
		),
		'conflicts' => array(
			'erotea_date2cal' => '',
			'kj_becalendar' => '',
		),
		'suggests' => array(
			'scriptmerger' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:82:{s:9:"ChangeLog";s:4:"326f";s:16:"de.locallang.xml";s:4:"1aca";s:21:"ext_conf_template.txt";s:4:"7809";s:12:"ext_icon.gif";s:4:"78f7";s:17:"ext_localconf.php";s:4:"0b0a";s:14:"ext_tables.php";s:4:"4bcf";s:13:"locallang.xml";s:4:"9c8d";s:14:"doc/manual.sxw";s:4:"82f6";s:29:"resources/images/calendar.png";s:4:"5307";s:30:"resources/images/calendar2.gif";s:4:"c1e5";s:29:"resources/images/helpIcon.png";s:4:"b079";s:30:"resources/jscalendar/ChangeLog";s:4:"5628";s:38:"resources/jscalendar/calendar-setup.js";s:4:"cf54";s:32:"resources/jscalendar/calendar.js";s:4:"e1d0";s:40:"resources/jscalendar/lang/calendar-af.js";s:4:"8a39";s:40:"resources/jscalendar/lang/calendar-al.js";s:4:"5a46";s:40:"resources/jscalendar/lang/calendar-bg.js";s:4:"eaa5";s:47:"resources/jscalendar/lang/calendar-big5-utf8.js";s:4:"9eaa";s:42:"resources/jscalendar/lang/calendar-big5.js";s:4:"d4f2";s:40:"resources/jscalendar/lang/calendar-br.js";s:4:"f4c3";s:40:"resources/jscalendar/lang/calendar-ca.js";s:4:"a912";s:45:"resources/jscalendar/lang/calendar-cn-utf8.js";s:4:"0a98";s:45:"resources/jscalendar/lang/calendar-cs-utf8.js";s:4:"6119";s:44:"resources/jscalendar/lang/calendar-cs-win.js";s:4:"1776";s:40:"resources/jscalendar/lang/calendar-da.js";s:4:"c942";s:40:"resources/jscalendar/lang/calendar-de.js";s:4:"f811";s:40:"resources/jscalendar/lang/calendar-du.js";s:4:"5c26";s:40:"resources/jscalendar/lang/calendar-el.js";s:4:"b2b4";s:40:"resources/jscalendar/lang/calendar-en.js";s:4:"4681";s:40:"resources/jscalendar/lang/calendar-es.js";s:4:"9760";s:40:"resources/jscalendar/lang/calendar-fi.js";s:4:"f385";s:45:"resources/jscalendar/lang/calendar-fr-utf8.js";s:4:"49ae";s:45:"resources/jscalendar/lang/calendar-he-utf8.js";s:4:"4f95";s:45:"resources/jscalendar/lang/calendar-hr-utf8.js";s:4:"7d22";s:40:"resources/jscalendar/lang/calendar-hr.js";s:4:"e83c";s:40:"resources/jscalendar/lang/calendar-hu.js";s:4:"a884";s:40:"resources/jscalendar/lang/calendar-it.js";s:4:"4e7a";s:40:"resources/jscalendar/lang/calendar-jp.js";s:4:"9071";s:45:"resources/jscalendar/lang/calendar-ko-utf8.js";s:4:"ea3e";s:40:"resources/jscalendar/lang/calendar-ko.js";s:4:"e0e3";s:45:"resources/jscalendar/lang/calendar-lt-utf8.js";s:4:"cfac";s:40:"resources/jscalendar/lang/calendar-lt.js";s:4:"0175";s:40:"resources/jscalendar/lang/calendar-lv.js";s:4:"77e7";s:40:"resources/jscalendar/lang/calendar-nl.js";s:4:"01a6";s:40:"resources/jscalendar/lang/calendar-no.js";s:4:"931f";s:45:"resources/jscalendar/lang/calendar-pl-utf8.js";s:4:"f92e";s:40:"resources/jscalendar/lang/calendar-pl.js";s:4:"d4d0";s:40:"resources/jscalendar/lang/calendar-pt.js";s:4:"7d8b";s:40:"resources/jscalendar/lang/calendar-ro.js";s:4:"559f";s:40:"resources/jscalendar/lang/calendar-ru.js";s:4:"bc19";s:45:"resources/jscalendar/lang/calendar-ru_win_.js";s:4:"b1fa";s:40:"resources/jscalendar/lang/calendar-si.js";s:4:"7e30";s:40:"resources/jscalendar/lang/calendar-sk.js";s:4:"1565";s:40:"resources/jscalendar/lang/calendar-sp.js";s:4:"c97f";s:40:"resources/jscalendar/lang/calendar-sv.js";s:4:"ee70";s:40:"resources/jscalendar/lang/calendar-tr.js";s:4:"b692";s:40:"resources/jscalendar/lang/calendar-zh.js";s:4:"9a07";s:40:"resources/jscalendar/skins/menuarrow.gif";s:4:"1f8c";s:41:"resources/jscalendar/skins/menuarrow2.gif";s:4:"1f8c";s:45:"resources/jscalendar/skins/aqua/active-bg.gif";s:4:"f8fb";s:43:"resources/jscalendar/skins/aqua/dark-bg.gif";s:4:"949f";s:44:"resources/jscalendar/skins/aqua/hover-bg.gif";s:4:"803a";s:45:"resources/jscalendar/skins/aqua/menuarrow.gif";s:4:"1f8c";s:45:"resources/jscalendar/skins/aqua/normal-bg.gif";s:4:"8511";s:47:"resources/jscalendar/skins/aqua/rowhover-bg.gif";s:4:"c097";s:45:"resources/jscalendar/skins/aqua/status-bg.gif";s:4:"1238";s:41:"resources/jscalendar/skins/aqua/theme.css";s:4:"4877";s:44:"resources/jscalendar/skins/aqua/title-bg.gif";s:4:"8d65";s:44:"resources/jscalendar/skins/aqua/today-bg.gif";s:4:"9bef";s:47:"resources/jscalendar/skins/skin_grey2/theme.css";s:4:"7f96";s:43:"resources/jscalendar/skins/t3skin/theme.css";s:4:"07c4";s:44:"resources/jscalendar/skins/t3skin2/theme.css";s:4:"98f9";s:56:"resources/naturalLanguageParser/naturalLanguageParser.js";s:4:"1f12";s:44:"resources/naturalLanguageParser/help/de.html";s:4:"bf4e";s:44:"resources/naturalLanguageParser/help/en.html";s:4:"2493";s:46:"resources/naturalLanguageParser/patterns/de.js";s:4:"fed0";s:46:"resources/naturalLanguageParser/patterns/en.js";s:4:"62d6";s:24:"src/class.jscalendar.php";s:4:"5154";s:32:"src/class.tx_date2cal_befunc.php";s:4:"c449";s:32:"src/class.tx_date2cal_shared.php";s:4:"2bb0";s:32:"src/class.tx_date2cal_wizard.php";s:4:"ac0c";s:29:"src/class.ux_sc_db_layout.php";s:4:"c322";}',
	'suggests' => array(
	),
);

?>