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
 * @copyright  2019 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * May be generated like so: `date +"%Y%m%d"000`
 * We accidentally pushed an additional number to the version like so:
 * `YYYYMMDDXXX` (it should be `YYYYMMDDXX`) - I dont want to decrease the number because we dont want to lower
 * the version number, so we are going with it.
 */
$plugin->version = 20230809000;
$plugin->requires = 2019052000; // Moodle 3.7
$plugin->component = 'filter_ffmpegavcc';

