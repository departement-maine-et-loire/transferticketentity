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

require '../../../inc/includes.php';

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
    // Session is not valid then exit
    exit;
}

class PluginTransferticketentityTicket extends CommonDBTM
{
    public $checkAssign;
    public $checkEntityETT;
    public $checkGroup;
    public $theEntity;
    public $theGroup;
    public $ticketTransferETT;

    public function __construct()
    {
        $this->checkAssign = $this->checkAssign();
        $this->checkEntityETT = $this->checkEntityETT();
        $this->checkGroup = $this->checkGroup();
        $this->theEntity = $this->theEntity();
        $this->theGroup = $this->theGroup();
        $this->ticketTransferETT = $this->ticketTransferETT();
    }

    /**
     * Checks that the technician or his group is assigned to the ticket
     *
     * @return bool
     */
    public function checkAssign() {
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
    
        $query = "SELECT E.id
        FROM glpi_groups G
        LEFT JOIN glpi_entities E ON E.id = G.entities_id
        WHERE G.entities_id IS NOT NULL
        AND G.is_assign = 1
        GROUP BY E.id
        ORDER BY E.id";
    
        $result = $DB->query($query);
    
        $checkEntity = array();
    
        foreach ($result as $data) {
            array_push($checkEntity, $data['id']);
        }
    
        return $checkEntity;

        // Test ok
        // $result = $DB->request([
        //     'SELECT' => 'glpi_entities.id',
        //     'FROM' => 'glpi_groups',
        //     'LEFT JOIN' => ['glpi_entities' => ['FKEY' => ['glpi_groups'     => 'entities_id',
        //                                                         'glpi_entities' => 'id']]],
        //     'WHERE' => ['glpi_groups.is_assign' => 1],
        //     'GROUPBY' => 'glpi_entities.id',
        //     'ORDER' => 'glpi_entities.id ASC'
        // ]);

        // $array = array();

        // foreach($result as $data){
        //     array_push($array, $data['id']);
        // }

        // return $array;
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
    
        $query = "SELECT G.id
        FROM glpi_groups G
        LEFT JOIN glpi_entities E ON E.id = G.entities_id
        WHERE G.entities_id IS NOT NULL
        AND G.is_assign = 1
        AND E.id = $entity_choice
        ORDER BY E.id";
    
        $result = $DB->query($query);
    
        $checkGroup = array();
    
        foreach ($result as $data) {
            array_push($checkGroup, $data['id']);
        }
    
        return $checkGroup;

        // Test ok
        // $result = $DB->request([
        //     'SELECT' => 'glpi_groups.id',
        //     'FROM' => 'glpi_groups',
        //     'LEFT JOIN' => ['glpi_entities' => ['FKEY' => ['glpi_groups'     => 'entities_id',
        //                                                         'glpi_entities' => 'id']]],
        //     'WHERE' => ['glpi_groups.is_assign' => 1, 'glpi_entities.id' => $entity_choice],
        //     'ORDER' => 'glpi_entities.id ASC'
        // ]);

        // $array = array();

        // foreach($result as $data){
        //     array_push($array, $data['id']);
        // }

        // return $array;
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

        $query = "SELECT name
        FROM glpi_entities
        WHERE id = $entity_choice";
  
        $result = $DB->query($query);

        foreach ($result as $data) {
            return $data['name'];
        }

        // Test ok
        // $result = $DB->request([
        //     'SELECT' => 'name',
        //     'FROM' => 'glpi_entities',
        //     'WHERE' => ['id' => $entity_choice]
        // ]);

        // $array = array();

        // foreach($result as $data){
        //     array_push($array, $data['name']);
        // }

        // return $array;
    }

    /**
     * Get the selected group name
     *
     * @return $data
     */
    public function theGroup()
    {
        global $DB;
        $group_choice = $_REQUEST['group_choice'];

        $query = "SELECT name
        FROM glpi_groups
        WHERE id = $group_choice";
  
        $result = $DB->query($query);

        foreach ($result as $data) {
            return $data['name'];
        }

        // Test ok
        // $result = $DB->request([
        //     'SELECT' => 'name',
        //     'FROM' => 'glpi_groups',
        //     'WHERE' => ['id' => $group_choice]
        // ]);

        // $array = array();

        // foreach($result as $data){
        //     array_push($array, $data['name']);
        // }

        // return $array;
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
            $checkEntity = self::checkEntityETT();
            $checkGroup = self::checkGroup();

            $id_ticket = $_POST['id_ticket'];
            $id_user = $_POST['id_user'];
            $theServer = $_POST['theServer'];
            $justification = $_POST['justification'];

            $theEntity = self::theEntity();
            $theGroup = self::theGroup();
            
            $entity_choice = $_REQUEST['entity_choice'];
            $group_choice = $_REQUEST['group_choice'];

            if (!$checkAssign) {
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
                        "Veuillez sélectionner une entité valide", 
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
    
                header('location:' . $theServer);
            } else if (!in_array($group_choice, $checkGroup)) {
                Session::addMessageAfterRedirect(
                    __(
                        "Veuillez sélectionner un groupe valide", 
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
    
                header('location:' . $theServer);
            } else if (!isset($_POST['justification']) || $_POST['justification'] == '') {
                Session::addMessageAfterRedirect(
                    __(
                        "Please explain your transfer", 
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
    
                header('location:' . $theServer);
            }
            else { 
                // Remove the link with the current user
                $ticket_user = new Ticket_User();
                $ticket_user->delete(
                    [
                    'id'     => $id_ticket,
                    'type' => CommonITILActor::ASSIGN
                    ]
                );

                // Remove the link with the current group
                $group_ticket = new Group_Ticket();
                $group_ticket->delete(
                    [
                    'id'     => $id_ticket,
                    'type' => CommonITILActor::ASSIGN
                    ]
                );

                // Change the entity ticket and set its status to processing (assigned)
                $ticket = new Ticket();
                $ticket->update(
                    [
                    'id'     => $id_ticket,
                    'entities_id' => $entity_choice,
                    'status' => 2
                    ]
                );

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

                // Log the transfer in a task
                $itil_followup = new ITILFollowup();
                $itil_followup->add(
                    [
                    'itemtype' => 'Ticket',
                    'items_id' => $id_ticket,
                    'users_id' => $id_user,
                    'content' => __(
                        "Escalade vers", 
                        "transferticketentity"
                    ) . " $theEntity " .
                    __("dans le groupe", "transferticketentity") . " $theGroup \n <br> <br> $justification"
                    ]
                );
    
                Session::addMessageAfterRedirect(
                    __(
                        "Transfert réussi pour le ticket n° : ", 
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
                __("Transfert annulé", 'transferticketentity'),
                true,
                ERROR
            );

            header('location:' . $theServer);
        }
    }
}

$transferticketentityTicket = new PluginTransferticketentityTicket();