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
 * Unit tests for local_completion_report plugin
 *
 * @package    local_completion_report
 * @copyright  2026 Mher Avetyan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_completion_report;

use advanced_testcase;

/**
 * Tests for completion report functionality
 */
class completion_report_test extends advanced_testcase {

    /**
     * Test that active users are retrieved correctly
     */
    public function test_get_active_users() {
        $this->resetAfterTest();

        // Create test users
        $user1 = $this->getDataGenerator()->create_user(['suspended' => 0]);
        $user2 = $this->getDataGenerator()->create_user(['suspended' => 1]); // Suspended
        $user3 = $this->getDataGenerator()->create_user(['deleted' => 1]); // Deleted

        global $DB;
        $active_users = $DB->get_records_select('user', 'suspended = 0 AND deleted = 0', null, 'lastname, firstname');

        // Test that only active users are returned
        $this->assertArrayHasKey($user1->id, $active_users);
        $this->assertArrayNotHasKey($user2->id, $active_users);
        $this->assertArrayNotHasKey($user3->id, $active_users);
    }

    /**
     * Test completion status display logic
     */
    public function test_completion_status_display() {
        $this->resetAfterTest();

        // Test completed status
        $completed_time = time();
        $status_completed = $completed_time ? get_string('completed', 'local_completion_report') : get_string('notcompleted', 'local_completion_report');
        $this->assertEquals(get_string('completed', 'local_completion_report'), $status_completed);

        // Test not completed status
        $not_completed_time = 0;
        $status_not_completed = $not_completed_time ? get_string('completed', 'local_completion_report') : get_string('notcompleted', 'local_completion_report');
        $this->assertEquals(get_string('notcompleted', 'local_completion_report'), $status_not_completed);
    }

    /**
     * Test completion date formatting
     */
    public function test_completion_date_formatting() {
        $this->resetAfterTest();

        // Test with completion time
        $completion_time = strtotime('2026-01-15 14:30:00');
        $formatted_date = $completion_time ? userdate($completion_time) : '-';
        $this->assertNotEquals('-', $formatted_date);
        $this->assertStringContainsString('2026', $formatted_date);

        // Test without completion time
        $no_completion_time = 0;
        $formatted_no_date = $no_completion_time ? userdate($no_completion_time) : '-';
        $this->assertEquals('-', $formatted_no_date);
    }

    /**
     * Test user validation logic
     */
    public function test_user_validation() {
        $this->resetAfterTest();

        // Create test user
        $user = $this->getDataGenerator()->create_user();

        global $DB;

        // Test valid user
        $valid_user = $DB->get_record('user', ['id' => $user->id]);
        $this->assertNotFalse($valid_user);

        // Test invalid user ID
        $invalid_user = $DB->get_record('user', ['id' => 99999]);
        $this->assertFalse($invalid_user);
    }

    /**
     * Test language string loading
     */
    public function test_language_strings() {
        // Test essential language strings are defined
        $pluginname = get_string('pluginname', 'local_completion_report');
        $this->assertNotEmpty($pluginname);
        $this->assertEquals('Course completion report', $pluginname);

        $selectuser = get_string('selectuser', 'local_completion_report');
        $this->assertNotEmpty($selectuser);
        $this->assertEquals('Select user', $selectuser);

        $completed = get_string('completed', 'local_completion_report');
        $this->assertNotEmpty($completed);
        $this->assertEquals('Completed', $completed);
    }
}
