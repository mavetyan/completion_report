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
 * Report service class.
 *
 * @package    local_completion_report
 * @copyright  2026 Mher Avetyan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_completion_report;

defined('MOODLE_INTERNAL') || die();

/**
 * Service for completion report operations.
 */
class report_service {

    /** @var user_repository */
    private $repository;

    /**
     * Constructor.
     *
     * @param user_repository|null $repository
     */
    public function __construct(user_repository $repository = null) {
        $this->repository = $repository ?? new user_repository();
    }

    /**
     * Get all active users.
     *
     * @return array
     */
    public function get_users(): array {
        return $this->repository->get_active_users();
    }

    /**
     * Get completion report data for a user.
     *
     * @param int $userid User ID
     * @return array Report data
     * @throws \moodle_exception
     */
    public function get_user_completion_data(int $userid): array {
        $user = $this->repository->get_valid_user($userid);
        if (!$user) {
            throw new \moodle_exception('usernotfound', 'error');
        }

        // Get enrolled courses using Moodle API.
        $courses = enrol_get_all_users_courses($userid, true);

        // Get completion data efficiently in one query.
        $courseids = array_keys($courses);
        $completions = $this->repository->get_completion_records($userid, $courseids);

        // Combine course and completion data.
        $reportdata = [];
        foreach ($courses as $course) {
            $completion = $completions[$course->id] ?? null;
            $reportdata[] = [
                'course' => $course,
                'iscomplete' => $completion && $completion->timecompleted,
                'timecompleted' => $completion ? $completion->timecompleted : null
            ];
        }

        // Sort by course name.
        usort($reportdata, function($a, $b) {
            return strcasecmp($a['course']->fullname, $b['course']->fullname);
        });

        return [
            'user' => $user,
            'completions' => $reportdata
        ];
    }

    /**
     * Prepare template data for completion table.
     *
     * @param array $completiondata Raw completion data
     * @return array Template-ready data
     */
    public function prepare_template_data(array $completiondata): array {
        if (empty($completiondata)) {
            return [
                'courses' => false,
                'strings' => [
                    'nocoursesenrolled' => get_string('nocoursesenrolled', 'local_completion_report')
                ]
            ];
        }

        $templatedata = [];
        foreach ($completiondata as $data) {
            $course = $data['course'];
            $iscomplete = $data['iscomplete'];
            $timecompleted = $data['timecompleted'];

            $templatedata[] = [
                'courseurl' => (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(),
                'coursename' => format_string($course->fullname),
                'status' => $iscomplete ?
                    get_string('completed', 'local_completion_report') :
                    get_string('notcompleted', 'local_completion_report'),
                'completiondate' => $timecompleted ?
                    userdate($timecompleted, get_string('strftimedatetime', 'langconfig')) :
                    '-'
            ];
        }

        return [
            'courses' => true,
            'coursedata' => $templatedata,
            'strings' => [
                'coursename' => get_string('coursename', 'local_completion_report'),
                'completionstatus' => get_string('completionstatus', 'local_completion_report'),
                'timecompleted' => get_string('timecompleted', 'local_completion_report')
            ]
        ];
    }
}
