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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginEntitytickettransferProfileRights extends CommonDBTM
{

    static function hasChangeProfile()
    {
        if ((isset($_SESSION['glpi_plugin_entitytickettransfer_profile']['id']))) {
            if ($_SESSION['glpiactiveprofile']['id'] != $_SESSION['glpi_plugin_entitytickettransfer_profile']['id']) {
                $_SESSION['glpi_plugin_entitytickettransfer_profile']['id'] = $_SESSION['glpiactiveprofile']['id'];
                return true;
            } else {
                return false;
            }
        } else {
            $_SESSION['glpi_plugin_entitytickettransfer_profile']['id'] = $_SESSION['glpiactiveprofile']['id'];
        }
    }

    static function setSessionProfileId()
    {
        if (!isset($_SESSION['glpi_plugin_entitytickettransfer_profile'])) {
            $_SESSION['glpi_plugin_entitytickettransfer_profile']['id'] = $_SESSION['glpiactiveprofile']['id'];
        }
    }

    static function getSessionRights()
    {
        global $DB;

        $result = $DB->request(
            [
            'SELECT' =>
            "right",
            'FROM' => 'glpi_plugin_entitytickettransfer_profile_rights',
            'WHERE' => [
            "profile"    => $_SESSION['glpiactiveprofile']['id']
            ],
            ]
        );
        foreach ($result as $data) {
            $right = $data['right'];
            return $right;
        }
    }

    static function getProfileRight($ID)
    {
        global $DB;

        $result = $DB->request(
            [
            'SELECT' =>
            "right",
            'FROM' => 'glpi_plugin_entitytickettransfer_profile_rights',
            'WHERE' => [
            "profile"    => $ID
            ],
            ]
        );
        foreach ($result as $data) {
            $right = $data['right'];
            return $right;
        }
    }

    static function setSessionProfileRights()
    {
        $_SESSION['glpi_plugin_entitytickettransfer_profile']['right'] = self::getSessionRights();
    }

    static function changeProfile()
    {
        self::hasChangeProfile();
        self::setSessionProfileRights();
    }

    static function canUpdate()
    {

        if (isset($_SESSION["glpi_plugin_entitytickettransfer_profile"])) {
            return ($_SESSION['glpi_plugin_entitytickettransfer_profile']['right'] == 'w'
            || $_SESSION["glpi_plugin_entitytickettransfer_profile"]['right'] == 'su');
        }
        return false;
    }

    static function canView()
    {

        if (isset($_SESSION["glpi_plugin_entitytickettransfer_profile"])) {
            return ($_SESSION["glpi_plugin_entitytickettransfer_profile"]['right'] == 'w'
            || $_SESSION["glpi_plugin_entitytickettransfer_profile"]['right'] == 'r'
            || $_SESSION["glpi_plugin_entitytickettransfer_profile"]['right'] == 'su');
        }
        return false;
    }

    static function canProfileUpdate($ID)
    {
        $right = self::getProfileRight($ID);
        if (isset($right)) {
            return ($right == 'w'
            || $right == 'su');
        }
        return false;
    }

    static function canProfileView($ID)
    {
        $right = self::getProfileRight($ID);
        if (isset($right)) {
            return ($right == 'w'
            || $right == 'r'
            || $right == 'su');
        }
        return false;
    }

    static function isSuperAdmin()
    {
        if (isset($_SESSION["glpi_plugin_entitytickettransfer_profile"])) {
            return $_SESSION["glpi_plugin_entitytickettransfer_profile"]['right'] == 'su';
        }
        return false;
    }

    static function createAdminAccess($ID)
    {
        global $DB;

        $myProfile = new self();
        // if the profile does not already exist in the profile table of the plugin
        if (!$myProfile->getFromDB($ID)) {
            // Add a field in the table including the profile ID of the connected user and the right to write
            $DB->insert(
                'glpi_plugin_entitytickettransfer_profile_rights',
                [
                'profile' => $ID,
                'right' => 'su'
                ]
            );
        }
    }

    static function updateProfile($data)
    {
        global $DB;
        foreach ($data as $value) {
            $read = $value['readValue'];
            $update = $value['updateValue'];
            $id = $value['id'];
            $right = 'no';
            if ($read == 'true') {
                $right = 'r';
            }
            if ($update == 'true') {
                $right = 'w';
            }

            if ($id == 4) {
                return false;
            }

            $DB->updateOrInsert(
                'glpi_plugin_entitytickettransfer_profile_rights',
                [
                'profile'      => $id,
                'right'  => $right
                ],
                [
                'profile' => $id
                ]
            );

            return true;
        }
    }

    static function isAskerIdIdentic($id)
    {
        if ($_SESSION['glpiID'] == $id) {
            return true;
        }
        return false;
    }

    static function addErrorMessage($message)
    {
        Session::addMessageAfterRedirect(
            $message,
            false,
            ERROR
        );
        echo json_encode(['success' => false]);
        Html::displayAjaxMessageAfterRedirect();
    }

    static function addSuccessMessage($message)
    {
        Session::addMessageAfterRedirect(
            $message,
            false,
            INFO
        );
        echo json_encode(['success' => true]);
        Html::displayAjaxMessageAfterRedirect();
    }
}