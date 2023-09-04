<?php
/**
 -------------------------------------------------------------------------
 LICENSE

 This file is part of transferticketentity plugin for GLPI.

 transferticketentity is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 transferticketentity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @category  Ticket
 @package   Transferticketentity
 @author    Yannick Comba <y.comba@maine-et-loire.fr>
 @copyright 2015-2023 Département de Maine et Loire plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            https://www.gnu.org/licenses/gpl-3.0.html
 @link      https://github.com/departement-maine-et-loire/
 --------------------------------------------------------------------------
 */

require "../../../inc/includes.php";

Session::checkLoginUser();

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Html::header(
        "Transferticketentity", $_SERVER['PHP_SELF'], 
        "plugins", "plugintransferticketentity", ""
    );
} else {
    Html::helpHeader("Transferticketentity", $_SERVER['PHP_SELF']);
}

$config = new PluginTransferticketentityConfig();
$config->addProfiles();
$config->deleteProfiles();
$config->showFormETT();

Html::closeForm();

Html::footer();