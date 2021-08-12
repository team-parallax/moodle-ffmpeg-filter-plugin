<?php
require 'ffmpegavcc/classes/curl_handler.php';
require 'ffmpegavcc/util.php';

use \filter_ffmpegavcc\util\Utility;
/* Array to store occuring errors during assertions */

$errors = array();
/* Error handler for assertions */
function error_handler($file, $line, $code, $desc = null)
{
    global $errors;
    echo "Assertion failed at $file:$line: $code";
    if ($desc) {
        echo ": $desc";
    }
    echo "\n";
    $errors[$line] = false;
}
/* Generic assertion executor function */
function test_assertion($assertion, $desc = "")
{
    assert($assertion, $desc);
}
/* Assertion configuration */
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);
assert_options(ASSERT_BAIL, false);
assert_options(ASSERT_EXCEPTION, false);
assert_options(ASSERT_CALLBACK, 'error_handler');
/* Base url for the webservice */
$base_url = "http://localhost:3000";

 // TODO: Add test cases to array
 $test_cases = array();
 // Ping Route
 $ping_route = "/ping";
 $ping_expected_response = "pong";
 // GET /conversion
 $conversion_route = "/conversion";
 $conversion_expected_response = new stdClass;
 $conversion_expected_response->{'conversions'} = array();
 $conversion_expected_response->{'remainingConversions'} = 0;
 // GET /conversion/conversionId     
 // Should handle FAILURE because non-existent conversionId-url param
 // generate uuid
 $non_conversion_id = file_get_contents('/proc/sys/kernel/random/uuid');
 $conversion_route = "/conversion/" . $non_conversion_id;

 $test_cases[$ping_route] = $ping_expected_response;
 $test_cases[$conversion_route] = $conversion_expected_response;

 foreach ($test_cases as $route => $expected_res) {
     $raw = $route == "/ping";
     $route = $base_url . $route;
     $response = \filter_ffmpegavcc\curl_handler::fetch_url_data(
         $route
     );
     echo "Asserting for route: " . $route . "\n";
     test_assertion($response == $expected_res);
 }
$conversionfile = "file_example_OGG_1MB.ogg";
if (function_exists("curl_file_create")) {
   $conversionfile = curl_file_create("file_example_OGG_1MB.ogg");
} else {
   $conversionfile = "@$conversionfile";
}
$req_body = [
   "conversionFile" => $conversionfile,
   "originalFormat" => '.ogg',
   "targetFormat" => '.mp3'
];
var_dump($req_body);
$conversion_response = Utility::start_conversion($req_body);
var_dump($conversion_response);
/* 
 * If $errors contains a 'false' one of the assertions failed.
 * Therefore exit the program with non-zero exitcode.
 */
in_array(false, $errors) ? exit(1) : exit(0);
