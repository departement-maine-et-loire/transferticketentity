<?php
/**
 -------------------------------------------------------------------------
 LICENSE

 This file is part of entitytickettransfer plugin for GLPI.

 entitytickettransfer is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 entitytickettransfer is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @category  Ticket
 @package   Entitytickettransfer
 @author    Yannick Comba <y.comba@maine-et-loire.fr>
 @copyright 2015-2023 Département de Maine et Loire plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            https://www.gnu.org/licenses/gpl-3.0.html
 @link      https://github.com/departement-maine-et-loire/
 --------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

define('ENTITYTICKETTRANSFER_VERSION', '1.0.0');
require_once GLPI_ROOT . "/plugins/entitytickettransfer/inc/profilerights.class.php";

function plugin_init_entitytickettransfer()
{
    global $PLUGIN_HOOKS;

    // Ajout d'un onglet sur les profils et les tickets
    Plugin::registerClass('PluginEntitytickettransferProfile', ['addtabon' => 'Profile']);
    Plugin::registerClass('PluginEntitytickettransferTicket', ['addtabon' => 'Ticket']);

    $PLUGIN_HOOKS['change_profile']['entitytickettransfer'] = ['PluginEntitytickettransferProfileRights', 'changeProfile'];
    $PLUGIN_HOOKS['csrf_compliant']['entitytickettransfer'] = true;

    // Ajout de la page de configuration
    $PLUGIN_HOOKS['config_page']['entitytickettransfer'] = 'front/config.form.php';
}

function plugin_version_entitytickettransfer()
{
    return [
      'name'           => 'Entitytickettransfer',
      'version'        => ENTITYTICKETTRANSFER_VERSION,
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

function plugin_entitytickettransfer_check_prerequisites()
{
    return true;
}

function plugin_entitytickettransfer_check_config($verbose = false)
{
    if (true) {
        return true;
    }

    if ($verbose) {
        echo "Installed, but not configured";
    }

    return false;
}

function plugin_entitytickettransfer_options()
{
    return [
      Plugin::OPTION_AUTOINSTALL_DISABLED => true,
    ];
}
