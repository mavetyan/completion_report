<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * User repository class.
 *
 * @package    local_completion_report
 * @copyright  2026 Mher Avetyan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_completion_report;

defined('MOODLE_INTERNAL') || die();

/**
 * Repository for user data operations.
 */
class user_repository {

    /**
     * Get all active users suitable for selection.
     *
     * @return array Array of user objects
     */
    public function get_active_users(): array {
        global $DB;

        return $DB->get_records_sql("
            SELECT id, firstname, lastname, email, username
            FROM {user}
            WHERE deleted = 0 AND confirmed = 1 AND suspended = 0 AND id > 1
            ORDER BY lastname, firstname
        ");
    }

    /**
     * Get user by ID with proper validation.
     *
     * @param int $userid User ID
     * @return object|false User object or false if not found/not valid
     */
    public function get_valid_user(int $userid) {
        global $DB;

        return $DB->get_record('user', [
            'id' => $userid,
            'deleted' => 0,
            'confirmed' => 1,
            'suspended' => 0
        ]);
    }

    /**
     * Get completion data for user's enrolled courses efficiently.
     *
     * @param int $userid User ID
     * @param array $courseids Array of course IDs
     * @return array Completion records keyed by course ID
     */
    public function get_completion_records(int $userid, array $courseids): array {
        global $DB;

        if (empty($courseids)) {
            return [];
        }

        list($coursesql, $courseparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params = array_merge(['userid' => $userid], $courseparams);

        $records = $DB->get_records_sql("
            SELECT course, timecompleted
            FROM {course_completions}
            WHERE userid = :userid AND course $coursesql
        ", $params);

        // Return keyed by course ID for easy lookup.
        $completions = [];
        foreach ($records as $record) {
            $completions[$record->course] = $record;
        }

        return $completions;
    }
}
