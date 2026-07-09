<?php
/**
 * -------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Transferticketentity plugin for GLPI.
 *
 * Transferticketentity is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Transferticketentity is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Reports. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Ticket
 * @package   Transferticketentity
 * @author    Département de Maine et Loire, Y.COMBA, I.MELLOUK
 * @copyright 2015-2023 Département de Maine et Loire plugin team
 * @license   AGPL License 3.0 or (at your option) any later version
 * https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/departement-maine-et-loire/
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Transferticketentity;

use Ajax;
use CommonDBTM;
use CommonITILActor;
use CommonITILObject;
use CommonGLPI;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Group;
use Group_Ticket;
use Group_User;
use Html;
use MassiveAction;
use Planning;
use Session;
use Ticket_User;
use TicketTask;
use TicketTemplateMandatoryField;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class Ticket extends CommonDBTM
{
    public static $rightname = "plugin_transferticketentity_use";


    /**
     * @return string
     */
    public static function getIcon()
    {
        return "ti ti-transfer";
    }

    /**
     * Get all the entities which aren't the current entity with their rights
     *
     * @return array
     */
    public function getEntitiesRights($entities_id)
    {
        $array = [];
        $entity_config = new Entity();
        $entities = $entity_config->find(['NOT' => ['entities_id' => $entities_id]]);

        if (count($entities) > 0) {
            foreach ($entities as $data) {
                $temp_array['id'] = $data['id'];
                $temp_array['entities_id'] = $data['entities_id'];
                $entity = new \Entity();
                $entity->getFromDB($data['entities_id']);
                $temp_array['name'] = $entity->getName();
                $temp_array['allow_entity_only_transfer'] = $data['allow_entity_only_transfer'];
                $temp_array['justification_transfer'] = $data['justification_transfer'];
                $temp_array['allow_transfer'] = $data['allow_transfer'];
                array_push($array, $temp_array);
            }
        }

        return $array;
    }

    /**
     * Get the groups to which tickets can be assigned
     *
     * @return array $allGroupsEntities
     */
    public static function getGroupEntities($entities)
    {
        global $DB;

        $array = [];

        $criteria = [
            'SELECT' => ['id', 'name'],
            'FROM' => 'glpi_groups',
            'WHERE' => [
                'is_assign' => 1,
            ],
            'ORDERBY' => 'name'
        ];
        $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
            'glpi_groups', '', $entities, true
        );

        $iterator = $DB->request($criteria);

        if (count($iterator) > 0) {
            foreach ($iterator as $data) {
                $array[$data['id']] = $data['name'];
            }
        }

        return $array;
    }


    /**
     * Display the ticket transfer form
     *
     * @return void
     */
    public function showFormMcv($ticket)
    {
        $params['id_ticket'] = $ticket->getID();
        $params['id_user']   = $_SESSION['glpiID'];

        $entity = new \Entity();
        $entity->getFromDB($ticket->fields['entities_id']);
        $checkAssign = self::checkAssign($params);
        if (!Session::haveright("plugin_transferticketentity_bypass", READ) && !$checkAssign) {
            echo "<div class='alert alert-danger'>";
            echo "<p>" .
                __("You must be assigned to the ticket to be able to transfer it", "transferticketentity")
                . "</p>";
            echo "</div>";

            return false;
        }

        $getEntitiesRights = self::getEntitiesRights($ticket->fields['entities_id']);

        if (!Session::haveRight('ticket', UPDATE)) {
            echo "<div class='alert alert-danger'>";
            echo "<p>" .
                __("You don't have right to update tickets. Please contact your administrator.", "transferticketentity")
                . "</p>";
            echo "</div>";

            return false;
        }

        if (count($getEntitiesRights) == 0) {
            echo "<div class='alert alert-danger'>";
            echo "<p>" .
                __("No entity available found, transfer impossible.", "transferticketentity")
                . "</p>";
            echo "</div>";

            return false;
        }

        // Check if ticket is closed
        if ($ticket->fields['status'] == CommonITILObject::CLOSED) {
            echo "<div class='alert alert-danger'>";
            echo "<p>" .
                __("Unauthorized transfer on closed ticket.", "transferticketentity")
                . "</p>";
            echo "</div>";

            return false;
        }


        $entities_selection[0] = Dropdown::EMPTY_VALUE;
        foreach ($getEntitiesRights as $getEntitiesRight) {
            if ($getEntitiesRight['allow_transfer']) {
                $entities_selection[$getEntitiesRight['entities_id']] = $getEntitiesRight['name'];
            }
        }

        $target = $this->getFormURL();

        TemplateRenderer::getInstance()->display(
            '@transferticketentity/ticket.html.twig',
            [
                'can_edit'       => Session::haveRight(self::$rightname, READ),
                'root_plugin'    => PLUGIN_TRANSFERTICKETENTITY_WEBDIR,
                'action'         => $target,
                'id_ticket'      => $ticket->getID(),
                'id_user'        => $_SESSION['glpiID'],
                'entities_id'    => $ticket->getID(),
                'entities_name'  => $entity->getName(),
                'entities'       => $entities_selection,
            ],
        );
    }


    /**
     * Checks that the technician or his group is assigned to the ticket
     *
     * @return bool
     */
    public static function checkAssign($params)
    {
        $ticket = new \Ticket();
        $ticket->getfromDB($params['id_ticket']);

        $id_user = $params['id_user'];

        $groupTech = Group_User::getUserGroups($id_user);

        $checkAssignedTech  = [];
        $checkAssignedGroup = [];

        $users_ticket = $ticket->getUsers(CommonITILActor::ASSIGN);
        foreach ($users_ticket as $data) {
            array_push($checkAssignedTech, $data['users_id']);
        }
        $groups_ticket = $ticket->getGroups(CommonITILActor::ASSIGN);
        foreach ($groups_ticket as $data) {
            array_push($checkAssignedGroup, $data['groups_id']);
        }

        $var_check = 0;

        if (in_array($id_user, $checkAssignedTech)) {
            $var_check++;
        }

        foreach ($groupTech as $checkAssign) {
            if (in_array($checkAssign['id'], $checkAssignedGroup)) {
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
    public static function checkEntityETT()
    {
        $array = [];

        $entity_config = new Entity();
        $entities = $entity_config->find(['allow_transfer' => 1]);
        foreach ($entities as $data) {
            array_push($array, $data['entities_id']);
        }

        return $array;
    }

    /**
     * Get only the groups belonging to the selected entity
     *
     * @return array $checkGroup
     */
    public static function checkGroup($params)
    {
        global $DB;

        $array = [];

        $entities = $params['entity_choice'];

        $criteria = [
            'SELECT' => 'id',
            'FROM'   => 'glpi_groups',
            'WHERE'  => [
                'is_assign' => 1,
            ],
            'ORDERBY' => 'name'
        ];
        $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
            'glpi_groups', '', $entities, true
        );

        $iterator = $DB->request($criteria);

        if (count($iterator) > 0) {
            foreach ($iterator as $data) {
                array_push($array, $data['id']);
            }
        }

        return $array;
    }


    /**
     * Check if category exist in target entity
     *
     * @return bool
     */
    public static function checkExistingCategory($params)
    {
        global $DB;

        $id_ticket   = $params['id_ticket'];
        $targetEntity = $params['entity_choice'];

        $result = $DB->request([
            'SELECT' => 'itilcategories_id',
            'FROM'   => 'glpi_tickets',
            'WHERE'  => ['id' => $id_ticket]
        ]);

        $getTicketCategory = '';

        foreach ($result as $data) {
            $getTicketCategory = $data['itilcategories_id'];
        }

        $result = $DB->request([
            'FROM'  => 'glpi_entities',
            'WHERE' => ['id' => $targetEntity]
        ]);

        $ancestorsEntities = [];

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
            'FROM'  => 'glpi_itilcategories',
            'WHERE' => ['id' => $getTicketCategory]
        ]);

        $getEntitiesFromCategoryTicket = '';
        $isRecursiveCategory           = '';

        foreach ($result as $data) {
            $getEntitiesFromCategoryTicket = $data['entities_id'];
            $isRecursiveCategory           = $data['is_recursive'];
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
     * If the profile is authorised, add an extra tab
     *
     * @param object $item Ticket
     * @param int $withtemplate 0
     *
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == \Ticket::class
            && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
            return self::createTabEntry(__("Transfer Ticket Entity", "transferticketentity"));
        }
        return '';
    }

    /**
     * If we are on tickets, an additional tab is displayed
     *
     * @param object $item Ticket
     * @param int $tabnum 1
     * @param int $withtemplate 0
     *
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == \Ticket::class
            && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $ticket = new self();
            $ticket->showFormMcv($item);
        }

        return true;
    }


    /**
     * Check GLPIs mandatory fields
     *
     * @return boolean
     */
    public static function checkMandatoryCategory($params)
    {
        $entity = new \Entity();
        $entity->getFromDB($params['entity_choice']);

        $tickettemplates_id = \Entity::getUsedConfig('tickettemplates_strategy', $params['entity_choice'], 'tickettemplates_id', 0);

        $ttm = new TicketTemplateMandatoryField();
        $mandatoryFields = $ttm->getMandatoryFields($tickettemplates_id);

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
    public function launchTicketTransfer($params)
    {
        global $CFG_GLPI;

        $checkAssign  = self::checkAssign($params);
        $checkEntity  = self::checkEntityETT();
        $checkGroup   = self::checkGroup($params);

        $checkEntityRight = Entity::checkEntityRight($params);

        $checkExistingCategory = self::checkExistingCategory($params);

        $checkMandatoryCategory = self::checkMandatoryCategory($params);

        $justification = $params['justification'];
        $requiredGroup = true;

        $entity = new \Entity();
        $entity->getfromDB($params['entity_choice']);
        $theEntity = $entity->getName();

        $group = new Group();
        $group->getfromDB($params['group_choice']);

        if (!isset($params['justification'])
            || $params['justification'] == '') {
            if ($checkEntityRight['justification_transfer'] == 1) {
                Session::addMessageAfterRedirect(
                    __(
                        "Please explain your transfer",
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );

                Html::back();
            } else {
                $justification = '';
            }
        }

        if (empty($params['group_choice'])
            && $checkEntityRight['allow_entity_only_transfer'] == 1) {
            Session::addMessageAfterRedirect(
                __(
                    "Please select a valid group",
                    'transferticketentity'
                ),
                true,
                ERROR
            );

            Html::back();
        } elseif (empty($params['group_choice'])
            && $checkEntityRight['allow_entity_only_transfer'] == 0) {
            $requiredGroup = false;
        }

        if (!Session::haveright("plugin_transferticketentity_bypass", READ)
            && !$checkAssign) {
            Session::addMessageAfterRedirect(
                __(
                    "You must be assigned to the ticket to be able to transfer it",
                    'transferticketentity'
                ),
                true,
                ERROR
            );

            Html::back();
        } elseif (!in_array($params['entity_choice'], $checkEntity)) {
            // Check that the selected entity belongs to those available
            Session::addMessageAfterRedirect(
                __(
                    "Please select a valid entity",
                    'transferticketentity'
                ),
                true,
                ERROR
            );

            Html::back();
        } elseif (!empty($params['group_choice'])
            && !in_array($params['group_choice'], $checkGroup)) {
            Session::addMessageAfterRedirect(
                __(
                    "Please select a valid group",
                    'transferticketentity'
                ),
                true,
                ERROR
            );

            Html::back();
        } else {
            // Change the entity ticket and set its status to processing (assigned)
            $ticket = new \Ticket();

            $ticket_update = [
                'id'          => $params['id_ticket'],
                'entities_id' => $params['entity_choice'],
            ];

            if ($params['group_choice'] && $params['group_choice'] > 0) {
                $ticket_status = ['status' => CommonITILObject::ASSIGNED];
                $ticket_update = array_merge($ticket_update, $ticket_status);
            } else {
                $ticket_status = ['status' => CommonITILObject::INCOMING];
                $ticket_update = array_merge($ticket_update, $ticket_status);
            }

            // In case keep_category is at yes and category doesn't exist, reset category's ticket
            if ($checkEntityRight['keep_category'] && !$checkExistingCategory) {
                $ticket_category = ['itilcategories_id' => 0];
                $ticket_update   = array_merge($ticket_update, $ticket_category);
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
            if ($ticket_category['itilcategories_id'] == 0
                && $checkMandatoryCategory) {
                Session::addMessageAfterRedirect(
                    __(
                        "Category will be set to null but its configured as mandatory in GLPIs template, please contact your administrator.",
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );

                Html::back();
            }

            // Remove the link with the current user
            $delete_link_user = [
                'tickets_id' => $params['id_ticket'],
                'type'       => CommonITILActor::ASSIGN
            ];

            $ticket_user = new Ticket_User();
            $found_user  = $ticket_user->find($delete_link_user);

            foreach ($found_user as $id => $tu) {
                $ticket_user->delete(['id' => $id]);
            }

            // Remove the link with the current group
            $delete_link_group = [
                'tickets_id' => $params['id_ticket'],
                'type'       => CommonITILActor::ASSIGN
            ];

            $group_ticket = new Group_Ticket();
            $found_group  = $group_ticket->find($delete_link_group);

            foreach ($found_group as $id => $tu) {
                $group_ticket->delete(['id' => $id]);
            }

            $ticket->update($ticket_update);

            // Add current user as observer if requested
            if (isset($params['add_as_observer']) && $params['add_as_observer'] == 1) {
                $ticket_user  = new Ticket_User();
                $observer_data = [
                    'tickets_id' => $params['id_ticket'],
                    'users_id'   => $_SESSION['glpiID'],
                    'type'       => CommonITILActor::OBSERVER
                ];
                if (!$ticket_user->find($observer_data)) {
                    $ticket_user->add($observer_data);
                }
            }

            if ($requiredGroup) {
                // Change group ticket
                $group_check = [
                    'tickets_id' => $params['id_ticket'],
                    'groups_id'  => $params['group_choice'],
                    'type'       => CommonITILActor::ASSIGN
                ];

                if (!$group_ticket->find($group_check)) {
                    $group_ticket->add($group_check);
                } else {
                    $group_ticket->update($group_check);
                }
            }

            $groupText = "<br> <br> $justification";

            if ($params['group_choice'] && $params['group_choice'] > 0) {
                $groupText = __("in the group", "transferticketentity") . " " . $group->getName() . "\n <br> <br> $justification";
            }

            // Log the transfer in a task
            $task = new TicketTask();
            $task->add([
                'tickets_id' => $params['id_ticket'],
                'is_private' => true,
                'state'      => Planning::INFO,
                'content'    => __(
                    "Escalation to",
                    "transferticketentity"
                ) . " $theEntity " . $groupText
            ]);

            $ticket = new \Ticket();
            $ticket->getFromDB($params['id_ticket']);
            Session::addMessageAfterRedirect(
                __(
                    "Successful transfer for ticket n° : ",
                    "transferticketentity"
                ) . $ticket->getLink(),
                true,
                INFO
            );

            Html::redirect($CFG_GLPI["root_doc"] . "/front/ticket.form.php?id=" . $ticket->getID());
        }
    }


    // =========================================================================
    // MASSIVE ACTIONS
    // =========================================================================

    /**
     * Get specific massive actions available for tickets.
     *
     * @param mixed $checkitem Not used here
     *
     * @return array
     */
    public function getSpecificMassiveActions($checkitem = null): array
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        if (Session::haveRight('plugin_transferticketentity_massive', READ)) {
            $actions[self::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer_entity'] =
                __('Transfer to another entity', 'transferticketentity');
        }

        return $actions;
    }

    /**
     * Display the form fields for the massive action "transfer_entity".
     *
     * @param MassiveAction $ma Massive action object
     *
     * @return bool
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma): bool
    {
        if ($ma->getAction() === 'transfer_entity') {
            $ticket_obj = new self();

            $ids             = array_keys($ma->getItems()[\Ticket::class] ?? []);
            $first_ticket_id = reset($ids);
            $fake_params     = ['id_ticket' => $first_ticket_id ?: 0];

            // Build entities list (only those with allow_transfer = 1)
            $entity_config  = new Entity();
            $all_entities   = $entity_config->find(['allow_transfer' => 1]);
            $entities_list  = [];
            foreach ($all_entities as $ec) {
                $e = new \Entity();
                $e->getFromDB($ec['entities_id']);
                $entities_list[$ec['entities_id']] = $e->getName();
            }
            asort($entities_list);

            // Build groups list (all assignable groups)
            global $DB;
            $groups_list = [];
            $iterator = $DB->request([
                'SELECT'  => ['id', 'name', 'entities_id'],
                'FROM'    => 'glpi_groups',
                'WHERE'   => ['is_assign' => 1],
                'ORDERBY' => ['entities_id ASC', 'name ASC'],
            ]);
            foreach ($iterator as $row) {
                $groups_list[] = $row;
            }

            echo "<table class='tab_cadre_fixe' style='width:50%; margin-top:10px;'>";

            // Target entity
            echo "<tr>
                    <td style='width:200px; text-align:right; padding-right:10px;'>
                        " . __('Target entity', 'transferticketentity') . "
                    </td>
                    <td>
                        <select name='target_entity_id' id='target_entity_id' style='width:250px;'>
                            <option value=''>-- " . __("Choose an entity", "transferticketentity") . " --</option>";
            foreach ($entities_list as $eid => $ename) {
                echo "<option value='{$eid}'>" . htmlspecialchars($ename) . "</option>";
            }
            echo "      </select>
                    </td>
                </tr>";

            // Group to assign
            echo "<tr>
                    <td style='text-align:right; padding-right:10px;'>
                        " . __('Group to assign', 'transferticketentity') . "
                    </td>
                    <td>
                        <select name='group_choice' id='group_choice_massive' style='width:250px;'>
                            <option value=''>-- " . __('No group', 'transferticketentity') . " --</option>";
            foreach ($groups_list as $g) {
                echo "<option class='group_option entity_{$g['entities_id']}'
                              value='{$g['id']}'
                              style='display:none'>"
                    . htmlspecialchars($g['name']) .
                    "</option>";
            }
            echo "      </select>
                    </td>
                </tr>";

            echo "</table>";

            // JS to filter groups by selected entity
            echo Html::scriptBlock("
                function updateGroupOptionsTTE(entityId) {
                    const allOptions = document.querySelectorAll('#group_choice_massive option.group_option');
                    allOptions.forEach(function(opt) {
                        opt.style.display = opt.classList.contains('entity_' + entityId) ? '' : 'none';
                    });
                }

                function updateSubmitStateTTE() {
                    const entitySelect = document.getElementById('target_entity_id');
                    const groupSelect  = document.getElementById('group_choice_massive');
                    const submitBtn    = document.getElementById('tte_massive_submit');

                    if (entitySelect && groupSelect && submitBtn) {
                        submitBtn.disabled = (entitySelect.value === '' || groupSelect.value === '');
                    }
                }

                document.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'target_entity_id') {
                        updateGroupOptionsTTE(e.target.value);
                        const groupSelect = document.getElementById('group_choice_massive');
                        if (groupSelect) groupSelect.value = '';
                    }
                    if (e.target && (e.target.id === 'target_entity_id' || e.target.id === 'group_choice_massive')) {
                        updateSubmitStateTTE();
                    }
                });

                updateSubmitStateTTE();
            ");

            echo "<br><input type='submit' class='submit' name='massiveaction' id='tte_massive_submit' value='" . __('Transfer', 'transferticketentity') . "' disabled>";

            return true;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    /**
     * Process the massive action "transfer_entity" for each selected ticket.
     *
     * @param MassiveAction  $ma   Massive action object
     * @param CommonDBTM     $item Ticket object
     * @param array          $ids  List of ticket IDs
     *
     * @return void
     */
    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids): void
    {
        global $DB;

        if ($ma->getAction() !== 'transfer_entity') {
            parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
            return;
        }

        $current_user     = Session::getLoginUserID();
        $input            = $ma->getInput();
        $target_entity_id = $input['target_entity_id'] ?? 0;
        $group_id         = $input['group_choice'] ?? null;

        foreach ($ids as $id) {
            // Check if current user is assigned or has bypass right
            $assigned = countElementsInTable(
                'glpi_tickets_users',
                ['tickets_id' => $id, 'users_id' => $current_user, 'type' => CommonITILActor::ASSIGN]
            );

            $can_bypass = Session::haveright("plugin_transferticketentity_bypass", READ);

            if (!$assigned && !$can_bypass) {
                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                $ma->addMessage(sprintf(
                    __("You must be assigned to ticket ID %d to transfer it.", 'transferticketentity'),
                    $id
                ));
                continue;
            }

            if ($item->getFromDB($id)) {
                // Remove assigned users
                $DB->delete('glpi_tickets_users', [
                    'tickets_id' => $id,
                    'type'       => CommonITILActor::ASSIGN
                ]);

                // Update entity
                $item->update([
                    'id'          => $id,
                    'entities_id' => $target_entity_id,
                ]);

                // Remove assigned groups
                $DB->delete('glpi_groups_tickets', [
                    'tickets_id' => $id,
                    'type'       => CommonITILActor::ASSIGN
                ]);

                // Assign new group if provided
                if (!empty($group_id)) {
                    $DB->insert('glpi_groups_tickets', [
                        'tickets_id' => $id,
                        'groups_id'  => $group_id,
                        'type'       => CommonITILActor::ASSIGN
                    ]);
                }

                // Log task
                $entity = new \Entity();
                $entity->getFromDB($target_entity_id);
                $entity_name = $entity->getName();

                $group_name = '';
                if (!empty($group_id)) {
                    $group = new Group();
                    $group->getFromDB($group_id);
                    $group_name = $group->getName();
                }

                $content = __("Escalate to", "transferticketentity") . " " . $entity_name;
                if (!empty($group_name)) {
                    $content .= " " . __("in group", "transferticketentity") . " " . $group_name;
                }

                $task = new TicketTask();
                $task->add([
                    'tickets_id' => $id,
                    'is_private' => true,
                    'state'      => Planning::INFO,
                    'content'    => $content
                ]);

                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
            } else {
                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                $ma->addMessage(sprintf(__('Failed to transfer ticket ID %d', 'transferticketentity'), $id));
            }
        }
    }
}
