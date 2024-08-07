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
use Ticket_User;
use CommonITILActor;
use Group_Ticket;
use Ticket;
use TicketTask;
use Planning;

require '../../../inc/includes.php';

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
    // Session is not valid then exit
    exit;
}

class PluginTransferticketentityTransfer extends CommonDBTM
{
    public $checkTechRight;
    public $checkAssign;
    public $checkEntityETT;
    public $checkGroup;
    public $theEntity;
    public $checkEntityRight;
    public $theGroup;
    public $ticketTransferETT;

    public function __construct()
    {
        $this->checkTechRight = $this->checkTechRight();
        $this->checkAssign = $this->checkAssign();
        $this->checkEntityETT = $this->checkEntityETT();
        $this->checkGroup = $this->checkGroup();
        $this->theEntity = $this->theEntity();
        $this->checkEntityRight = $this->checkEntityRight();
        $this->theGroup = $this->theGroup();
        $this->ticketTransferETT = $this->ticketTransferETT();
    }

    /**
     * Checks technician's right
     *
     * @return bool
     */
    public function checkTechRight()
    {
        global $DB;

        $technician_profile = $_POST['technician_profile'];

        $result = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => ['name' => 'plugin_transferticketentity_bypass', 'profiles_id' => $technician_profile]
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['rights']);
        }

        return $array;
    }

    /**
     * Checks that the technician or his group is assigned to the ticket
     *
     * @return bool
     */
    public function checkAssign()
    {
        global $DB;

        $id_ticket = $_POST['id_ticket'];
        $id_user = $_POST['id_user'];
        $groupTech = array();

        $result = $DB->request([
            'SELECT' => 'groups_id',
            'FROM' => 'glpi_groups_users',
            'WHERE' => ['users_id' => $id_user]
        ]);

        foreach($result as $data){
            if(!in_array($data, $groupTech)) {
                array_push($groupTech, $data['groups_id']);
            }
        }

        $checkAssignedTech = array();
        $checkAssignedGroup = array();

        $result = $DB->request([
            'SELECT' => 'users_id',
            'FROM' => 'glpi_tickets_users',
            'WHERE' => ['tickets_id' => $id_ticket]
        ]);

        foreach($result as $data){
            if(!in_array($data, $checkAssignedTech)) {
                array_push($checkAssignedTech, $data['users_id']);
            }
        }

        $result = $DB->request([
            'SELECT' => 'groups_id',
            'FROM' => 'glpi_groups_tickets',
            'WHERE' => ['tickets_id' => $id_ticket]
        ]);

        foreach($result as $data){
            if(!in_array($data, $checkAssignedGroup)) {
                array_push($checkAssignedGroup, $data['groups_id']);
            }
        }

        $var_check = 0;

        if (in_array($id_user, $checkAssignedTech)) {
            $var_check++;
        }

        foreach($groupTech as $checkAssign) {
            if (in_array($checkAssign, $checkAssignedGroup)) {
                $var_check++;
            }
        }

        if ($var_check >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get only the entities with at least one active group
     *
     * @return array $checkEntityETT
     */
    public function checkEntityETT()
    {
        global $DB;
    
        $result = $DB->request([
            'SELECT' => ['E.id', 'E.entities_id', 'E.name', 'TES.allow_entity_only_transfer', 'TES.justification_transfer', 'TES.allow_transfer'],
            'FROM' => 'glpi_entities AS E',
            'LEFT JOIN' => ['glpi_plugin_transferticketentity_entities_settings AS TES' => ['FKEY' => ['E' => 'id',
                                                           'TES' => 'entities_id']]],
            'WHERE' => ['TES.allow_transfer' => 1],
            'GROUPBY' => 'E.id',
            'ORDER' => 'E.entities_id ASC'
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id']);
        }

        return $array;
    }

    /**
     * Get only the groups belonging to the selected entity
     *
     * @return array $checkGroup
     */
    public function checkGroup()
    {
        global $DB;
        
        $entity_choice = $_REQUEST['entity_choice'];
    
        $result = $DB->request([
            'SELECT' => 'glpi_groups.id',
            'FROM' => 'glpi_groups',
            'LEFT JOIN' => ['glpi_entities' => ['FKEY' => ['glpi_groups'     => 'entities_id',
                                                                'glpi_entities' => 'id']]],
            'WHERE' => ['glpi_groups.is_assign' => 1, 'glpi_entities.id' => $entity_choice],
            'ORDER' => 'glpi_entities.id ASC'
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id']);
        }

        return $array;
    }

    /**
     * Get the name of the selected entity
     *
     * @return $data
     */
    public function theEntity()
    {
        global $DB;
        $entity_choice = $_REQUEST['entity_choice'];

        $result = $DB->request([
            'SELECT' => 'name',
            'FROM' => 'glpi_entities',
            'WHERE' => ['id' => $entity_choice]
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['name']);
        }

        return $array[0];
    }

    /**
     * Get selected entity rights
     *
     * @return array
     */
    public function checkEntityRight()
    {
        global $DB;

        $entity_choice = $_REQUEST['entity_choice'];

        $result = $DB->request([
            'FROM' => 'glpi_plugin_transferticketentity_entities_settings',
            'WHERE' => ['entities_id' => $entity_choice]
        ]);

        $array = array();

        foreach($result as $data){
            $array['allow_entity_only_transfer'] = $data['allow_entity_only_transfer'];
            $array['justification_transfer'] = $data['justification_transfer'];
            $array['allow_transfer'] = $data['allow_transfer'];
            $array['keep_category'] = $data['keep_category'];
            $array['itilcategories_id'] = $data['itilcategories_id'];
        }

        return $array;
    }

    /**
     * Get the selected group name
     *
     * @return $data
     */
    public function theGroup()
    {
        global $DB;

        if (!empty($_REQUEST['group_choice'])) {
            $group_choice = $_REQUEST['group_choice'];
    
            $result = $DB->request([
                'SELECT' => 'name',
                'FROM' => 'glpi_groups',
                'WHERE' => ['id' => $group_choice]
            ]);
    
            $array = array();
    
            foreach($result as $data){
                array_push($array, $data['name']);
            }
    
            return $array[0];
        } else return false;
    }

    /**
     * Check if category exist in target entity
     *
     * @return bool
     */
    public function checkExistingCategory()
    {
        global $DB;

        $id_ticket = $_POST['id_ticket'];
        $targetEntity = $_REQUEST['entity_choice'];

        $result = $DB->request([
            'SELECT' => 'itilcategories_id',
            'FROM' => 'glpi_tickets',
            'WHERE' => ['id' => $id_ticket]
        ]);

        $getTicketCategory = '';

        foreach ($result as $data) {
            $getTicketCategory = $data['itilcategories_id'];
        }

        $result = $DB->request([
            'FROM' => 'glpi_entities',
            'WHERE' => ['id' => $targetEntity]
        ]);

        $ancestorsEntities = array();

        foreach ($result as $data) {
            if ($data['ancestors_cache']) {
                $ancestorsEntities = $data['ancestors_cache'];
                $ancestorsEntities = json_decode($ancestorsEntities, true);
                array_push($ancestorsEntities, $targetEntity);
            } else {
                array_push($ancestorsEntities, 0);
            }
        }
        
        $result = $DB->request([
            'FROM' => 'glpi_itilcategories',
            'WHERE' => ['id' => $getTicketCategory]
        ]);

        $getEntitiesFromCategoryTicket = '';
        $isRecursiveCategory = '';

        foreach ($result as $data) {
            $getEntitiesFromCategoryTicket = $data['entities_id'];
            $isRecursiveCategory = $data['is_recursive'];
        }

        if (!$isRecursiveCategory) {
            if ($getEntitiesFromCategoryTicket == $targetEntity) {
                $isRecursiveCategory = true;
            }
        }

        if (in_array($getEntitiesFromCategoryTicket, $ancestorsEntities) && $isRecursiveCategory) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check GLPIs mandatory fields
     *
     * @return int
     */
    public function checkTicketTemplateID()
    {
        global $DB;

        $entity_choice = $_REQUEST['entity_choice'];
        $selectedTemplate = 0;

        while (!$selectedTemplate) {
            $query = [
                'FROM' => 'glpi_entities',
                'WHERE' => ['id' => $entity_choice]
            ];

            foreach ($DB->request($query) as $data) {
                $id = $data['id'];
                $entity_choice = $data['entities_id'];
                $selectedTemplate = $data['tickettemplates_id'];
            }
            
            if ((!$id && !$selectedTemplate) || ($entity_choice === null && !$selectedTemplate)) {
                return 0;
            }
        }

        return $selectedTemplate;
    }

    /**
     * Check GLPIs mandatory fields
     *
     * @return boolean
     */
    public function checkMandatoryCategory()
    {
        global $DB;

        $id_ticket = $_POST['id_ticket'];
        $tickettemplates_id = self::checkTicketTemplateID();

        $ttm_class = 'Ticket' . 'TemplateMandatoryField'; 
        $ttm = new $ttm_class();
        $mandatoryFields = $ttm->getMandatoryFields($tickettemplates_id);

        $result = $DB->request([
            'FROM' => 'glpi_tickets',
            'WHERE' => ['id' => $id_ticket]
        ]);

        $ticketFields = array();

        foreach($result as $data){
            array_push($ticketFields, $data);
        }

        $ticketFields = $ticketFields[0];

        // Check if category field is mandatory
        foreach ($mandatoryFields as $mandatoryField => $MF) {
            if ($mandatoryField == 'itilcategories_id') {
                return true;
            }
        }

        return false;
    }

    /**
     * Carries out the necessary actions for the transfer entity
     * 
     * @return void
     */
    public function ticketTransferETT()
    {
        global $CFG_GLPI;
        global $DB;

        if (isset($_POST['transfertticket'])) {
            $checkAssign = self::checkAssign();
            $checkTechRight = self::checkTechRight();
            $checkEntity = self::checkEntityETT();
            $checkGroup = self::checkGroup();
            $checkEntityRight = self::checkEntityRight();
            $checkExistingCategory = self::checkExistingCategory();
            $checkMandatoryCategory = self::checkMandatoryCategory();

            $id_ticket = $_POST['id_ticket'];
            $theServer = $_POST['theServer'];
            $justification = $_POST['justification'];
            $requiredGroup = true;

            $theEntity = self::theEntity();
            $theGroup = self::theGroup();
            
            $entity_choice = $_REQUEST['entity_choice'];
            $group_choice = $_REQUEST['group_choice'];

            if (!isset($_POST['justification']) || $_POST['justification'] == '') {
                if ($checkEntityRight['justification_transfer'] == 1) {
                    Session::addMessageAfterRedirect(
                        __(
                            "Please explain your transfer", 
                            'transferticketentity'
                        ),
                        true,
                        ERROR
                    );
        
                    header('location:' . $theServer);

                    return false;
                } else {
                    $justification = '';
                }
            }

            if (empty($group_choice) && $checkEntityRight['allow_entity_only_transfer'] == 1) {
                Session::addMessageAfterRedirect(
                    __(
                        "Please select a valid group", 
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
    
                header('location:' . $theServer);

                return false;
            } else if (empty($group_choice) && $checkEntityRight['allow_entity_only_transfer'] == 0) {
                $requiredGroup = false;
            }

            if (!$checkTechRight[0] && !$checkAssign) {
                Session::addMessageAfterRedirect(
                    __(
                        "You must be assigned to the ticket to be able to transfer it", 
                        'transferticketentity'
                    ),
                true,
                ERROR
                );

                header('location:' . $theServer);
            } else if (!in_array($entity_choice, $checkEntity)) {
                // Check that the selected entity belongs to those available
                Session::addMessageAfterRedirect(
                    __(
                        "Please select a valid entity", 
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
    
                header('location:' . $theServer);
            } else if (!empty($group_choice) && !in_array($group_choice, $checkGroup)) {
                Session::addMessageAfterRedirect(
                    __(
                        "Please select a valid group", 
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
    
                header('location:' . $theServer);
            }
            else { 
                // Change the entity ticket and set its status to processing (assigned)
                $ticket = new Ticket();

                $ticket_update = [
                    'id'     => $id_ticket,
                    'entities_id' => $entity_choice,
                ];

                if ($theGroup) {
                    $ticket_status = ['status' => 2];
                    $ticket_update = array_merge($ticket_update, $ticket_status);
                } else {
                    $ticket_status = ['status' => 1];
                    $ticket_update = array_merge($ticket_update, $ticket_status);
                }

                // In case keep_category is at yes and category doesn't exist, reset category's ticket
                if ($checkEntityRight['keep_category'] && !$checkExistingCategory) {
                    $ticket_category = ['itilcategories_id' => 0];
                    $ticket_update = array_merge($ticket_update, $ticket_category);
                }

                if (!$checkEntityRight['keep_category']) {
                    if ($checkEntityRight['itilcategories_id'] == null) {
                        $ticket_category = ['itilcategories_id' => 0];
                    } else {
                        $ticket_category = ['itilcategories_id' => $checkEntityRight['itilcategories_id']];
                    }
                    $ticket_update = array_merge($ticket_update, $ticket_category);
                }

                // If category is mandatory with GLPIs template and category will be null
                if ($ticket_category['itilcategories_id'] == 0 && $checkMandatoryCategory) {
                    Session::addMessageAfterRedirect(
                        __(
                            "Category will be set to null but its configured as mandatory in GLPIs template, please contact your administrator.", 
                            'transferticketentity'
                        ),
                        true,
                        ERROR
                    );
        
                    header('location:' . $theServer);

                    return false;
                }

                // Remove the link with the current user
                $delete_link_user = [
                    'tickets_id' => $id_ticket,
                    'type' => CommonITILActor::ASSIGN
                ];

                $ticket_user = new Ticket_User();
                $found_user = $ticket_user->find($delete_link_user);

                foreach ($found_user as $id => $tu) {
                    //delete user
                    $ticket_user->delete(['id' => $id]);
                }

                // Remove the link with the current group
                $delete_link_group = [
                    'tickets_id' => $id_ticket,
                    'type' => CommonITILActor::ASSIGN
                ];

                $group_ticket = new Group_Ticket();
                $found_group = $group_ticket->find($delete_link_group);

                foreach ($found_group as $id => $tu) {
                    //delete group
                    $group_ticket->delete(['id' => $id]);
                }
                
                $ticket->update($ticket_update);

                if ($requiredGroup) {
                    // Change group ticket
                    $group_check = [
                        'tickets_id' => $id_ticket,
                        'groups_id' => $group_choice,
                        'type' => CommonITILActor::ASSIGN
                    ];

                    if (!$group_ticket->find($group_check)) {
                        $group_ticket->add($group_check);
                    } else {
                        $group_ticket->update($group_check);
                    }
                }

                $groupText = "<br> <br> $justification";

                if ($theGroup) {
                    $groupText = __("in the group", "transferticketentity") . " $theGroup \n <br> <br> $justification";
                }

                // Log the transfer in a task
                $task = new TicketTask();
                $task->add([
                    'tickets_id' => $id_ticket,
                    'is_private' => true,
                    'state'      => Planning::INFO,
                    'content'    => __(
                        "Escalation to", 
                        "transferticketentity"
                    ) . " $theEntity " . $groupText
                ]);
    
                Session::addMessageAfterRedirect(
                    __(
                        "Successful transfer for ticket n° : ", 
                        "transferticketentity"
                    ) . $id_ticket,
                    true,
                    INFO
                );
    
                $theServer = explode("/ticket.form.php",$theServer);
                $theServer = $theServer[0];
                header('location:' . $theServer . '/central.php');
            }

        } else if (isset($_POST['canceltransfert'])) {
            $id_ticket = $_POST['id_ticket'];
            $theServer = $_POST['theServer'];

            Session::addMessageAfterRedirect(
                __("Transfer canceled", 'transferticketentity'),
                true,
                ERROR
            );

            header('location:' . $theServer);
        }
    }
}

$transferticketentityTicket = new PluginTransferticketentityTransfer();