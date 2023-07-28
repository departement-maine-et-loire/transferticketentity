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

require '../../../inc/includes.php';

if (!isset($_SESSION['glpiactiveprofile']['id'])) {
    // Session is not valid then exit
    exit;
}

class PluginEntitytickettransferChangeProfile extends CommonDBTM
{
    public $changeProfile;
    public $getNameProfile;

    public function __construct()
    {
        $this->getNameProfile = $this->getNameProfile();
        $this->changeProfile = $this->changeProfile();
    }

    /**
     * Récupère le nom donné au profil
     *
     * @return $data
     */
    public function getNameProfile()
    {
        global $DB;
        $id_profil = $_POST['id_profil'];

        $query = "SELECT name
        FROM glpi_profiles
        WHERE id = $id_profil";
  
        $result = $DB->query($query);

        foreach ($result as $data) {
            return $data['name'];
        }
    }

    /**
     * Rend éligible ou non le profil au transfert d'entité
     *
     * @return $data
     */
    public function changeProfile()
    {
        global $CFG_GLPI;
        global $DB;

        if (isset($_POST['plugin_update_profile'])) {
            $name_profile = self::getNameProfile();
            $id_profil = $_POST['id_profil'];

            if ($_POST['plugin_change_profile'] == 'swap_profil') {
                $query = "INSERT INTO glpi_plugin_entitytickettransfer_profiles (`id_profiles`)
                VALUES ($id_profil)";
            
                $result = $DB->query($query);

                Session::addMessageAfterRedirect(
                    __("Élément modifié", "entitytickettransfer") . " : <a href='/front/profile.form.php?id=" . $id_profil . "'>$name_profile</a>",
                    true,
                    SUCCESS
                );
    
                header('location:/front/profile.form.php?id='.$id_profil);
            } else {
                $query = "DELETE FROM glpi_plugin_entitytickettransfer_profiles
                WHERE id_profiles = $id_profil";
            
                $result = $DB->query($query);

                Session::addMessageAfterRedirect(
                    __("Élément modifié", "entitytickettransfer") . " : <a href='/front/profile.form.php?id=" . $id_profil . "'>$name_profile</a>",
                    true,
                    SUCCESS
                );
    
                header('location:/front/profile.form.php?id='.$id_profil);
            }
        }
    }
}

$profile = new PluginEntitytickettransferChangeProfile();