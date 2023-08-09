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
 * Utility functions for the filter_ffmpegavcc plugin.
 *
 * @package    filter_ffmpegavcc
 * @copyright  2021 Sven Patrick Meier <sven.patrick.meier@team-parallax.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_ffmpegavcc\util;
require_once($CFG->dirroot . '/filter/ffmpegavcc/event.php');


use \filter_ffmpegavcc\curl_handler;
use \filter_ffmpegavcc\event\log_event;

class Utility
{
    /**
     * Retrieve the current status of a conversion
     * 
     * @param string $conversion_id     The uuid of the conversion document
     * @return mixed
     */
    static function get_conversion_status(string $conversion_id, bool $displaytrace)
    {
        Utility::log_to_moodle("Request conversion status of $conversion_id");
        if ($displaytrace) {
            var_dump("Request conversion status of $conversion_id");
        }
        $url = Utility::get_webservice_route("conversion/$conversion_id?v2=true");
        if ($displaytrace){
            var_dump("Requesting route: $url");
        }
        return curl_handler::fetch_url_data($url);
    }
    static function get_converted_file($response, $target_path)
    {
        $conversion_id = $response->conversionId;
        Utility::log_to_moodle("Request converted file for id $conversion_id");
        $url = Utility::get_webservice_route("conversion/$conversion_id/download");
        $file_response = curl_handler::download_file($url, $target_path);
        // file_put_contents("./response.mp4", $file_response);
        // $response->resultfile = $file_response;
        return $response;
    }
    /**
     * Get Ffmpeg Webservice url from settings
     *
     * @return string   The url for an endpoint of the webservice
     */
    static function get_ffmpeg_webservice_url(): string
    {
        Utility::log_to_moodle("Retrieve webserivce url from configuration");
        $ffmpegwebservice_url = get_config('filter_ffmpegavcc', 'ffmpegwebserviceurl');

        if (substr($ffmpegwebservice_url, -1) !== '/') {
            $ffmpegwebservice_url .= '/';
        }
        Utility::log_to_moodle("URL: $ffmpegwebservice_url");
        return $ffmpegwebservice_url;
    }
    /**
     * Returns an aggregated path consisting of the base url of the webservice and a path
     * 
     * @param string $path      The path for the request url
     * @return string
     */
    static function get_webservice_route(string $path): string
    {
        return Utility::get_ffmpeg_webservice_url() . $path;
    }
    /**
     * Handles a conversion-status response from the webservice
     * 
     * @param mixed $response   The response object returned from the webservice
     */
    static function handle_conversion_status_response($response)
    {
        Utility::log_to_moodle("Handle conversion status response for id: " . $response->conversionId);
        $result = $response;
        if ($response->status == "converted") {
            // TODO: Handle converted object
            Utility::log_to_moodle("Handle 'converted' response");
            $result = Utility::get_converted_file($response);
        }
        return $result;
    }
    /**
     * Utility function to log plugin processes and operations
     * 
     * @param mixed $msg   The message to log
     */
    static function log_to_moodle($msg)
    {
        $event = log_event::create(array(
           "other" => array(
               "dump" => var_export($msg, true)
           ),
            'context' => \context_system::instance(),
        ));
        $event->trigger();
    }
    /**
     * Sends a 'ping' message to the ffmpeg-webservice to determine if the plugin can be used
     * 
     * @return bool
     */
    static function ping_webservice(): bool
    {
        $url = Utility::get_webservice_route("ping");
        Utility::log_to_moodle("Send ping to the ffmpeg Webservice\n" . $url);
        $response = curl_handler::fetch_url_data($url, true);
        Utility::log_to_moodle("Got response " . $response);
        return $response == "\"pong\"";
    }
    /**
     * Sends the data needed for conversion to the Webservice.
     * @param mixed $data   The conversion request body
     * @return mixed The response object from the web-api
     */
    static function start_conversion($data)
    {
        Utility::log_to_moodle('Send conversion request to Webservice');
        $url = Utility::get_webservice_route('conversion/v2');
        Utility::log_to_moodle("Sending request to following url: $url");
        // Utility::log_to_moodle($data);
        $response_ping = Utility::ping_webservice();
        Utility::log_to_moodle($response_ping);
        $response = curl_handler::convert_to_file($url, $data);
        Utility::log_to_moodle($response);
        Utility::log_to_moodle('Received Response');
        return $response;
    }
    static function log_var_dump(bool $displaytrace, $message)
    {
        if ($displaytrace) {
            Utility::log_to_moodle($message);
        }
    }
}
