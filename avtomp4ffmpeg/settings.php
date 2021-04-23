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
 * Version information
 *
 * @package    filter_avtomp4ffmpeg
 * @copyright  2021 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/filter/avtomp4ffmpeg/locallib.php');

$settings->add(new admin_setting_configexecutable('filter_avtomp4ffmpeg/ffmpegwebserviceurl',get_string('ffmpegwebserviceurl','filter_avtomp4ffmpeg'),get_string('ffmpegwebserviceurl_desc','filter_avtomp4ffmpeg'),'/usr/bin/ffmpeg'));

$settings->add(new admin_setting_configtext('filter_avtomp4ffmpeg/ffmpegwebserviceurl', get_string('ffmpegwebserviceurl', 'filter_avtomp4ffmpeg'), get_string('ffmpegwebserviceurldefault', 'filter_avtomp4ffmpeg')));

$settings->add(new admin_setting_configtext('filter_avtomp4ffmpeg/convertonlyexts', get_string('convertonlyexts', 'filter_avtomp4ffmpeg'), get_string('convertonlyexts_desc', 'filter_avtomp4ffmpeg'), 'ogg, ogv, webm'));

$settings->add(new admin_setting_configcheckbox('filter_avtomp4ffmpeg/convertaudio', get_string('convertaudio', 'filter_avtomp4ffmpeg'), get_string('convertaudio_desc', 'filter_avtomp4ffmpeg'), true, true, false));

$settings->add(new admin_setting_configtext('filter_avtomp4ffmpeg/audioffmpegsettings', get_string('audioffmpegsettings', 'filter_avtomp4ffmpeg'), get_string('audioffmpegsettings_desc', 'filter_avtomp4ffmpeg'), '-i ' . FILTER_AVTOMP4FFMPEG_INPUTFILE_PLACEHOLDER . ' ' . FILTER_AVTOMP4FFMPEG_OUTPUTFILE_PLACEHOLDER, PARAM_RAW, 80));

$settings->add(new admin_setting_configcheckbox('filter_avtomp4ffmpeg/convertvideo', get_string('convertvideo', 'filter_avtomp4ffmpeg'), get_string('convertvideo_desc', 'filter_avtomp4ffmpeg'), true, true, false));

$settings->add(new admin_setting_configtext('filter_avtomp4ffmpeg/videoffmpegsettings', get_string('videoffmpegsettings', 'filter_avtomp4ffmpeg'), get_string('videoffmpegsettings_desc', 'filter_avtomp4ffmpeg'), '-i ' . FILTER_AVTOMP4FFMPEG_INPUTFILE_PLACEHOLDER . ' ' . FILTER_AVTOMP4FFMPEG_OUTPUTFILE_PLACEHOLDER, PARAM_RAW, 80));