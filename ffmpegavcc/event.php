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
 * Event logging functions for the filter_ffmpegavcc plugin.
 *
 * @package    filter_ffmpegavcc
 * @copyright  2023 Marcel Michelfelder <marcel.michelfelder@team-parallax.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace filter_ffmpegavcc\event;

use \core\event\manager;

class log_event extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'filter_ffmpegavcc_jobs';
    }

    public static function get_name() {
        return "ffmpegavcc filter";
//        return get_string('eventcoursemoduleviewed', 'mod_mymodule');
    }

    public function get_description() {
        return $this->other["dump"];
    }
//
//    public function get_url() {
//        return new \moodle_url('/mod/mymodule/view.php', array('id' => $this->objectid));
//    }
}