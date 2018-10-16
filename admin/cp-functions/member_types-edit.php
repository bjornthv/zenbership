<?php


/**
 *
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */

// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$type = 'add';

$task = 'member_types-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$order = 0;

$user = new user;

$q1 = $db->delete("
    DELETE FROM `ppSD_member_types_content`
    WHERE `member_type`='" . $db->mysql_clean($_POST['id']) . "'
");

foreach ($_POST['content'] as $aContent) {

    if (! empty($aContent['id']) && empty($aContent['del'])) {
        // Create the reference
        $q3 = $db->insert("
            INSERT INTO `ppSD_member_types_content` (
                `member_type`,
                `act_id`,
                `act_type`
            )
            VALUES (
                '" . $db->mysql_clean($_POST['id']) . "',
                '" . $db->mysql_clean($aContent['id']) . "',
                'content'
            )
        ");
    }

    if ($_POST['alter_existing'] == '1') {
        // Grant users new access
        if (empty($aContent['del'])) {
            // Grant existing members access
            $q0 = $db->run_query("
                SELECT `id`
                FROM `ppSD_members`
                WHERE `member_type`='" . $db->mysql_clean($_POST['id']) . "'
            ");
            while ($row = $q0->fetch()) {
                $grant = $user->add_content_access($aContent['id'], $row['id']);
            }
        }

        // Remove existing access
        // if it has been deleted.
        else {
            // Grant existing members access
            $q0 = $db->run_query("
                SELECT `id`
                FROM `ppSD_members`
                WHERE `member_type`='" . $db->mysql_clean($_POST['id']) . "'
            ");
            while ($row = $q0->fetch()) {
                $remove = $user->remove_content_access($aContent['id'], $row['id']);
            }
        }
    }

}

$task                  = $db->end_task($task_id, '1');

$return                = array();
$return['redirect_popup'] = array(
    'page' => 'member_types',
    'fields' => '',
);
$return['show_saved']  = 'Updated Content Package';

echo "1+++" . json_encode($return);
exit;



