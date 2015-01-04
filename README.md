Description
===========

This code sample will take a given CSV file and import its contents into a given MySQL table.

Assumptions
===========

* all rows in the CSV files have values in all the columns
* the column content is more or less the same in each row; if not, you better create the destination MySQL table yourself; don't forget to add the id column (autoincrement)
* the CSV file uses , as field delimiter, " as field enclosure and " as escape character

Configuration
=============

Copy config.json.sample to config.json and edit accordingly.

Run
===

To create a sample CSV file
    
    php generate_csv_sample.php --file DatabaseCopierTest.php.csv --rows 100000 
This will generate a 4GB CSV test file.

To copy from the CSV file to the MySQL table:

    php run.php --config config.json --clean-to-table --create-to-table 
    

Drop the last two parameters if resuming an interrupted operation.

    php run.php --config config.json

* the code will auto-magically create the MySQL table's structure
* it will add an id as a primary key, autoincremented

If your CSV file has headers on the first line, try 
    
    tail -n +2 DatabaseCopierTest.php.csv > DatabaseCopierTest1.php.csv 
to get a new file without the first line, otherwise column detection will probably fail.


