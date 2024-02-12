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

class PluginTransferticketentityEntity extends Entity
{
    /**
     * If the profile is authorised, add an extra tab
     *
     * @param object $item         Entity
     * @param int    $withtemplate 0
     * 
     * @return "Entity ticket transfer"
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Entity') {
            return __("Transfer Ticket Entity", "transferticketentity");
        }
        return '';
    }

    /**
     * If we are on tickets, an additional tab is displayed
     * 
     * @param object $item         Ticket
     * @param int    $tabnum       1
     * @param int    $withtemplate 0
     * 
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Entity') {
            $profile = new self();
            $ID   = $item->getField('id');
            $profile->showFormMcv($ID);
        }

        return true;
    }

    public function checkRights($ID) {
        global $DB;

        $result = $DB->request([
            'FROM' => 'glpi_plugin_transferticketentity_entities_settings',
            'WHERE' => ['entities_id' => $ID]
        ]);

        $array = array();

        foreach($result as $data){
            $array['allow_entity_only_transfer'] = $data['allow_entity_only_transfer'];
            $array['justification_transfer'] = $data['justification_transfer'];
            $array['allow_transfer'] = $data['allow_transfer'];
            $array['keep_category'] = $data['keep_category'];
        }

        return $array;
    }

    public static function addScript() 
    {
        echo Html::script("/plugins/transferticketentity/js/entitySettings.js");
    }

    /**
     * Display the ticket transfer form
     *
     * @return void
     */
    public function showFormMcv($ID)
    {
        $checkRights = self::checkRights($ID);
        $theServer = explode("front/entity.form.php?",$_SERVER["HTTP_REFERER"]);
        $theServer = $theServer[0];

        if(empty($checkRights)) {
            $checkRights['allow_entity_only_transfer'] = 0;
            $checkRights['justification_transfer'] = 0;
            $checkRights['allow_transfer'] = 0;
            $checkRights['keep_category'] = 0;
        }

        echo "<div class='firstbloc'>";
        if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
           echo "<form class='transferticketentity' method='post' action='".self::getFormURL()."'>";
        }

        echo "<table class='tab_cadre_fixe'>";
            echo "<tbody>";
                echo "<tr>";
                    echo "<th>";
                        echo __("Settings Transfer Ticket Entity", "transferticketentity");
                    echo "</th>";
                echo "</tr>";
                echo "<tr class='tab_bg_1'>";
                    echo "<td>";
                    echo __('Allow Transfer function', 'transferticketentity');
                    echo "&nbsp;";
                    echo "&nbsp;";
                    echo Dropdown::showYesNo('allow_transfer', $checkRights['allow_transfer'], -1, ['display' => false, 'class' => 'allow_transfer']);
                    echo "</td>";
                echo "</tr>";
                echo "<tr class='tab_bg_1' id='allow_entity_only_transfer'>";
                    echo "<td>";
                    echo __('Assigned group required', 'transferticketentity');
                    echo "&nbsp;";
                    echo "&nbsp;";
                    echo Dropdown::showYesNo('allow_entity_only_transfer', $checkRights['allow_entity_only_transfer'], -1, ['display' => false]);
                    echo "</td>";
                echo "</tr>";
                echo "<tr class='tab_bg_1' id='justification_transfer'>";
                    echo "<td>";
                    echo __('Justification required', 'transferticketentity');
                    echo "&nbsp;";
                    echo "&nbsp;";
                    echo Dropdown::showYesNo('justification_transfer', $checkRights['justification_transfer'], -1, ['display' => false]);
                    echo "</td>";
                echo "</tr>";
                echo "<tr class='tab_bg_1' id='keep_category'>";
                    echo "<td>";
                    echo __('Keep category after transfer', 'transferticketentity');
                    echo "&nbsp;";
                    echo "&nbsp;";
                    echo Dropdown::showYesNo('keep_category', $checkRights['keep_category'], -1, ['display' => false]);
                    echo "</td>";
                echo "</tr>";
            echo "</tbody>";
        echo "</table>";
        echo Html::hidden("theServer", ["value" => "$theServer"]);
        echo Html::hidden("ID", ["value" => "$ID"]);
        if ($canedit) {
           echo "<div class='center'>";
           echo Html::hidden('id', ['value' => $ID]);
           echo Html::submit(_sx('button', 'Save'), ['name' => 'transfertticket', 'class' => 'btn btn-primary']);
           echo "</div>\n";
           Html::closeForm();
        }
        echo "</div>";

        self::addScript();
    }
}