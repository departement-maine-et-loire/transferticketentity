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

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_transferticketentity_install()
{
    global $DB;

    PluginTransferticketentityProfile::createFirstAccess($_SESSION["glpiactiveprofile"]["id"]);

    $default_charset = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();
    $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

    if (!$DB->TableExists("glpi_plugin_transferticketentity_entities_settings")) {
        $query = "CREATE TABLE `glpi_plugin_transferticketentity_entities_settings` (
            `id` int {$default_key_sign} NOT NULL auto_increment,
            `entities_id` int {$default_key_sign} NOT NULL,
            `allow_entity_only_transfer` BOOLEAN NOT NULL DEFAULT 0,
            `justification_transfer` BOOLEAN NOT NULL DEFAULT 0,
            `allow_transfer` BOOLEAN NOT NULL DEFAULT 0,
            `keep_category` BOOLEAN NOT NULL DEFAULT 0,
            PRIMARY KEY  (`id`),
            FOREIGN KEY  (`entities_id`) REFERENCES `glpi_entities` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

        $DB->query($query) or die("error creating glpi_plugin_transferticketentity_entities_settings " . $DB->error());
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

    $result = $DB->request([
        'SELECT' => ['profiles_id'],
        'FROM' => 'glpi_profilerights',
        'WHERE' => ['name' => 'plugin_transferticketentity_use', 'rights' => ALLSTANDARDRIGHT]
    ]);

    foreach ($result as $id_profil) {
        $DB->delete(
            'glpi_profilerights', [
                'name' => 'plugin_transferticketentity_use',
                'profiles_id' => $id_profil
            ]
        );
    }

    $tables = ["glpi_plugin_transferticketentity_entities_settings"];

    foreach ($tables as $table) {
        if ($DB->tableExists($table)) {
            $DB->queryOrDie("DROP TABLE IF EXISTS `".$table."`", $DB->error());
        }
    }

    return true;
}