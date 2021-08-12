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
 * Test that unowebconv is configured correctly
 *
 * @package   filter_ffmpegavcc
 * @copyright 2021 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require('./util.php');
use filter_ffmpegavcc\util\Utility;

$PAGE->set_url(new moodle_url('/filter/ffmpegavcc/testffmpegfilter.php'));
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());

$strheading = get_string('test_ffmpegws', 'filter_ffmpegavcc');
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('plugins', 'admin'));
$PAGE->navbar->add(get_string('filtername', 'filter_ffmpegavcc'),
        new moodle_url('/admin/settings.php', array('section' => 'filtersettingffmpegavcc')));
$PAGE->navbar->add($strheading);
$PAGE->set_heading($strheading);
$PAGE->set_title($strheading);

$ffmpeg_response = Utility::ping_webservice();
if ($ffmpeg_response == 'pong') {
    $ffmpeg_response = $OUTPUT->notification(
        get_string('test_ffmpeg_connection_ok', 'filter_ffmpegavcc'),
        'success'
    );
}
else {
    $ffmpeg_response = $OUTPUT->notification(
        get_string('test_ffmpeg_connection_error', 'filter_ffmpegavcc'),
        'error'
    );
}

$returl = new moodle_url('/admin/settings.php', array('section' => 'filtersettingffmpegavcc'));
$msg .= $ffmpeg_response;
$msg .= $OUTPUT->continue_button($returl);

echo $OUTPUT->header();
echo $OUTPUT->box($msg, 'generalbox');
echo $OUTPUT->footer();
