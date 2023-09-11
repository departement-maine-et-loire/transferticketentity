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
    static $rightname = "profile";

    static function getAllRights() {
        $rights = [
            ['itemtype'  => 'PluginTransferTicketEntityUse',
                  'label'     => __('Change entity', 'transferticketentity'),
                  'field'     => 'plugin_transferticketentity_use',
                  'rights'    => [READ => __('Read')]]];
          return $rights;
    }

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
    public static function canUseProfiles()
    {
        global $DB;

        $result = $DB->request([
            'SELECT' => ['profiles_id'],
            'FROM' => 'glpi_profilerights',
            'WHERE' => ['name' => 'plugin_transferticketentity_use', 'rights' => ALLSTANDARDRIGHT],
            'ORDER' => 'name ASC'
        ]);

        $array = array();

        foreach($result as $data){
            array_push($array, $data['profiles_id']);
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
            $profile = new self();
            $ID   = $item->getField('id');

            $profile->showFormMcv($ID);
        }

        return true;
    }

        /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights) {

    $profileRight = new ProfileRight();
    foreach ($rights as $right => $value) {
       if (!countElementsInTable(
           'glpi_profilerights',
           ['profiles_id' => $profiles_id, 'name' => $right]
       )) {
          $myright['profiles_id'] = $profiles_id;
          $myright['name']        = $right;
          $myright['rights']      = $value;
          $profileRight->add($myright);

          //Add right to the current session
          $_SESSION['glpiactiveprofile'][$right] = $value;
       }
    }
 }

  /**
  * @param $ID  integer
  */
 static function createFirstAccess($profiles_id) {

    include_once Plugin::getPhpDir('transferticketentity')."/inc/profile.class.php";
    foreach (self::getAllRights() as $right) {
       self::addDefaultProfileInfos(
        $profiles_id,
        ['plugin_transferticketentity_use' => ALLSTANDARDRIGHT]
       );
    }
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

        $canUseProfiles = self::canUseProfiles();
        $id_profil = $_GET['id'];

        // Check or uncheck the box if the profile is authorised to use entity transfer
        if (in_array($id_profil, $canUseProfiles)) {
            $checked = true;
        } else {
            $checked = false;
        }

        if(Session::haveRight("profile", UPDATE)) {
            $disabled = false;
        } else {
            $disabled = true;
        }

        $paramCheckbox = [
            'name' => 'plugin_change_profile',
            'checked' => $checked,
            'zero_on_empty' => false,
            'value' => 'swap_profil',
            'readonly' => $disabled
        ];
        
        if (Session::haveRight("profile", UPDATE)) {
            echo "<form action='../plugins/transferticketentity/inc/profile.php' method='post'>";
        }
        
        echo "  <table class='table table-hover card-table'>
                    <tbody>
                        <tr class='border-top tt_profile_border-top'>
                            <th colspan='2'><h4>".__("Change rights", "transferticketentity")."</h4></th>
                        </tr>
                        <tr>
                            <td class='tab_bg_2 tt_profile_tab_bg_2'>".__("Using entity transfer", "transferticketentity")."</td>
                            <td>";
                                Html::showCheckbox($paramCheckbox);
                            echo "</td>
                        </tr>
                    </tbody>
                </table>";
                
        echo Html::hidden("id_profil", ["value" => "$id_profil"]);
        
        if(Session::haveRight("profile", UPDATE)){
            echo "<div class='center'>";
                echo Html::submit(_sx('button', 'Save'), ['name' => 'plugin_update_profile', 'class' => 'btn btn-primary mt-2']);
            echo"</div>";
            Html::closeForm();
        }

        PluginTransferticketentityTicket::addStyleSheetAndScript();
    }
}