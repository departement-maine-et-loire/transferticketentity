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

// Namespace fait planter l'install
// use GlpiPlugin\Transferticketentity\PluginTransferticketentityProfile;
// use GlpiPlugin\Transferticketentity\PluginTransferticketentityChangeProfile;
// use GlpiPlugin\Transferticketentity\PluginTransferticketentityTicket;
// use GlpiPlugin\Transferticketentity\PluginTransferticketentityTransfer;

/**
 * Install hook
 *
 * @return boolean
 */
function plugin_transferticketentity_install()
{
    global $DB;

    PluginTransferticketentityProfile::createFirstAccess($_SESSION["glpiactiveprofile"]["id"]);

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

    return true;
}