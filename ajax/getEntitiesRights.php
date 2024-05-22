<?php

/**
 -------------------------------------------------------------------------
 LICENSE

 This file is part of Transferticketentity plugin for GLPI.

 Transferticketentity is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Transferticketentity is distributed in the hope that it will be useful,
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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

// if (!isset($_REQUEST['id'])
//     || !isset($_REQUEST['type'])) {
//    exit;
// }

/**
 * Retrieve entities rights from plugin table
 *
 * @return $array
 */
function getEntitiesRights()
{
    global $DB;

    $result = $DB->request(
        [
        'SELECT' => ['entities_id', 'allow_entity_only_transfer',
            'justification_transfer', 'allow_transfer', 'keep_category'],
        'FROM' => 'glpi_plugin_transferticketentity_entities_settings',
        'ORDER' => ['entities_id ASC']
        ]
    );

    $array = array();

    foreach ($result as $data) {
        array_push($array, $data);
    }

    return $array;
}

/**
 * Affiche les données en json pour faire de l'ajax dans ../js/script.js
 *
 * @return void
 */
function showJsonData()
{
    $getEntitiesRights = getEntitiesRights();
    
    echo json_encode($getEntitiesRights);
}

showJsonData();
