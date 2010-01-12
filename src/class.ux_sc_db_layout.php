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
 * http://bugs.typo3.org/view.php?id=4340
 * It's not needed anymore for TYPO3 4.2 and above!
 */
class ux_SC_db_layout extends SC_db_layout {
	function printContent() {
		echo $this->doc->insertStylesAndJS($this->content);
	}
}

?>
