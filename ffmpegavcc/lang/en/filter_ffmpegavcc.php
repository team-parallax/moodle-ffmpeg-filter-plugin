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
 * @package    filter_ffmpegavcc
 * @copyright  2021 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'ffmpegavcc - Compatibility Filter';
$string['ffmpegwebserviceurl'] = 'Ffmpeg-Webservice URL';
$string['ffmpegwebserviceurldefault'] = 'https://example-converterservice.com';
$string['ffmpegwebserviceurl_desc'] = 'URL of the conversion service for ffmpeg';
$string['convertaudio'] = 'Convert audio';
$string['convertaudio_desc'] = 'Convert audio OGG files to MP3';
$string['convertvideo'] = 'Convert video';
$string['convertvideo_desc'] = 'Convert video WEBM files to MP4';
$string['processjobs_task'] = 'Process reencoding media files';
$string['convertonlyexts'] = 'convert only these extensions';
$string['convertonlyexts_desc'] = 'Comma-separated list of file extensions to be converted';
$string['privacy:metadata'] = 'The ffmpegavcc - Compatibility Filter does not store any personal data.';
$string['test_ffmpegws'] = 'Test Connection to Ffmpeg-Webservice';
$string['test_ffmpeg_connection_ok'] = 'The webservice seems to be correctly configured and ready.';
$string['test_ffmpeg_connection_error'] = 'The webservice was not reachable or unresponsive when pinging.';
$string['deleteoriginalfiles'] = 'Delete source files';
$string['deleteoriginalfiles_desc'] = 'Delete source files and keep only converted files.';