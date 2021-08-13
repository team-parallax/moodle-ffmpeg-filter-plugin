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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/filter/ffmpegavcc/locallib.php');

$ffmpeg_webservice_url_setting = new admin_setting_configtext(
    'filter_ffmpegavcc/ffmpegwebserviceurl',
    get_string('ffmpegwebserviceurl', 'filter_ffmpegavcc'),
    get_string('ffmpegwebserviceurl_desc', 'filter_ffmpegavcc'),
    get_string('ffmpegwebserviceurldefault', 'filter_ffmpegavcc')
);
$settings->add($ffmpeg_webservice_url_setting);

$test_url = new moodle_url('/filter/ffmpegavcc/testffmpegfilter.php');
$link = html_writer::link($test_url, get_string('test_ffmpegws', 'filter_ffmpegavcc'));
$settings->add(new admin_setting_heading('test_ffmpegws', '', $link));

$settings->add(new admin_setting_configtext(
    'filter_ffmpegavcc/convertonlyexts',
    get_string('convertonlyexts', 'filter_ffmpegavcc'),
    get_string('convertonlyexts_desc', 'filter_ffmpegavcc'),
    'ogg, ogv, webm'
));

$settings->add(new admin_setting_configcheckbox(
    'filter_ffmpegavcc/convertaudio',
    get_string('convertaudio', 'filter_ffmpegavcc'),
    get_string('convertaudio_desc', 'filter_ffmpegavcc'),
    true,
    true,
    false
));

$settings->add(new admin_setting_configcheckbox(
    'filter_ffmpegavcc/convertvideo',
    get_string('convertvideo', 'filter_ffmpegavcc'),
    get_string('convertvideo_desc', 'filter_ffmpegavcc'),
    true,
    true,
    false
));

$settings->add(new admin_setting_configcheckbox(
    'filter_ffmpegavcc/deleteoriginalfiles',
    get_string('deleteoriginalfiles', 'filter_ffmpegavcc'),
    get_string('deleteoriginalfiles_desc', 'filter_ffmpegavcc'),
    false)
);