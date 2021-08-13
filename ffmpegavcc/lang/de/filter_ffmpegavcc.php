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

$string['filtername'] = 'ffmpegavcc - Kompatibilitätsfilter';
$string['ffmpegwebserviceurl'] = 'Ffmpeg-Webservice URL';
$string['ffmpegwebserviceurldefault'] = 'https://beispiel-konvertierungsservice.com';
$string['ffmpegwebserviceurl_desc'] = 'URL für den ffmpeg-Konverterservice';
$string['convertaudio'] = 'Audio konvertieren';
$string['convertaudio_desc'] = 'Konvertiert OGG Dateien zu MP3';
$string['convertvideo'] = 'Video konvertieren';
$string['convertvideo_desc'] = 'Konvertiert OGV/WEBM Dateien zu MP4';
$string['processjobs_task'] = 'Prozess des Re-Encodings von Mediendateien';
$string['convertonlyexts'] = 'Nur Dateien mit dieser Endung konvertieren';
$string['convertonlyexts_desc'] = 'Komma separierte Liste an Dateiendungen die konvertiert werden sollen';
$string['privacy:metadata'] = 'Der ffmpegavcc - Kompatibilitätsfilter speichert keine persönlichen Daten.';
$string['test_ffmpegws'] = 'Testen Sie die Verbindung zum Ffmpeg-Webservice';
$string['test_ffmpeg_connection_ok'] = 'Der Webservice ist erreichbar und bereit.';
$string['test_ffmpeg_connection_error'] = 'Der Webservice hat auf den "Ping" nicht reagiert oder ist nicht erreichbar.';
$string['deleteoriginalfiles'] = 'Original Dateien löschen';
$string['deleteoriginalfiles_desc'] = 'Löscht Originale der Dateien und behält nur konvertierte Resultate der Dateien.';