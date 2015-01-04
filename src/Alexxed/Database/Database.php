<?php

namespace Alexxed\Database;

abstract class Database
{
    protected $database;
    protected $is_connected = false;

    /**
     * Selects the working database
     * @param String $database the database name
     * @return mixed
     */
    abstract public function setDatabase($database);

    /**
     * Returns the current database name
     * @return String the selected database name
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Open the database for operations
     * @return mixed
     */
    abstract public function open();

    /**
     * Closes the database for operations
     * @return mixed
     */
    abstract public function close();

    /**
     * Read one row from the database
     * @return mixed
     */
    abstract public function read();

    /**
     * Write one row to the database
     * @param Array $row associative array with row data
     * @return mixed
     */
    abstract public function write($row);

    /**
     * Count all the available rows
     * @return integer
     */
    abstract public function count();

    /**
     * Check whether the database is opened for operations
     * @return bool
     */
    public function isOpen()
    {
        return $this->is_connected;
    }

    /**
     * Reset a current pointer in a read operation
     * @return null
     */
    public function resetRead()
    {
        $this->close();
        $this->open();
    }
}