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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginTransferticketentityTicket extends Ticket
{
    /**
     * Check the user profile
     *
     * @return array $checkProfiles
     */
    public function checkProfiles()
    {
        global $DB;

        $result = $DB->request([
            'SELECT' => 'id_profiles',
            'FROM' => 'glpi_plugin_transferticketentity_profiles'
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id_profiles']);
        }

        return $array;
    }

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
        $checkProfiles = self::checkProfiles();

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

        $id_ticket = $_SERVER["QUERY_STRING"];
        $id_ticket = preg_replace('/[^0-9]/', '', $id_ticket);
        $id_ticket = substr($id_ticket, 1);

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

        $id_ticket = $_SERVER["QUERY_STRING"];
        $id_ticket = preg_replace('/[^0-9]/', '', $id_ticket);
        $id_ticket = substr($id_ticket, 1);

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

        $id_ticket = $_SERVER["QUERY_STRING"];
        $id_ticket = preg_replace('/[^0-9]/', '', $id_ticket);
        $id_ticket = substr($id_ticket, 1);

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
     * Get all entities that have at least one group AND in use
     *
     * @return array $allEntities
     */
    public function getAllEntities()
    {
        global $DB;
        $getTicketEntity = self::getTicketEntity();
        $theEntity = $getTicketEntity[0];

        $result = $DB->request([
            'SELECT' => ['E.id', 'E.name'],
            'FROM' => 'glpi_groups AS G',
            'LEFT JOIN' => ['glpi_entities AS E' => ['FKEY' => ['G'     => 'entities_id',
                                                           'E' => 'id']]],
            'WHERE' => ['NOT' => ['G.entities_id' => 'NULL'], 'G.is_assign' => 1, 'NOT' => ['E.id' => $theEntity]],
            'GROUPBY' => 'E.id',
            'ORDER' => 'E.id ASC'
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id'], $data['name']);
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

    public static function addStyleSheetAndScript() {
        echo Html::css("/plugins/transferticketentity/css/style.css");
        echo Html::script("/plugins/transferticketentity/js/script.js");
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

        $getAllEntities = self::getAllEntities();
        $getGroupEntities = self::getGroupEntities();

        $theServer = explode("front/profile.form.php?",$_SERVER["HTTP_REFERER"]);
        $theServer = $theServer[0];

        $id_ticket = $_SERVER["QUERY_STRING"];
        $id_ticket = preg_replace('/[^0-9]/', '', $id_ticket);
        $id_ticket = substr($id_ticket, 1);

        $id_user = $_SESSION["glpiID"];
        $checkTicket = self::checkTicket();

        // Impossible de l'utiliser avec du JS ?
        // $entitiesValues = array(null);
        // $entitiesNames = array(" -- " . __("Choose your entity", "transferticketentity") . " -- ");

        // for ($i = 0; $i < count($getAllEntities); $i = $i+2) {
        //     array_push($entitiesValues, $getAllEntities[$i]);
        //     array_push($entitiesNames, $getAllEntities[$i+1]);
        // }
        
        // for ($i = 0; $i < count($entitiesValues); $i++) {
        //     $allSelectEntities[$entitiesValues[$i]] = $entitiesNames[$i];
        // }

        // $paramEntities = [
        //     'class' => 'entity_choice',
        //     'required' => true,
        //     'rand' => ''
        // ];
        
        // Dropdown::showFromArray('entity_choice', $allSelectEntities, $paramEntities);

        if ($checkTicket == false) {
            echo "<div class='unauthorised'>";
                echo "<p>".
                    __("Unauthorized transfer on closed ticket.", "transferticketentity")
                    ."</p>";
            echo "</div>";

            return false;
        }

        echo "<div id='tt_gest_error'>";
            echo "<p>".__("Error, please reload the page.", "transferticketentity")."</p>";
            echo "<p>".__("If the problem persists, you can try to empty the cache by doing CTRL + F5.", "transferticketentity")."</p>";
        echo "</div>";

        echo"
            <form class='form_transfert' action='../plugins/transferticketentity/inc/ticket.php' method='post'>
                <div class='tt_entity_choice'>
                    <label for='entity_choice'>".__("Select ticket entity to transfer", "transferticketentity")." : </label>
                    <select name='entity_choice' id='entity_choice'>
                        <option selected disabled value=''>-- ".__("Choose your entity", "transferticketentity")." --</option>";
                    for ($i = 0; $i < count($getAllEntities); $i = $i+2) {
                        echo "<option value='" . $getAllEntities[$i] . "'>" . $getAllEntities[$i+1] . "</option>";
                    }
                    echo "</select>
                </div>

                <div class='tt_flex'>
                    <div class='tt_group_choice'>
                        <label for='group_choice'>".__("Select the group to assign", "transferticketentity")." : </label>
                        <select name='group_choice' id='group_choice'>
                            <option id='no_select' disabled value=''>-- ".__("Choose your group", "transferticketentity")." --</option>";
                        for ($i = 0; $i < count($getGroupEntities); $i = $i+3) {
                            echo "<option class='tt_plugin_entity_" . $getGroupEntities[$i+1] . "' value='" . $getGroupEntities[$i] . "'>" . $getGroupEntities[$i+2] . "</option>";
                        }
                        echo "</select>
                    </div>

                    <div class='tt_hidden_value'>
                        <input type ='number' id='id_ticket' value= '$id_ticket' name='id_ticket' readonly>
                        <input type ='number' id='id_user' value= '$id_user' name='id_user' readonly>
                        <input type ='text' id='theServer' value= '$theServer' name='theServer' readonly>
                    </div>

                    <div id='div_confirmation'>
                        <button id='tt_btn_open_modal_form'>".__("Confirm", "transferticketentity")."</button>
                    </div>
                </div>

                <dialog id='tt_modal_form_adder' class='tt_modal'>
                    <h2>".__("Confirm transfer ?", "transferticketentity")."</h2>
                    <p>".__("Once the transfer has been completed, the ticket will remain visible only if you have the required rights.", "transferticketentity")."</p>
                    <div class='justification'>
                        <label for='justification'>".__("Please explain your transfer", "transferticketentity")." : </label>
                        <textarea name='justification' required></textarea>
                    </div>
                    <div>
                        <button type='submit' name='canceltransfert' id='canceltransfert'>".__("Cancel", "transferticketentity")."</button>
                        <button type='submit' name='transfertticket' id='transfertticket'>".__("Confirm", "transferticketentity")."</button>
                    </div>
                </dialog>";
            Html::closeForm();
        self::addStyleSheetAndScript();
    }
}