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

use GlpiPlugin\Transferticketentity\Entity;
use GlpiPlugin\Transferticketentity\Ticket;

Session::checkRight("entity", UPDATE);

$config = new Entity();

if (isset($_POST["update"])) {
    $config_data = $config::getInstance($_POST['entities_id']);
    if (empty($config_data)) {
        unset($_POST['id']);
        $config->add($_POST);
    } else {
        if ($_POST['allow_transfer'] == 0) {
            $config->delete(['id' => $_POST['id']]);
        } else {
            $params['entity_choice'] = $_POST['entities_id'];
            $checkMandatoryCategory = Ticket::checkMandatoryCategory($params);

            if ($checkMandatoryCategory
                && $_POST['keep_category'] == 0
                && $_POST['itilcategories_id'] == 0) {
                Session::addMessageAfterRedirect(
                    __(
                        "The category is mandatory in the ticket template assigned to the entity",
                        'transferticketentity'
                    ),
                    true,
                    ERROR
                );
            } else {
                $config->update($_POST);
            }
        }
    }
    Html::back();
}
