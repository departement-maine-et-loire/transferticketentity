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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginTransferticketentityProfile extends Profile
{
    /**
     * Add an additional tab
     *
     * @param object $item         Ticket
     * @param int    $withtemplate 0
     * 
     * @return nametab
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return __("Transfer Ticket Entity", "transferticketentity");
        }
        return '';
    }

    /**
     * Get profiles authorised to use entity transfer
     *
     * @return array $allProfiles
     */
    public function canUseProfiles()
    {
        global $DB;

        $result = $DB->request([
            'SELECT' => ['glpi_profiles.id', 'glpi_profiles.name'],
            'DISTINCT' => TRUE,
            'FROM' => 'glpi_profiles',
            'LEFT JOIN' => ['glpi_plugin_transferticketentity_profiles' => ['FKEY' => ['glpi_profiles'     => 'id',
                                                                                       'glpi_plugin_transferticketentity_profiles' => 'id_profiles']]],
            'WHERE' => ['NOT' => ['glpi_plugin_transferticketentity_profiles.id_profiles' => 'NULL']],
            'ORDER' => 'name ASC'
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['id'], $data['name']);
        }

        return $array;
    }

    /**
     * If we are on profiles, an additional tab is displayed
     * 
     * @param object $item         Ticket
     * @param int    $tabnum       1
     * @param int    $withtemplate 0
     * 
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Profile') {
            $ID   = $item->getID();
            $profile = new self();
            if (!isset($_SESSION['glpi_plugin_transferticketentity_profile']['id'])) {
                PluginTransferticketentityProfileRights::changeProfile();
            }
            $profile->showFormMcv($ID);
        }

        return true;
    }

    /**
     * Display the plugin configuration form
     *
     * @param int $ID id
     * 
     * @return void
     */
    public function showFormMcv($ID)
    {
        global $CFG_GLPI;
        global $DB;

        $profiles_id = $_SESSION['glpiactiveprofile']['id'];

        $canUseProfiles = self::canUseProfiles();
        $id_profil = $_GET['id'];

        // Check or uncheck the box if the profile is authorised to use entity transfer
        if (in_array($id_profil, $canUseProfiles)) {
            $checked = 'checked';
        } else {
            $checked = '';
        }

        if(Session::haveRight("profile", UPDATE)) {
            $disabled = '';
        } else {
            $disabled = 'disabled';
        }
        
        if(Session::haveRight("profile", UPDATE)) {
            echo "<form action='../plugins/transferticketentity/inc/profile.php' method='post'>";
        }
        
        echo "  <table class='table table-hover card-table'>
                    <tbody>
                        <tr class='border-top' style='background:var(--tblr-border-color-light);'>
                            <th colspan='2'><h4 style='line-height:1.4285714286; font-size:0.875rem; color:#626976;'>".__("Change rights", "transferticketentity")."</h4></th>
                        </tr>
                        <tr>
                            <td class='tab_bg_2' style='width:40%;'>".__("Using entity transfer", "transferticketentity")."</td>
                            <td><input type='checkbox' class='form-check-input' name='plugin_change_profile' value='swap_profil' $checked $disabled></td>
                        </tr>
                    </tbody>
                </table>

        <div style='display:none'>
            <input type ='number' id='id_profil' value= '$id_profil' name='id_profil' style='display: none;' readonly>
        </div>";
        
        if(Session::haveRight("profile", UPDATE)){
            echo "  <div class='center'>
                        <button type='submit' value='Sauvegarder' class='btn btn-primary mt-2' name='plugin_update_profile'>          
                            <span><i class='fas fa-save'></i><span>".__("Save", "transferticketentity")."</span></span>
                        </button>
                    </div>";
            Html::closeForm();
        }
    }
}