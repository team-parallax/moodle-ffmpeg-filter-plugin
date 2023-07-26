<?php

/**
 * Class for handling cURL requests for the communication
 * between the plugin and the webservice.
 * 
 * @package filter_ffmpegavcc
 * @copyright 2021 Sven Patrick Meier <sven.patrick.meier@gmail.com>
 */

namespace filter_ffmpegavcc;

// require_once('util.php');

use filter_ffmpegavcc\util\Utility;

class curl_handler
{
    public static function convert_to_file($url, $data)
    {
        Utility::log_to_moodle("conversion function l. 1");
        if (!$url || !is_string($url)) {
            return false;
        }
        Utility::log_to_moodle("Init curl handler");
        $ch = curl_init($url);
        if ($ch === false) {
            Utility::log_to_moodle("$url not found.");
            Utility::log_to_moodle(curl_errno($ch) . " " . curl_error($ch));
            return false;
        }
        Utility::log_to_moodle("CONFIGURING CURL");
        $ch = (new curl_handler)->configure_curl_session($ch);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: application/json',
            'Content-Type: multipart/form-data'
        ));
        Utility::log_to_moodle("SET POST FIELDS");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // var_dump("DATA FOR REQ: \n$data");
        $response = curl_exec($ch);
        Utility::log_to_moodle("Response from webservice is");
        Utility::log_to_moodle($response);
        $err = curl_errno($ch);
        if ($err) {
            Utility::log_to_moodle($err . " " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $result = utf8_encode($response);
        return json_decode($result);
    }
    public static function fetch_url_data($url, $raw = false)
    {
        if (!$url || !is_string($url)) {
            return false;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        $ch = (new curl_handler)->configure_curl_session($ch);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $err = curl_errno($ch);
        if ($err) {
            Utility::log_to_moodle($err . " " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        if ($raw) {
            return $response;
        }
        $result = utf8_encode($response);
        return json_decode($result);
    }
    public static function download_file($url, $target_path)
    {
        if (!$url || !is_string($url)) {
            return false;
        }
        $fh = fopen($target_path, "w");
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_exec($ch);
        $err = curl_errno($ch);
        if ($err) {
            Utility::log_to_moodle($err . " " . curl_error($ch));
            curl_close($ch);
            fclose($fh);
            return false;
        }
        curl_close($ch);
        fclose($fh);
    }
    public static function get_http_response_code($url)
    {
        if (!$url || !is_string($url)) {
            return false;
        }
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        $ch = self::configure_curl_session($ch);
        curl_setopt($ch, CURLOPT_HEADER, true);    // get headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // no body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $err = curl_errno($ch);
        if ($err) {
            Utility::log_to_moodle($err . " " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        // note: php.net documentation shows this returns a string, but really it returns an int
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }
    private static function configure_curl_session($curl_handler, $follow_redirects = true)
    {
        if ($follow_redirects) {
            curl_setopt($curl_handler, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl_handler, CURLOPT_MAXREDIRS, 10);      // max redirects
        } else {
            curl_setopt($curl_handler, CURLOPT_FOLLOWLOCATION, false);
        }
        curl_setopt($curl_handler, CURLOPT_CONNECTTIMEOUT, 10);     // timeout in seconds to wait
        // pretend we're a regular browser
        curl_setopt(
            $curl_handler,
            CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1"
        );
        return $curl_handler;
    }
}
