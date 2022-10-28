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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/filter/ffmpegavcc/locallib.php');
require_once('util.php');

use filter_ffmpegavcc\util\Utility;


class filter_ffmpegavcc extends moodle_text_filter
{

    function filter($text, array $options = array())
    {
        Utility::log_to_file("start filter");
        if (!is_string($text)) {
            // non string data can not be filtered anyway
            return $text;
        }

        if (get_config('filter_ffmpegavcc', 'convertaudio') === false && get_config(
            'filter_ffmpegavcc',
            'convertvideo'
        ) === false) {
            Utility::log_to_file("plugin not configured to convert");
            // plugin is not configured to convert anything
            return $text;
        }

        if ((get_config('filter_ffmpegavcc', 'convertaudio') === false || strpos(
            $text,
            '</audio>'
        ) === false) && (get_config(
            'filter_ffmpegavcc',
            'convertvideo'
        ) === false || strpos($text, '</video>') === false)) {
            Utility::log_to_file("nothing to do");
            // nothing to do
            return $text;
        }

        $pattern = '/(<(audio|video)[^>]+>)[^<]*(<source src="[^"]+"[^>]*>[^<]+?)+?[^<]*(<\/\2>)/sU';
        $text = preg_replace_callback($pattern, 'filter_ffmpegavcc_checksources', $text);
        
        return $text;
    }
}

/**
 * @param $matches
 *
 * @return mixed|string
 * @throws coding_exception
 * @throws dml_exception
 * @throws file_exception
 */
function filter_ffmpegavcc_checksources($matches)
{
    Utility::log_to_file("check sources");
    global $CFG;

    $fullmatch = array_shift($matches);
    Utility::log_to_file("fullmatch: " . $fullmatch);
    $tag_open = array_shift($matches);
    Utility::log_to_file("tag_open: " . $tag_open);
    $tag_close = array_pop($matches);
    Utility::log_to_file("tag_close: " . $fullmatch);

    // now we just need to get rid of the capturing group that was used for backreference ("\2" in regex)
    array_shift($matches);

    $type = preg_replace('/[^a-z]/', '', strtolower($tag_close)); // 'audio' or 'video'
    Utility::log_to_file("type: " . $type);

    $source_tags = $matches;
    Utility::log_to_file("source_tags: " . $source_tags);
    $extra_source_tags = [];

    if (count($source_tags) > 1) {
        // there are several source tags, we therefore assume it's sufficient
        Utility::log_to_file("more than 1 source tag found");
        return $fullmatch;
    }

    // so we can now assume there is only one element in $source_tags
    $source_tag = array_pop($source_tags);
    Utility::log_to_file("source_tag: " . $source_tag);
    $src_url = preg_replace('/^.*src="([^"]+)".*$/s', '$1', $source_tag);
    Utility::log_to_file("src_url: " . $src_url);

    if (strpos($src_url, $CFG->wwwroot) === false) {
        Utility::log_to_file("file not hosted on moodle server");
        // file is not hosted on the Moodle server, abort
        return $fullmatch;
    }

    $toconvert_fileextensions = array_map('trim', explode(',', get_config('filter_ffmpegavcc', 'convertonlyexts')));
    Utility::log_to_file("file extensions to convert: " . $toconvert_fileextensions);
    $src_fileextension = preg_replace('/^.*\.([a-zA-Z0-9]{3,4})$/', '$1', strtolower($src_url));
    Utility::log_to_file("src_fileextension: " . $src_fileextension);
    if (!in_array($src_fileextension, $toconvert_fileextensions)) {
        Utility::log_to_file("no extra source required");
        // no need to continue as no extra source is required
        return $fullmatch;
    }

    $filepath = str_replace($CFG->wwwroot . '/pluginfile.php/', '', $src_url);
    Utility::log_to_file("filepath: " . $filepath);
    $filepathargs = explode('/', ltrim($filepath, '/'));
    Utility::log_to_file("filepathargs: " . $filepathargs);

    if (count($filepathargs) < 4) { // always at least context, component and filearea
        Utility::log_to_file("filepaths not enough");
        print_error('invalidarguments');
    }

    $contextid = (int)array_shift($filepathargs);
    Utility::log_to_file("contextid: " . $contextid);
    $component = clean_param(array_shift($filepathargs), PARAM_COMPONENT);
    Utility::log_to_file("component: " . $component);
    $filearea = clean_param(array_shift($filepathargs), PARAM_AREA);
    Utility::log_to_file("filearea: " . $filearea);
    $inputfilename = clean_param(array_pop($filepathargs), PARAM_FILE);
    Utility::log_to_file("inputfilename: " . $inputfilename);

    $outputfile_ext = ($type == 'audio')
        ? 'mp3'
        : 'mp4';
    Utility::log_to_file("outputfile_ext: " . $outputfile_ext);
    $outputfilename = preg_replace('/\.' . $src_fileextension . '/i', '.' . $outputfile_ext, $inputfilename);
    Utility::log_to_file("outputfilename: " . $outputfilename);

    $inputfile = null;
    $outputfile = null;

    $fs = get_file_storage();
    $files = $fs->get_area_files($contextid, $component, $filearea);
    Utility::log_to_file("files: " . $files);

    foreach ($files as $file) {
        
        if ($file->get_filename() === $inputfilename) {
            Utility::log_to_file("input file with name: " . $inputfilename . " was found");
            // this is the OGG or WEBM file
            $inputfile = $file;
        }
        if ($file->get_filename() === $outputfilename) {
            Utility::log_to_file("output file with name: " . $outputfilename . " was found");
            // this is the MP3 or MP4 file
            $outputfile = $file;
        }
    }
    Utility::log_to_file("before is null 1");
    if (is_null($inputfile) && is_null($outputfile)) {
        Utility::log_to_file("could not find input file");
        // could not find input file, abort
        return $fullmatch;
    }
    Utility::log_to_file("before is null 2");

    if (is_null($outputfile) && get_config('filter_ffmpegavcc', 'convert' . $type)) {
        global $DB;
        $existingjob = $DB->get_record('filter_ffmpegavcc_jobs', ['fileid' => $inputfile->get_id()]);
        if ($existingjob){
            Utility::log_to_file("existingjob found ");    
        }
        // first make sure there's not yet a job planned for this file

        if (!$existingjob) {
            // let's create a job to convert this file
            $job = (object)[
                'fileid' => $inputfile->get_id(),
                'status' => FILTER_FFMPEGAVCC_JOBSTATUS_INITIAL
            ];
            $jobid = $DB->insert_record('filter_ffmpegavcc_jobs', $job);
            Utility::log_to_file("jobid: " . $jobid);
            if ($type == 'audio') {
                Utility::log_to_file("process audio job immediately");
                // process audio jobs immediately
                \filter_ffmpegavcc_processjobs($jobid, false);
            }
        }
        Utility::log_to_file("return full match");

        return $fullmatch;
    } else {
        Utility::log_to_file("display mp4: ");
        // we're good to display the MP4 :))
        $extra_source_tags = [
            '<source src="' . str_replace($inputfilename, $outputfilename, $src_url) . '" type="' . $outputfile->get_mimetype() . '">'
        ];
    }
    Utility::log_to_file("source_tag: " . $source_tag);
    if (is_null($inputfile)) {
        $alltags = array_merge([$tag_open], $extra_source_tags, [$tag_close]);
    } else {
        $alltags = array_merge([$tag_open], [$source_tag], $extra_source_tags, [$tag_close]);
    }
    return implode("\n", $alltags);
}
