<?php

namespace Alexxed\Database;

/**
 * Class CsvDatabase
 * @package Alexxed\Database
 */
class CsvDatabase extends Database
{
    protected $fileHandle;
    protected $options = array(
        'maxLineLength' => 0, // unlimited
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '"'
    );
    /**
     * Selects the working database
     * @param String $database the database name
     * @return mixed
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Sets parameters for CSV reading
     * @param integer $maxLineLength maximum number of bytes per line
     * @param string  $delimiter     the field delimiter character
     * @param string  $enclosure     the field enclosure character
     * @param string  $escape        the character used to escape the field enclosure
     * @return boolean
     */
    public function setOptions($maxLineLength, $delimiter, $enclosure, $escape)
    {
        $this->options['maxLineLength'] = $maxLineLength;
        $this->options['delimiter'] = $delimiter;
        $this->options['enclosure'] = $enclosure;
        $this->options['escape'] = $escape;

        return true;
    }

    /**
     * Open the database for operations
     * @return boolean
     * @throws DatabaseException
     */
    public function open()
    {
        if (is_null($this->database)) {
            throw new DatabaseException('The database is not set');
        }

        if (!file_exists($this->database)) {
            throw new DatabaseException(
                sprintf('The file "%s" does not exist', $this->database)
            );
        }

        $fileHandle = fopen($this->database, 'r');
        if (is_resource($fileHandle)) {
            $this->fileHandle = $fileHandle;
            $this->is_connected = true;
            return true;
        } else {
            throw new DatabaseException(
                sprintf(
                    'The file "%s" cannot be opened for reading',
                    $this->database
                )
            );
        }
    }

    /**
     * Closes the database for operations
     * @return mixed
     * @throws DatabaseException
     */
    public function close()
    {
        if (fclose($this->fileHandle) == false) {
            throw new DatabaseException(
                sprintf('Cannot close "%s"', $this->database)
            );
        } else {
            $this->is_connected = false;
            return true;
        }
    }

    /**
     * Read one row from the database
     * @return mixed
     */
    public function read()
    {
        if (feof($this->fileHandle)) {
            return false;
        } else {
            return fgetcsv(
                $this->fileHandle,
                $this->options['maxLineLength'],
                $this->options['delimiter'],
                $this->options['enclosure'],
                $this->options['escape']
            );
        }
    }

    /**
     * Write one row to the database
     * @param Array $row associative array with row data
     * @return mixed
     * @throws DatabaseException
     */
    public function write($row)
    {
        throw new DatabaseException(__METHOD__ . ' not implemented yet');
    }

    /**
     * Count all the available rows
     * @return integer
     */
    public function count()
    {
        return intval(exec(sprintf("wc -l '%s'", escapeshellarg($this->database))));
    }

}