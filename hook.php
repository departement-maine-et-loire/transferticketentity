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
 @author    Département de Maine et Loire, Y.COMBA, I.MELLOUK
 @copyright 2015-2023 Département de Maine et Loire plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            https://www.gnu.org/licenses/gpl-3.0.html
 @link      https://github.com/departement-maine-et-loire/
 --------------------------------------------------------------------------
*/

use GlpiPlugin\Transferticketentity\Profile;
use GlpiPlugin\Transferticketentity\Ticket;

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_transferticketentity_install()
{
    global $DB;

    Profile::createFirstAccess($_SESSION["glpiactiveprofile"]["id"]);

    $default_charset   = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();
    $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

    if (!$DB->TableExists("glpi_plugin_transferticketentity_entities_settings")) {
        $query = "CREATE TABLE `glpi_plugin_transferticketentity_entities_settings` (
            `id` int {$default_key_sign} NOT NULL auto_increment,
            `entities_id` int {$default_key_sign} NOT NULL,
            `allow_entity_only_transfer` BOOLEAN NOT NULL DEFAULT 0,
            `justification_transfer` BOOLEAN NOT NULL DEFAULT 0,
            `allow_transfer` BOOLEAN NOT NULL DEFAULT 0,
            `keep_category` BOOLEAN NOT NULL DEFAULT 0,
            `itilcategories_id` INT {$default_key_sign},
            PRIMARY KEY  (`id`),
            FOREIGN KEY  (`entities_id`) REFERENCES `glpi_entities` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

        $DB->doQuery($query);
    }

    return true;
}

/**
 * Uninstall hook
 *
 * @return boolean
 */
function plugin_transferticketentity_uninstall()
{
    global $DB;

    // Plugin tables deletion
    $tables = ["glpi_plugin_transferticketentity_entities_settings"];

    foreach ($tables as $table) {
        $DB->dropTable($table);
    }

    //Delete rights associated with the plugin
    $profileRight = new ProfileRight();
    foreach (Profile::getAllRights() as $right) {
        $profileRight->deleteByCriteria(['name' => $right['field']]);
    }
    return true;
}

/**
 * Declare specific massive actions for the plugin on core GLPI itemtypes.
 *
 * @param string $type Type of the item (e.g., 'Ticket')
 *
 * @return array Associative array of available massive actions
 */
function plugin_transferticketentity_MassiveActions($type)
{
    $actions = [];

    if ($type === 'Ticket') {
        if (Session::haveRight('plugin_transferticketentity_massive', READ)) {
            $class = Ticket::class;
            $key   = 'transfer_entity';
            $label = __('Transfer to another entity', 'transferticketentity');

            $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $key] = $label;
        }
    }

    return $actions;
}
