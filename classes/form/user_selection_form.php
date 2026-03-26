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
 * User selection form.
 *
 * @package    local_completion_report
 * @copyright  2026 Mher Avetyan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_completion_report\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Form for selecting a user.
 */
class user_selection_form extends \moodleform {

    /**
     * Define form elements.
     */
    protected function definition() {
        $mform = $this->_form;
        $users = $this->_customdata['users'];

        $mform->addElement('select', 'userid', get_string('selectuser', 'local_completion_report'),
                           ['' => get_string('choosedots')] + $users);
        $mform->addRule('userid', get_string('required'), 'required', null, 'client');
        $mform->setType('userid', PARAM_INT);

        $this->add_action_buttons(false, get_string('viewreport', 'local_completion_report'));
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['userid'])) {
            $errors['userid'] = get_string('required');
        }

        return $errors;
    }
}
