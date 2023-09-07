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

use Glpi\Plugin\Hooks;

define('TRANSFERTICKETENTITY_VERSION', '1.0.0');

function plugin_init_transferticketentity()
{
    global $PLUGIN_HOOKS;

    // Add a tab for profiles and tickets
    Plugin::registerClass('PluginTransferticketentityProfile', ['addtabon' => 'Profile']);
    Plugin::registerClass('PluginTransferticketentityTicket', ['addtabon' => 'Ticket']);

    $PLUGIN_HOOKS['change_profile']['transferticketentity'] = ['PluginTransferticketentityProfileRights', 'changeProfile'];
    $PLUGIN_HOOKS['csrf_compliant']['transferticketentity'] = true;
}

function plugin_version_transferticketentity()
{
    return [
      'name'           => 'TransferTicketEntity',
      'version'        => TRANSFERTICKETENTITY_VERSION,
      'author'         => 'Yannick COMBA',
      'license'        => 'GPLv3+',
      'homepage'       => 'https://github.com/departement-maine-et-loire/',
      'requirements'   => [
         'glpi'   => [
            'min' => '10.0'
         ]
      ]
    ];
}

function plugin_transferticketentity_check_prerequisites()
{
    $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
    if (version_compare($version, '10.0', '<')) {
       echo "This plugin requires GLPI >= 10.0";
       return false;
    }
    return true;
}

function plugin_transferticketentity_check_config($verbose = false)
{
    if (true) {
        return true;
    }

    if ($verbose) {
        echo "Installed, but not configured";
    }

    return false;
}

function plugin_transferticketentity_options()
{
    return [
      Plugin::OPTION_AUTOINSTALL_DISABLED => true,
    ];
}