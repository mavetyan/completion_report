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
global $CFG, $PAGE, $OUTPUT;

/**
 * Course completion report main entry point.
 *
 * @package    local_completion_report
 * @copyright  2026 Mher Avetyan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/completionlib.php');

use local_completion_report\report_service;
use local_completion_report\form\user_selection_form;

// Parameters.
$userid = optional_param('userid', 0, PARAM_INT);

// Access control - keep in page level, not in service.
require_login();
if (!is_siteadmin()) {
    throw new moodle_exception('accessdenied', 'admin');
}

// Setup admin page.
admin_externalpage_setup('local_completion_report');

$PAGE->set_url('/local/completion_report/index.php', $userid ? ['userid' => $userid] : []);
$PAGE->set_title(get_string('pluginname', 'local_completion_report'));
$PAGE->set_heading(get_string('pluginname', 'local_completion_report'));

echo $OUTPUT->header();

$service = new report_service();

if ($userid) {
    // Show completion report for selected user.
    try {
        $reportdata = $service->get_user_completion_data($userid);
        $user = $reportdata['user'];
        $completions = $reportdata['completions'];

        echo $OUTPUT->heading(get_string('completionreportfor', 'local_completion_report', fullname($user)));

        echo \html_writer::link(
            new moodle_url('/local/completion_report/index.php'),
            get_string('backtoselection', 'local_completion_report'),
            ['class' => 'btn btn-secondary mb-3']
        );

        // Render
        $templatedata = $service->prepare_template_data($completions);
        echo $OUTPUT->render_from_template('local_completion_report/completion_table', $templatedata);

    } catch (moodle_exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }

} else {
    // Show user selection form.
    echo $OUTPUT->heading(get_string('selectuser', 'local_completion_report'));

    // Format users for display here (presentation logic).
    $users = $service->get_users();
    $useroptions = [];
    foreach ($users as $user) {
        $useroptions[$user->id] = fullname($user) . ' (' . $user->email . ')';
    }

    $form = new user_selection_form(null, ['users' => $useroptions]);

    if ($data = $form->get_data()) {
        $redirecturl = new moodle_url('/local/completion_report/index.php', ['userid' => $data->userid]);
        redirect($redirecturl);
    }

    $form->display();
}

echo $OUTPUT->footer();
