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

use GlpiPlugin\Transferticketentity\Ticket;

Session::checkRight("plugin_transferticketentity_use", READ);

$ticket = new Ticket();

if (isset($_POST['transfertticket'])) {
    if (isset($_POST['entity_choice'])
        && $_POST['entity_choice'] > 0) {
        $ticket->launchTicketTransfer($_POST);
    } else {
        Session::addMessageAfterRedirect(
            __("Please select a valid entity", 'transferticketentity'),
            true,
            ERROR
        );
        Html::back();
    }
}

Html::back();
