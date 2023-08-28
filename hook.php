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

    include_once GLPI_ROOT . "/plugins/transferticketentity/inc/config.class.php";

    $default_charset = DBConnection::getDefaultCharset();
    $default_collation = DBConnection::getDefaultCollation();
    $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

    // Table permettant de gérer les droits du plugin
    if (!$DB->TableExists("glpi_plugin_transferticketentity_profile_rights")) {
        $query = "CREATE TABLE `glpi_plugin_transferticketentity_profile_rights` (
        `id` INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
        `profile` INT(11) NOT NULL,
        `right` CHAR(2) NOT NULL,
         PRIMARY KEY  (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

        $DB->query($query) or die("error creating glpi_plugin_transferticketentity_profile_rights " . $DB->error());
        PluginTransferticketentityProfileRights::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
    }

    // Table permettant de gérer les droits des profils autorisés à utiliser le plugin
    if (!$DB->tableExists("glpi_plugin_transferticketentity_profiles")) {
        $query = "CREATE TABLE `glpi_plugin_transferticketentity_profiles` (
                `id` int {$default_key_sign} NOT NULL auto_increment,
                `id_profiles` INT NOT NULL UNIQUE,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";

        $DB->query($query) or die("error creating glpi_plugin_transferticketentity_profiles ". $DB->error());
        PluginTransferticketentityProfileRights::createAdminAccess($_SESSION['glpiactiveprofile']['id']);

        $query = "INSERT INTO `glpi_plugin_transferticketentity_profiles`
                    (`id_profiles`)
                VALUES (4)";
        $DB->query($query) or die("error populate glpi_plugin_transferticketentity_profiles ". $DB->error());
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

    $tables = array('glpi_plugin_transferticketentity_profiles', 'glpi_plugin_transferticketentity_profile_rights');

    foreach ($tables as $table) {
        $DB->query("DROP TABLE IF EXISTS `$table`;");
    }
 
    return true;
}