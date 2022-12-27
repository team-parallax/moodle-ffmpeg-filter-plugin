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

require_once('util.php');
require_once "classes/curl_handler.php";
use filter_ffmpegavcc\util\Utility;
//use filter_ffmpegavcc\curl_handler;

defined('MOODLE_INTERNAL') || die();

define('FILTER_FFMPEGAVCC_JOBSPERPASS', 5);
define('FILTER_FFMPEGAVCC_JOBSTATUS_INITIAL', 0);
define('FILTER_FFMPEGAVCC_JOBSTATUS_RUNNING', 1);
define('FILTER_FFMPEGAVCC_JOBSTATUS_DONE', 2);
define('FILTER_FFMPEGAVCC_JOBSTATUS_FAILED', 3);

/**
 * @param int|null  $jobid
 * @param bool|null $displaytrace
 *
 * @throws dml_exception
 * @throws file_exception
 */
function filter_ffmpegavcc_processjobs(?int $jobid = null, ?bool $displaytrace = true)
{
    $ffmpegwebserviceurl = get_config('filter_ffmpegavcc', 'ffmpegwebserviceurl');
    Utility::log_var_dump($displaytrace,"FFMPEG_WEBSERVICE_URL: $ffmpegwebserviceurl");
    if (empty($ffmpegwebserviceurl)) {
        // show error and exit if no url is provided
        if ($displaytrace) {
            mtrace('ffmpeg webservice url not available, aborting');
        }
        return;
    }
    global $DB;
    // filter task gets called with specific jobId
    $base_query = "SELECT * 
                FROM {filter_ffmpegavcc_jobs} ";
    $query_params = array(
        'statusinit' => FILTER_FFMPEGAVCC_JOBSTATUS_INITIAL,
        'statusrunning' => FILTER_FFMPEGAVCC_JOBSTATUS_RUNNING
    );
    if ($jobid > 0) {
        // take one job at a time
        $query_conditions = "WHERE id = :jobid AND status = :statusinit OR status = :statusrunning";
        $query = $base_query . $query_conditions;
        $query_params['jobid'] = $jobid;
        // $query_params = $query_conditions;
        $jobs = $DB->get_records_sql(
            $query,
            $query_params,
            0,
            1            // since we're looking for one specific job
        );
    } else {
        $query_conditions = "WHERE status = :statusinit OR status = :statusrunning
                             ORDER BY id ASC";
        $query = $base_query . $query_conditions;
        $jobs = $DB->get_records_sql(
            $query,
            $query_params,
            0,                                           // offset
            FILTER_FFMPEGAVCC_JOBSPERPASS                // limit
        );
        if ($displaytrace) {
            mtrace('found - ' . count($jobs) . ' jobs');
            var_dump($jobs);
        }
    }
    // As long as there are jobs we process one at a time
    while ($job = array_shift($jobs)) {
        if (!$job) {
            if ($displaytrace) {
                mtrace('no jobs found');
            }
            return;
        }
        $fs = get_file_storage();
        $inputfile = $fs->get_file_by_id($job->fileid);
        if (!$inputfile) {
            update_job_and_record($job, FILTER_FFMPEGAVCC_JOBSTATUS_FAILED);
            if ($displaytrace) {
                mtrace('file ' . $job->fileid . ' not found');
            }
            return;
        }
        // create temp directory for data storage during conversion
        $filename = $inputfile->get_filename();
        $outname = get_outputfile_name($filename);
        $source_format = pathinfo($filename, PATHINFO_EXTENSION);
        $target_format = pathinfo($outname, PATHINFO_EXTENSION);
        $tempdir = make_temp_directory('filter_ffmpegavcc');
        // $filename = "$tempdir/" . $inputfile->get_id() . ".$source_format";
        try {
            // This function can either return false, or throw an exception so we need to handle both.
            if ($inputfile->copy_content_to($filename) === false) {
                throw new \file_exception('storedfileproblem', 'Could not copy file contents to temp file.');
            }
        } catch (\file_exception $fe) {
            error_log(
                "conversion for '" . $filename . "' encountered disk permission error when copying " .
                    "submitted file contents to unique temp file: '" . $filename . "'."
            );
            throw $fe;
        }
        $tmpinputfilepath = $inputfile->copy_content_to_temp('filter_ffmpegavcc');
        // $filepath = $inputfile->get_filepath();

        // Retrieve relevant info for API
        // retrieve file conversion status from the webservice to check if it is already converted.
        if ($job->status == FILTER_FFMPEGAVCC_JOBSTATUS_INITIAL) {
            // to make sure we don't try to run the same job twice
            Utility::log_var_dump($displaytrace,"Prepare conversion for jobid: $jobid");
            // $conversionfile = "@" . $filename;
            // var_dump("Conversion-File BEFORE cURLify: $conversionfile");
            $conversionfile = new CURLFile($tmpinputfilepath);
            $req_body = [
                "conversionFile" => $conversionfile,
                "originalFormat" => $source_format,
                "targetFormat" => $target_format
            ];
            $conversion_response = Utility::start_conversion($req_body);
            $conversion_id = $conversion_response->conversionId;
            Utility::log_var_dump($displaytrace,"Setting conversionid of job to $conversion_id");
            $job->conversionid = $conversion_id;
            Utility::log_var_dump($displaytrace,"Set jobstatus to running");
            update_job_and_record($job, FILTER_FFMPEGAVCC_JOBSTATUS_RUNNING);
            // we're done for now and wait for the conversion to terminate within the webservice.
            return;
        } else if ($job->status == FILTER_FFMPEGAVCC_JOBSTATUS_RUNNING) {
            // TODO: In case the result is 'converted' fetch the resultfile,
            // update the job-record and write output.
            $conversionId = $job->conversionid;
            $response = Utility::get_conversion_status($conversionId, $displaytrace);
            Utility::log_var_dump($displaytrace, "got response from webservice for conversion($conversionId)");
            if ($response->status == "converted") {
                if ($displaytrace){
                    var_dump("handle conversion-success");
                    // Handles conversion success
                    var_dump("Outfile name is $outname");
                }
                $tmpoutputfilepath = $tempdir . DIRECTORY_SEPARATOR . $outname;
                Utility::log_var_dump($displaytrace,"fetch result file");
                $response = Utility::get_converted_file($response, $tmpoutputfilepath);
                Utility::log_var_dump($displaytrace,"Prepare job-record update");
                if (!file_exists($tmpoutputfilepath) || !is_readable($tmpoutputfilepath)) {
                    Utility::log_var_dump($displaytrace,"File not found: $tmpoutputfilepath");
                    update_job_and_record($job, FILTER_FFMPEGAVCC_JOBSTATUS_FAILED);
                    if ($displaytrace) {
                        mtrace('output file not found');
                    }
                    return;
                }
                unlink($tmpinputfilepath); // Conversion was successful, so inputfile is not needed anymore
                $fs = get_file_storage();
                $inputfile_properties = $DB->get_record('files', ['id' => $inputfile->get_id()]);
                $outputfile_properties = [
                    'id' => $inputfile_properties->id,
                    'contextid'    => $inputfile_properties->contextid,
                    'component'    => $inputfile_properties->component,
                    'filearea'     => $inputfile_properties->filearea,
                    'itemid'       => $inputfile_properties->itemid,
                    'filepath'     => $inputfile_properties->filepath,
                    'filename'     => $outname,
                    'userid'       => $inputfile_properties->userid,
                    'author'       => $inputfile_properties->author,
                    'license'      => $inputfile_properties->license,
                    'timecreated'  => time(),
                    'timemodified' => time()
                ];
                try {
                    Utility::log_var_dump($displaytrace,"Write output for conversion to file $tmpoutputfilepath");
                    $outputfile = $fs->create_file_from_pathname($outputfile_properties, $tmpoutputfilepath);
                } catch (Exception $exception) {
                    Utility::log_var_dump($displaytrace,"Failed to write conversion output to disk");
                    update_job_and_record($job, FILTER_FFMPEGAVCC_JOBSTATUS_FAILED);
                    if ($displaytrace) {
                        mtrace('file could not be saved: ' . $exception->getMessage());
                    }
                    return;
                }
                Utility::log_var_dump($displaytrace,"Conversion successful for $jobid, deleting tmp-file");
                unlink($tmpoutputfilepath); // not needed anymore, since we just stored the converted outfile
                Utility::log_var_dump($displaytrace,"Update db-record for conversion");
                update_job_and_record($job, FILTER_FFMPEGAVCC_JOBSTATUS_DONE);
                if (get_config('filter_ffmpegavcc', 'deleteoriginalfiles') == true) {
                    $inputfile->delete();
                }
                if ($displaytrace) {
                    mtrace('created file id ' . $outputfile->get_id());
                    var_dump("outputfile is stored ");
                    var_dump($fs->get_file_by_id($outputfile->get_id()));
                }
                return;
            } else if ($response->status == "erroneous") {
                Utility::log_var_dump($displaytrace,"Update db-record for conversion");
                update_job_and_record($job, FILTER_FFMPEGAVCC_JOBSTATUS_FAILED);
                if ($displaytrace) {
                    mtrace('failed to convert file.');
                }
            }
            return;
        }
    }
    $displaytrace ?: mtrace("AFTER WHILE LOOP");
}
/**
 * @param $job
 * @param int $new_status
 *
 */
function update_job_and_record($job, $new_status)
{
    global $DB;
    $job->status = $new_status;
    $DB->update_record('filter_ffmpegavcc_jobs', $job);
}

function get_outputfile_name($inputfile)
{
    $infile_name = $inputfile; //->get_filename();
    if (stringEndsWith($infile_name, ".ogg")) {
        return str_replace('.ogg', '.mp3', $infile_name);
    }
    else if(
        stringEndsWith($infile_name, ".webm")
    ) {
        return str_replace('.webm', '.mp4', $infile_name);
    }
    else if(stringEndsWith($infile_name, ".ogv")) {
        return str_replace('.ogv', '.mp4', $infile_name);
    }
    return $infile_name;
}

function stringEndsWith($string, $endString): bool
{
    $len = strlen($endString);
    if ($len == 0) {
        return true;
    }
    return (substr($string, -$len) === $endString);
}