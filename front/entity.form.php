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

namespace GlpiPlugin\Transferticketentity;
use CommonDBTM;
use Session;

require '../../../inc/includes.php';

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
    // Session is not valid then exit
    exit;
}

class PluginTransferticketentityFormEntity extends CommonDBTM
{
    public $getNameEntity;
    public $ticketTransferRights;
    public $checkTransferSettings;

    public function __construct()
    {
        $this->getNameEntity = $this->getNameEntity(null);
        $this->ticketTransferRights = $this->ticketTransferRights();
        $this->checkTransferSettings = $this->checkTransferSettings(null);
    }

    public function getNameEntity($ID) 
    {
        global $DB;

        $result = $DB->request([
            'FROM' => 'glpi_entities',
            'WHERE' => ['id' => $ID]
        ]);

        $array = array();

        foreach($result as $data){
            return $data['name'];
        }

        return $array;
    }

    public function checkTransferSettings($ID) 
    {
        global $DB;

        $result = $DB->request([
            'FROM' => 'glpi_plugin_transferticketentity_entities_settings',
            'WHERE' => ['entities_id' => $ID]
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data);
        }

        return $array;
    }
   
    /**
     * Carries out the necessary actions for the transfer entity
     * 
     * @return void
     */
    public function ticketTransferRights()
    {
        global $CFG_GLPI;
        global $DB;

        if (isset($_POST['transfertticket'])) {
            $theServer = $_POST['theServer'];
            
            $allow_entity_only_transfer = $_POST['allow_entity_only_transfer'];
            $justification_transfer = $_POST['justification_transfer'];
            $allow_transfer = $_POST['allow_transfer'];
            $keep_category = $_POST['keep_category'];
            $itilcategories_id = $_POST['itilcategories_id'];
            $ID = $_POST['ID'];
            
            $entityName = self::getNameEntity($ID);
            $checkTransferSettings = self::checkTransferSettings($ID);

            if (!empty($checkTransferSettings)) {
                foreach ($checkTransferSettings as $transferSettings) {
                    $DB->delete('glpi_plugin_transferticketentity_entities_settings', 
                        [
                           'entities_id' => $transferSettings['entities_id']
                        ]
                    );
                }
            }

            $DB->insert('glpi_plugin_transferticketentity_entities_settings',
                [
                    'entities_id' => $ID,
                    'allow_entity_only_transfer' => $allow_entity_only_transfer,
                    'justification_transfer' => $justification_transfer,
                    'allow_transfer' => $allow_transfer,
                    'keep_category' => $keep_category,
                    'itilcategories_id' => $itilcategories_id
                ]
            );
            
            Session::addMessageAfterRedirect(
                __(
                    "Item successfully updated : "
                ) . $entityName,
                true,
                INFO
            );

            header('location:' . $theServer . 'front/entity.form.php?id=' . $ID);
        } 
    }
}

$transferticketentityEntity = new PluginTransferticketentityFormEntity();