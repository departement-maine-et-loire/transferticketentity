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

use Glpi\Application\View\TemplateRenderer;
use Glpi\ContentTemplates\Parameters\ITILCategoryParameters;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginTransferticketentityTicket extends Ticket
{
    /**
     * If the profile is authorised, add an extra tab
     *
     * @param object $item         Ticket
     * @param int    $withtemplate 0
     * 
     * @return "Entity ticket transfer"
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $checkProfiles = PluginTransferticketentityProfile::canUseProfiles();

        if (in_array($_SESSION['glpiactiveprofile']['id'], $checkProfiles)) {
            if ($item->getType() == 'Ticket') {
                return __("Transfer Ticket Entity", "transferticketentity");
            }
            return '';
        }
    }

    /**
     * Give the ticket entity
     *
     * @return $data
     */
    public function getTicketEntity()
    {
        global $DB;

        $id_ticket = $_REQUEST['id'];

        $result = $DB->request([
            'SELECT' => ['glpi_entities.id', 'glpi_entities.name'],
            'FROM' => 'glpi_tickets',
            'LEFT JOIN' => ['glpi_entities' => ['FKEY' => ['glpi_tickets'     => 'entities_id',
                                                           'glpi_entities' => 'id']]],
            'WHERE' => ['glpi_tickets.id' => $id_ticket]
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id'], $data['name']);
        }

        return $array;
    }

    /**
     * Check that the ticket is not closed
     *
     * @return $data
     */
    public function checkTicket()
    {
        global $DB;

        $id_ticket = $_REQUEST['id'];

        $result = $DB->request([
            'SELECT' => 'id',
            'FROM' => 'glpi_tickets',
            'WHERE' => ['status' => 6]
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id']);
        }

        if(!in_array($id_ticket, $array)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the group assigned to the ticket
     *
     * @return $data
     */
    public function getTicketGroup()
    {
        global $DB;

        $id_ticket = $_REQUEST['id'];

        $result = $DB->request([
            'FROM' => 'glpi_groups_tickets',
            'WHERE' => ['tickets_id' => $id_ticket, 'type' => 2]
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['groups_id']);
        }

        return $array;
    }

    /**
     * Get all the entities which aren't the current entity with their rights
     *
     * @return array
     */
    public function getEntitiesRights()
    {
        global $DB;

        $getTicketEntity = self::getTicketEntity();
        $theEntity = $getTicketEntity[0];

        $result = $DB->request([
            'SELECT' => ['E.id', 'E.entities_id', 'E.name', 'TES.allow_entity_only_transfer', 'TES.justification_transfer', 'TES.allow_transfer'],
            'FROM' => 'glpi_entities AS E',
            'LEFT JOIN' => ['glpi_plugin_transferticketentity_entities_settings AS TES' => ['FKEY' => ['E' => 'id',
                                                           'TES' => 'entities_id']]],
            'WHERE' => ['NOT' => ['E.id' => $theEntity]],
            'GROUPBY' => 'E.id',
            'ORDER' => 'E.entities_id ASC'
        ]);

        $array = array();
        $temp_array = array();

        foreach($result as $data){
            $temp_array['id'] = $data['id'];
            $temp_array['entities_id'] = $data['entities_id'];
            $temp_array['name'] = $data['name'];
            $temp_array['allow_entity_only_transfer'] = $data['allow_entity_only_transfer'];
            $temp_array['justification_transfer'] = $data['justification_transfer'];
            $temp_array['allow_transfer'] = $data['allow_transfer'];
            array_push($array, $temp_array);
        }

        return $array;
    }

    /**
     * Get the groups to which tickets can be assigned
     *
     * @return array $allGroupsEntities
     */
    public function getGroupEntities()
    {
        global $DB;

        $result = $DB->request([
            'FROM' => 'glpi_groups',
            'WHERE' => ['is_assign' => 1],
            'ORDER' => ['entities_id ASC', 'id ASC']
        ]);

        $array = array();

        foreach($result as $data){
            array_push(
                $array, $data['id'], 
                $data['entities_id'], $data['name']
            );
        }

        return $array;
    }

    /**
     * If we are on tickets, an additional tab is displayed
     * 
     * @param object $item         Ticket
     * @param int    $tabnum       1
     * @param int    $withtemplate 0
     * 
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Ticket') {
            $profile = new self();
            $ID   = $item->getField('id');
            $profile->showFormMcv($ID);
        }

        return true;
    }

    public static function addStyleSheetAndScript() 
    {
        echo Html::css("/plugins/transferticketentity/css/style.css");
        echo Html::script("/plugins/transferticketentity/js/script.js");
    }

    /**
     * Return parent's entity name
     *
     * @return string
    */
    public function searchParentEntityName($id)
    {
        global $DB;

        $result = $DB->request([
            'FROM' => 'glpi_entities',
        ]);

        foreach ($result as $subArray) {
            if ($subArray['id'] == $id) {
                return $subArray['completename'];
            }
        }
    }

    /**
     * Display the ticket transfer form
     *
     * @return void
     */
    public function showFormMcv()
    {
        global $CFG_GLPI;
        global $DB;

        $getGroupEntities = self::getGroupEntities();
        $getEntitiesRights = self::getEntitiesRights();
        
        $technician_profile = $_SESSION['glpiactiveprofile']['id'];

        $getAllEntities = array();

        foreach ($getEntitiesRights as $entity) {
            if ($entity['allow_transfer'] == 1) {
                array_push($getAllEntities, $entity['entities_id'], $entity['id'], $entity['name']);
            }
        }

        if (empty($getAllEntities)) {
            self::addStyleSheetAndScript();
            echo "<div class='group_not_found'>";
                echo "<p>".
                    __("No entity available found, transfer impossible.", "transferticketentity")
                    ."</p>";
            echo "</div>";

            return false;
        }

        $theServer = explode("front/profile.form.php?",$_SERVER["HTTP_REFERER"]);
        $theServer = $theServer[0];

        $id_ticket = $_REQUEST['id'];

        $id_user = $_SESSION["glpiID"];
        $checkTicket = self::checkTicket();

        // Check if ticket is closed
        if ($checkTicket == false) {
            self::addStyleSheetAndScript();
            echo "<div class='unauthorised'>";
                echo "<p>".
                    __("Unauthorized transfer on closed ticket.", "transferticketentity")
                    ."</p>";
            echo "</div>";

            return false;
        }

        // In case JS is not functionnal
        echo "<div id='tt_gest_error'>";
            echo "<p>".__("Error, please reload the page.", "transferticketentity")."</p>";
            echo "<p>".__("If the problem persists, you can try to empty the cache by doing CTRL + F5.", "transferticketentity")."</p>";
        echo "</div>";
        
        $previousEntity = null;

        echo"
            <form class='form_transfert' action='" . $this->getFormURL() . "' method='post'>
                <div class='tt_entity_choice'>
                    <label for='entity_choice'>".__("Select ticket entity to transfer", "transferticketentity")." : </label>
                    <select name='entity_choice' id='entity_choice' style='width: 30%'>
                        <option selected disabled value=''>-- ".__("Choose your entity", "transferticketentity")." --</option>";
                foreach($getEntitiesRights as $entity) {
                    if ($entity['allow_transfer']) {
                        if ($entity['entities_id'] === null) {
                            echo "<optgroup label='" . __('No previous entity', 'transferticketentity') . "'>";
                                echo "<option value='" . $entity['id'] . "'>" . $entity['name'] . "</option>";
                        } else {
                            $searchParentEntityName = self::searchParentEntityName($entity['entities_id']);
                            if ($previousEntity != $searchParentEntityName) {
                                echo "</optgroup>";
                                echo "<optgroup label='" . $searchParentEntityName . "'>";
                            }
    
                            echo "<option value='" . $entity['id'] . "'>" . $entity['name'] . "</option>";
                            $previousEntity = $searchParentEntityName;
                        }
                    }
                }
                echo "</optgroup>
                    </select>
                </div>";

            echo " <div class='group_not_found' id='nogroupfound'>" .
                    __("No group found with « Assigned to » right while a group is required. Transfer impossible.", "transferticketentity")
                . "</div>";

            echo" <div class='tt_flex'>
                    <div class='tt_group_choice'>
                        <label for='group_choice'>".__("Select the group to assign", "transferticketentity")." : </label>
                        <select name='group_choice' id='group_choice' style='width: 30%'>
                            <option id='no_select' disabled value=''>-- ".__("Choose your group", "transferticketentity")." --</option>
                            <option value='' id='tt_none'> ".__("None", "transferticketentity")." </option>";
                for ($i = 0; $i < count($getGroupEntities); $i = $i+3) {
                    echo   "<option class='tt_plugin_entity_" . $getGroupEntities[$i+1] . "' value='" . $getGroupEntities[$i] . "'>" . $getGroupEntities[$i+2] . "</option>";
                }
                echo"   </select>

                        <div id='div_confirmation'>";
                            echo Html::submit(__('Confirm', 'transferticketentity'), ['disabled' => true, 'id' => 'tt_btn_open_modal_form']);
                echo"   </div>
                    </div>";

                    echo Html::hidden("technician_profile", ["value" => "$technician_profile"]);
                    echo Html::hidden("id_ticket", ["value" => "$id_ticket"]);
                    echo Html::hidden("id_user", ["value" => "$id_user"]);
                    echo Html::hidden("theServer", ["value" => "$theServer"]);

                echo "
                </div>

                <dialog id='tt_modal_form_adder' class='tt_modal'>
                    <h2>".__("Confirm transfer ?", "transferticketentity")."</h2>
                    <p>".__("Once the transfer has been completed, the ticket will remain visible only if you have the required rights.", "transferticketentity")."</p>
                    <div class='justification'>
                        <label for='justification'>".__("Please explain your transfer", "transferticketentity")." : </label>
                        <textarea id='justification' name='justification' required></textarea>
                    </div>
                    <p class='adv-msg'>".__("Warning, category will be reset if it does not exist in the target entity.", "transferticketentity")."</p>

                    <div>";
                        echo Html::submit(__('Cancel'), ['name' => 'canceltransfert', 'id' => 'canceltransfert']);
                        echo Html::submit(__('Confirm', 'transferticketentity'), ['name' => 'transfertticket', 'id' => 'transfertticket']);
            echo"   </div>
                </dialog>";
            Html::closeForm();
        self::addStyleSheetAndScript();
    }
}