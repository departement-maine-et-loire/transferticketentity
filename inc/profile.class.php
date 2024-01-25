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
                  'label'     => __('Using entity transfer', 'transferticketentity'),
                  'field'     => 'plugin_transferticketentity_use',
                  'rights'    => [ALLSTANDARDRIGHT => __('Active', 'transferticketentity')]]];
          return $rights;
    }

    function cleanProfiles($ID) {

        global $DB;
        $query = "DELETE FROM `glpi_profiles`
                  WHERE `profiles_id`='$ID'
                  AND `name` LIKE '%plugin_transferticketentity%'";
        $DB->query($query);
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
        echo "<div class='firstbloc'>";
        if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
           $profile = new Profile();
           echo "<form method='post' action='".$profile->getFormURL()."'>";
        }
  
        $profile = new Profile();
        $profile->getFromDB($ID);
  
        $rights = self::getAllRights();
        $profile->displayRightsChoiceMatrix(
            $rights,
            [
               'canedit'       => $canedit,
               'default_class' => 'tab_bg_2',
               'title'         => __('General')
            ]
        );
        
        if ($canedit) {
           echo "<div class='center'>";
           echo Html::hidden('id', ['value' => $ID]);
           echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
           echo "</div>\n";
           Html::closeForm();
        }
        echo "</div>";
    }
}