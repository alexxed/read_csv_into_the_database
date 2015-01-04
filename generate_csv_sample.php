<?php
require dirname(__FILE__) . '/src/Alexxed/Utils/SampleCsvGenerator.php';
require dirname(__FILE__) . '/src/Alexxed/Utils/Progress.php';

if (!array_search('--file', $argv) || !array_search('--rows', $argv)) {
    exit(
        sprintf(
            "Usage: %s --file DatabaseCopierTest.php.csv --rows 100000\n",
            basename(__FILE__)
        )
    );
}

new \Alexxed\Utils\SampleCsvGenerator($argv[array_search('--file', $argv)+1], $argv[array_search('--rows', $argv)+1], true);
