<?php
/**
* This file is part of Dropper.
*
* Dropper is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Dropper is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Dropper. If not, see <http://www.gnu.org/licenses/>.
*
* @author Adam M. Dutko <adam@runbymany.com>
* @link http://www.runbymany.com
* @copyright Copyright &copy; 2011 RunByMany, LLC
* @license GPLv3 or Later
*/

require_once "../Dropper.php";

$dropper = new Dropper();
$dropper->setup("../settings.ini");
print $dropper->allSSHKeys();


?>
