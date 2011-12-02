<?php

// hook to add date2cal features for flexforms
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][] =
	'EXT:date2cal/src/class.tx_date2cal_befunc.php:tx_date2cal_befunc';

?>