<?php

/**
 * -------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of Transferticketentity plugin for GLPI.
 *
 * Transferticketentity is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Transferticketentity is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Reports. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Ticket
 * @package   Transferticketentity
 * @author    Département de Maine et Loire, Y.COMBA, I.MELLOUK
 * @copyright 2015-2023 Département de Maine et Loire plugin team
 * @license   AGPL License 3.0 or (at your option) any later version
 * https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/departement-maine-et-loire/
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Transferticketentity;

use CommonGLPI;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use ProfileRight;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Profile
 *
 * This class manages the profile rights of the plugin
 */
class Profile extends \Profile
{
    public static $rightname = "profile";

    /**
     * @return string
     */
    public static function getIcon()
    {
        return "ti ti-transfer";
    }

    /**
     * Get tab name for item
     *
     * @param CommonGLPI $item
     * @param        $withtemplate
     *
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile'
            && $item->fields['interface'] == 'central'
        ) {
            return self::createTabEntry(__("Transfer Ticket Entity", "transferticketentity"));
        }
        return '';
    }

    /**
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if (!$item instanceof \Profile || !self::canView()) {
            return false;
        }

        $profile = new \Profile();
        $profile->getFromDB($item->getID());

        $rights = self::getAllRights();

        $twig = TemplateRenderer::getInstance();
        $twig->display('@transferticketentity/profile.html.twig', [
            'id'      => $item->getID(),
            'profile' => $profile,
            'title'   => self::getTypeName(Session::getPluralNumber()),
            'rights'  => $rights,
        ]);

        return true;
    }


    /**
     * Get all rights
     *
     * @return array
     */
    public static function getAllRights()
    {
        $rights = [];

        $rights[] = [
            'itemtype' => Entity::class,
            'label'    => __('Authorized entity transfer', 'transferticketentity'),
            'field'    => 'plugin_transferticketentity_use',
            'rights'   => [
                READ => __('Read'),
            ],
        ];

        $rights[] = [
            'itemtype' => Entity::class,
            'label'    => __('Transfer authorized without assignment of technician or associated group', 'transferticketentity'),
            'field'    => 'plugin_transferticketentity_bypass',
            'rights'   => [
                READ => __('Read'),
            ],
        ];

        $rights[] = [
            'itemtype' => Entity::class,
            'label'    => __('Mass transfer authorized', 'transferticketentity'),
            'field'    => 'plugin_transferticketentity_massive',
            'rights'   => [
                READ => __('Read'),
            ],
        ];

        return $rights;
    }

    /**
     * Init profiles
     **/
    public static function translateARight($old_right)
    {
        switch ($old_right) {
            case '':
                return 0;
            case 'r':
                return READ;
            case 'w':
                return UPDATE + PURGE;
            case '0':
            case '1':
                return $old_right;

            default:
                return 0;
        }
    }


    /**
     * Initialize profiles, and migrate it necessary
     */
    public static function initProfile()
    {
        global $DB;
        $profile = new self();
        $dbu     = new DbUtils();
        //Add new rights in glpi_profilerights table
        foreach ($profile->getAllRights(true) as $data) {
            if ($dbu->countElementsInTable(
                "glpi_profilerights",
                ["name" => $data['field']]
            ) == 0) {
                ProfileRight::addProfileRights([$data['field']]);
            }
        }

        $it = $DB->request([
            'FROM'  => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                'name'        => ['LIKE', '%plugin_transferticketentity%'],
            ],
        ]);
        foreach ($it as $prof) {
            $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
        }
    }

    /**
     * @param $profiles_id
     */
    public static function createFirstAccess($profiles_id)
    {
        $rights = [
            'plugin_transferticketentity_use'     => READ,
            'plugin_transferticketentity_bypass'  => READ,
            'plugin_transferticketentity_massive' => READ,
        ];

        self::addDefaultProfileInfos(
            $profiles_id,
            $rights,
            true
        );
    }

    /**
     * @param $profiles_id
     * @param $rights
     * @param bool $drop_existing
     **/
    public static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false)
    {
        $dbu          = new DbUtils();
        $profileRight = new ProfileRight();
        foreach ($rights as $right => $value) {
            if ($dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            ) && $drop_existing) {
                $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
            }
            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
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
}
