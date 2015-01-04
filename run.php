<?php

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/src/Alexxed/Database/DatabaseException.php';
require dirname(__FILE__) . '/src/Alexxed/Database/Database.php';
require dirname(__FILE__) . '/src/Alexxed/Database/CsvDatabase.php';
require dirname(__FILE__) . '/src/Alexxed/Database/MysqlDatabase.php';
require dirname(__FILE__) . '/src/Alexxed/DatabaseCopier.php';
require dirname(__FILE__) . '/src/Alexxed/Utils/Progress.php';
require dirname(__FILE__) . '/src/Alexxed/Utils/SampleCsvGenerator.php';


if (!array_search('--config', $argv)) {
    exit(
        sprintf(
            "Usage: %s --config config.json [--clean-to-table] [--create-to-table]\n",
            basename(__FILE__)
        )
    );
}

$config = json_decode(file_get_contents($argv[array_search('--config', $argv)+1]));

$csv_db = new \Alexxed\Database\CsvDatabase();
$csv_db->setDatabase($config->from_database->csv_file_path);
$csv_db->setOptions(
    $config->from_database->maxLineLength,
    $config->from_database->delimiter,
    $config->from_database->enclosure,
    $config->from_database->escape
);
$csv_db->open();

$mysql_db = new \Alexxed\Database\MysqlDatabase();
$mysql_db->setServer($config->to_database->server);
$mysql_db->setUsername($config->to_database->username);
$mysql_db->setPassword($config->to_database->password);
$mysql_db->setDatabase($config->to_database->database);
$mysql_db->setTable($config->to_database->table);
$mysql_db->open();


$importer = new \Alexxed\DatabaseCopier();

if (array_search('--clean-to-table', $argv)) {
    $mysql_db->dropTable();
    $importer->setResume(false);
}

if (array_search('--create-to-table', $argv)) {
    $mysql_db->createTable($importer->guessTableStructureFromCsv($csv_db, $mysql_db));
    $importer->setResume(false);
}

$importer->copy($csv_db, $mysql_db, true);

$csv_db->close();
$mysql_db->close();



