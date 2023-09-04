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

class PluginTransferticketentityConfig extends CommonDBTM
{

    /**
     * Récupère les profils qui ne sont pas autorisés à utiliser le transfert d'entité
     *
     * @return $allProfiles
     */
    public static function cantUseProfile()
    {
        global $DB;

        $query = "SELECT DISTINCT P.id, P.name
        FROM glpi_profiles P
        LEFT JOIN glpi_plugin_transferticketentity_profiles PEP ON P.id = PEP.id_profiles
        WHERE PEP.id_profiles IS NULL
        ORDER BY name ASC";
    
        $result = $DB->query($query);
    
        $allProfiles = array();
    
        foreach ($result as $data) {
            array_push($allProfiles, $data['id'], $data['name']);
        }
    
        return $allProfiles;

        // Test ok
        // $result = $DB->request([
        //     'SELECT' => ['glpi_profiles.id', 'glpi_profiles.name'],
        //     'DISTINCT' => TRUE,
        //     'FROM' => 'glpi_profiles',
        //     'LEFT JOIN' => ['glpi_plugin_transferticketentity_profiles' => ['FKEY' => ['glpi_profiles'     => 'id',
        //                                                                                'glpi_plugin_transferticketentity_profiles' => 'id_profiles']]],
        //     'WHERE' => ['glpi_plugin_transferticketentity_profiles.id_profiles' => 'NULL'],
        //     'ORDER' => 'name'
        // ]);

        // $array = array();

        // foreach($result as $data){
        //     array_push($array, $data['id'], $data['name']);
        // }

        // return $array;
    }

    /**
     * Récupère les profils autorisés à utiliser le transfert d'entité
     *
     * @return $allProfiles
     */
    public static function canUseProfile()
    {
        global $DB;

        $query = "SELECT P.id, P.name
        FROM glpi_plugin_transferticketentity_profiles PEP
        LEFT JOIN glpi_profiles P ON P.id = PEP.id_profiles
        ORDER BY name ASC";
    
        $result = $DB->query($query);
    
        $allProfiles = array();
    
        foreach ($result as $data) {
            array_push($allProfiles, $data['id'], $data['name']);
        }
    
        return $allProfiles;

        // Test ok
        // $result = $DB->request([
        //     'SELECT' => ['glpi_profiles.id', 'glpi_profiles.name'],
        //     'DISTINCT' => TRUE,
        //     'FROM' => 'glpi_profiles',
        //     'LEFT JOIN' => ['glpi_plugin_transferticketentity_profiles' => ['FKEY' => ['glpi_profiles'     => 'id',
        //                                                                                'glpi_plugin_transferticketentity_profiles' => 'id_profiles']]],
        //     'WHERE' => ['NOT' => ['glpi_plugin_transferticketentity_profiles.id_profiles' => 'NULL']],
        //     'ORDER' => 'name'
        // ]);

        // $array = array();

        // foreach($result as $data){
        //     array_push($array, $data['id'], $data['name']);
        // }

        // return $array;
    }

    /**
     * Ajoute le droit à un profil d'utiliser le transfert d'entité
     *
     * @return void
     */
    public function addProfiles()
    {
        global $DB;

        if (isset($_POST['add_profiles'])) {
            $add_profiles = $_POST['add_profiles'];

            $DB->insert(
                'glpi_plugin_transferticketentity_profiles', [
                    'id_profiles'      => $add_profiles
                ]
            );
        }
    }

    /**
     * Supprime le droit à un profil d'utiliser le transfert d'entité
     *
     * @return void
     */
    public function deleteProfiles()
    {
        global $DB;

        if (isset($_POST['delete_profiles'])) {
            $delete_profiles = $_POST['delete_profiles'];
        
            $DB->delete(
                'glpi_plugin_transferticketentity_profiles', [
                   'id_profiles' => $delete_profiles
                ]
             );
        }
    }

    /**
     * Affiche le form pour configurer le plugin
     *
     * @return void
     */
    public function showFormETT()
    {
        global $CFG_GLPI;

        $cantUseProfile = self::cantUseProfile();
        $canUseProfile = self::canUseProfile();

        echo "<table>";
        echo "<tbody>";
        echo ("
            <tr style='padding-bottom: .5rem;'><form action='./config.form.php' method='post'>
                <td><label for='add_profiles'>".__("Sélectionnez le profil habilité à effectuer un transfert d'entité", "transferticketentity")."</label></td>
                <td> : </td>
                <td><select style='min-width: 150px' name='add_profiles' id='add_profiles'>");
        for ($i = 0; $i < count($cantUseProfile); $i = $i+2) {
            echo "<option value='" . $cantUseProfile[$i] . "'>" . $cantUseProfile[$i+1] . "</option>";
        }
                echo("</select></td>
                <td><button type='submit' name='addProfiles' style='background-color: #80cead;color: #1e293b;border: 1px solid rgba(98, 105, 118, 0.24);border-radius: 4px;font-weight: 500;line-height: 1.4285714286;padding: 0.4375rem 1rem; min-width:105px;'>".__("Ajouter", "transferticketentity")."</button></td>
            ");
        Html::closeForm();
        echo "</tr>";
        echo ("
            <tr style='padding-top: .5rem;'><form action='./config.form.php' method='post''>
                <td><label for='delete_profiles'>".__("Retirez les droits de transfert d'entité au profil sélectionné", "transferticketentity")."</label></td>
                <td> : </td>
                <td><select style='min-width: 150px' name='delete_profiles' id='delete_profiles'>");
        for ($i = 0; $i < count($canUseProfile); $i = $i+2) {
            echo "<option value='" . $canUseProfile[$i] . "'>" . $canUseProfile[$i+1] . "</option>";
        }
                echo("</select></td>
                <td><button type='submit' name='deleteProfiles' style='background-color: #f00020;color: white;border: 1px solid rgba(98, 105, 118, 0.24);border-radius: 4px;font-weight: 500;line-height: 1.4285714286;padding: 0.4375rem 1rem; min-width:105px;'>".__("Supprimer", "transferticketentity")."</button></td>
            ");
        Html::closeForm();
        echo "</tr>";
        echo "</tbody>";
        echo "</table>";
    }
}