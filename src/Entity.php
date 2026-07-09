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

use CommonDBTM;
use CommonGLPI;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class Entity extends CommonDBTM
{
    public static $rightname = "entity";

    public static function getTable($classname = null)
    {
        return "glpi_plugin_transferticketentity_entities_settings";
    }

    /**
     * @return string
     */
    public static function getIcon()
    {
        return "ti ti-transfer";
    }

    public static function getInstance($entities_id)
    {
        $temp = new self();
        if ($temp->getFromDBByCrit(['entities_id' => $entities_id])) {
            return $temp->fields;
        }
        return false;
    }

    /**
     * If category belong to ancestor, return it
     *
     * @return array
     */
    public function availableCategories()
    {
        global $DB;
        $entity = $_REQUEST['id'];
        $allItilCategories = [0 => Dropdown::EMPTY_VALUE];

        $result = $DB->request([
            'FROM' => 'glpi_entities',
            'WHERE' => ['id' => $entity],
        ]);

        $ancestorsEntities = [];

        foreach ($result as $data) {
            if ($data['ancestors_cache']) {
                $ancestorsEntities = $data['ancestors_cache'];
                $ancestorsEntities = json_decode($ancestorsEntities, true);
                array_push($ancestorsEntities, $entity);
            } else {
                array_push($ancestorsEntities, 0);
            }
        }

        foreach ($ancestorsEntities as $ancestorEntity) {
            if ($ancestorEntity == $entity) {
                $result = $DB->request([
                    'FROM' => 'glpi_itilcategories',
                    'WHERE' => ['entities_id' => $ancestorEntity],
                ]);

                foreach ($result as $data) {
                    $allItilCategories[$data['id']] = $data['name'];
                }
            } else {
                $result = $DB->request([
                    'FROM' => 'glpi_itilcategories',
                    'WHERE' => ['entities_id' => $ancestorEntity, 'is_recursive' => 1],
                ]);

                foreach ($result as $data) {
                    $allItilCategories[$data['id']] = $data['name'];
                }
            }
        }

        return $allItilCategories;
    }

    /**
     *
     * @param object $item Entity
     * @param int $withtemplate 0
     *
     * @return "Entity ticket transfer"
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == \Entity::class) {
            return self::createTabEntry(__("Transfer Ticket Entity", "transferticketentity"));
        }
        return '';
    }

    /**
     *
     * @param object $item Ticket
     * @param int $tabnum 1
     * @param int $withtemplate 0
     *
     * @return true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == \Entity::class) {
            $entity = new self();
            $entity->showFormMcv($item);
        }

        return true;
    }


    /**
     * Display the ticket transfer form
     *
     * @return true
     */
    public function showFormMcv($item)
    {
        $checkRights = new self();
        $checkRights->getFromDBByCrit(['entities_id' => $item->getID()]);

        $availableCategories = self::availableCategories();

        $params['entity_choice'] = $item->getID();
        $checkMandatoryCategory = Ticket::checkMandatoryCategory($params);

        if (empty($checkRights->fields)) {
            $checkRights->fields['allow_entity_only_transfer'] = 0;
            $checkRights->fields['justification_transfer'] = 0;
            $checkRights->fields['allow_transfer'] = 0;
            $checkRights->fields['keep_category'] = 0;
            $checkRights->fields['itilcategories_id'] = 0;
        }

        $target = self::getFormURL();

        TemplateRenderer::getInstance()->display(
            '@transferticketentity/config.html.twig',
            [
                'can_edit' => Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]),
                'item' => $checkRights,
                'action' => $target,
                'id' => $checkRights->getID(),
                'entities_id' => $item->getID(),
                'availableCategories' => $availableCategories,
                'checkMandatoryCategory' => $checkMandatoryCategory,
            ],
        );

        return true;
    }

    public static function getEntitiesRights()
    {
        global $DB;

        $result = $DB->request([
            'SELECT' => [
                'entities_id',
                'allow_entity_only_transfer',
                'justification_transfer',
                'allow_transfer',
                'keep_category',
            ],
            'FROM' => 'glpi_plugin_transferticketentity_entities_settings',
            'ORDER' => ['entities_id ASC'],
        ]);

        $array = [];

        foreach ($result as $data) {
            array_push($array, $data);
        }

        return $array;
    }


    /**
     * Get selected entity rights
     *
     * @return array
     */
    public static function checkEntityRight($params)
    {
        $array = [];
        $entity_config = new self();
        $entities = $entity_config->find(['entities_id' => $params['entity_choice']]);

        foreach ($entities as $data) {
            $array['allow_entity_only_transfer'] = $data['allow_entity_only_transfer'];
            $array['justification_transfer'] = $data['justification_transfer'];
            $array['allow_transfer'] = $data['allow_transfer'];
            $array['keep_category'] = $data['keep_category'];
            $array['itilcategories_id'] = $data['itilcategories_id'];
        }

        return $array;
    }
}
