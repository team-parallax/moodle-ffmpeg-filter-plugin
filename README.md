# Moodle ffmpegavcc-Filter

This Repository contains the code for the moodle ffmpegavcc-filter plugin as well as the setup for development.
"ffmpegavcc" is the short form of "ffmpeg audio/video compabitbility converter".

Running `docker-compose up` in the root of the project will start a Moodle instance and its database.
When running this for the first time the instance will be initialized with a default moodle configuration.

__Note__ that the moodle setup is not feasible for production environments.

## Running webservice tests

These tests are intended to check if the communication functionality of the plugin works and handles the webservice responses correctly.

To run the test-script issue the command:
(_Omitting the tag will result in pulling the 'latest' php image_)

```bash
docker run --rm --net=host -it -v $PWD:/app php:<YOUR TAG> php /app/test.php
```
