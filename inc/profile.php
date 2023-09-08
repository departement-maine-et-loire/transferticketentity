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
use GlpiPlugin\Transferticketentity\PluginTransferticketentityProfile;
use CommonDBTM;
use Session;

require '../../../inc/includes.php';

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
    // Session is not valid then exit
    exit;
}

class PluginTransferticketentityChangeProfile extends CommonDBTM
{
    public $changeProfile;
    public $getNameProfile;

    public function __construct()
    {
        $this->getNameProfile = $this->getNameProfile();
        $this->changeProfile = $this->changeProfile();
    }

    /**
     * Get the profile name
     *
     * @return $data
     */
    public function getNameProfile()
    {
        global $DB;
        $id_profil = $_POST['id_profil'];

        $result = $DB->request([
            'SELECT' => ['name'],
            'FROM' => 'glpi_profiles',
            'WHERE' => ['id' => $id_profil],
        ]);

        $array = array();

        foreach ($result as $data) {
            array_push($array, $data['name']);
        }

        return $array[0];
    }

    /**
     * Make the profile eligible or ineligible for entity transfer
     *
     * @return $data
     */
    public function changeProfile()
    {
        global $CFG_GLPI;
        global $DB;

        $theServer = explode("front/profile.form.php?",$_SERVER["HTTP_REFERER"]);
        $theServer = $theServer[0];

        if (isset($_POST['plugin_update_profile'])) {
            $name_profile = self::getNameProfile();
            $id_profil = $_POST['id_profil'];

            if ($_POST['plugin_change_profile'] == 'swap_profil') {
                $PluginTransferticketentityProfile = new \PluginTransferticketentityProfile();
                $PluginTransferticketentityProfile::addDefaultProfileInfos($id_profil, ['plugin_transferticketentity_use' => ALLSTANDARDRIGHT]);

                Session::addMessageAfterRedirect(
                    __("Item successfully updated", "transferticketentity") . " : <a href='" . $theServer . "front/profile.form.php?id=" . $id_profil . "'>$name_profile</a>",
                    true,
                    INFO
                );
    
                header('location:' . $theServer . 'front/profile.form.php?id='.$id_profil);
            } else {
                $DB->delete(
                    'glpi_profilerights', [
                        'name' => 'plugin_transferticketentity_use',
                        'profiles_id' => $id_profil
                    ]
                 );

                Session::addMessageAfterRedirect(
                    __("Item successfully updated", "transferticketentity") . " : <a href='" . $theServer . "front/profile.form.php?id=" . $id_profil . "'>$name_profile</a>",
                    true,
                    INFO
                );
    
                header('location:' . $theServer . 'front/profile.form.php?id='.$id_profil);
            }
        }
    }
}

$profile = new PluginTransferticketentityChangeProfile();