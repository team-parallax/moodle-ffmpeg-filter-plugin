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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    filter_ffmpegavcc
 * @copyright  2021 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_ffmpegavcc\task;

defined('MOODLE_INTERNAL') || die();

class processjobs_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('processjobs_task', 'filter_ffmpegavcc');
    }

    /**
     * Run the task
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/filter/ffmpegavcc/locallib.php');
        \filter_ffmpegavcc_processjobs(null, true);
    }

}
