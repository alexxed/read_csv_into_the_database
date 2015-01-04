<?php

namespace Alexxed\Database;

class MysqlDatabase extends Database
{
    protected $server;
    protected $username;
    protected $password;
    protected $table;
    protected $databaseLink;
    protected $databaseHandle;

    /**
     * Sets the database server host
     * @param string $server the database server host
     * @return boolean
     */
    public function setServer($server)
    {
        $this->server = $server;

        return true;
    }

    /**
     * Sets the database username
     * @param string $username the username used to connect to the database server
     * @return boolean
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return true;
    }

    /**
     * Sets the database password for the associated username
     * @param string $password the password used to connect to the database server
     * @return bool
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return true;
    }

    /**
     * Sets the database used for operations
     * @param String $database the database to be selected
     * @return boolean
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return true;
    }

    /**
     * Sets the table used for operations
     * @param String $table the table to be selected
     * @return boolean
     */
    public function setTable($table)
    {
        $this->table = $table;

        return true;
    }

    /**
     * Open the database for operations
     * @return boolean
     * @throws DatabaseException
     * @todo create getters for the class properties so they can be mocked
     */
    public function open()
    {
        if (is_null($this->database)) {
            throw new DatabaseException('The database is not set');
        }

        if (is_null($this->table)) {
            throw new DatabaseException('The table is not set');
        }

        if (is_null($this->server)) {
            throw new DatabaseException('The server is not set');
        }

        if (is_null($this->username)) {
            throw new DatabaseException('The username is not set');
        }

        if (is_null($this->password)) {
            throw new DatabaseException('The password is not set');
        }

        $databaseLink = mysql_connect(
            $this->server,
            $this->username,
            $this->password
        );

        if ($databaseLink == false) {
            throw new DatabaseException(
                sprintf(
                    'The server "%s", username "%s" or password is not correct',
                    $this->server,
                    $this->username
                )
            );
        } else {
            $this->databaseLink = $databaseLink;
        }

        if (!mysql_select_db($this->database, $this->databaseLink)) {
            throw new DatabaseException(
                sprintf(
                    'The database "%s" cannot be opened for reading',
                    $this->database
                )
            );
        } else {
            $handle = mysql_query(
                sprintf('SELECT * FROM %s', $this->table),
                $this->databaseLink
            );
            if ($handle == false) {
                throw new DatabaseException(sprintf('Could not read from table "%s"', $this->table));
            } else {
                $this->databaseHandle = $handle;
                $this->is_connected = true;
                return true;
            }
        }
    }

    /**
     * Closes the database for operations
     * @return mixed
     * @throws DatabaseException
     */
    public function close()
    {
        if (mysql_close($this->databaseLink) == false) {
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
     * @throws DatabaseException
     */
    public function read()
    {
        throw new DatabaseException(__METHOD__ . ' not implemented yet');
    }

    /**
     * Write one row to the database
     * @param Array $row associative array with row data
     * @return mixed
     * @throws DatabaseException
     */
    public function write($row)
    {
        $sql_row = array('NULL');
        foreach ($row as $key=>$value) {
            $sql_row[] = sprintf(
                "'%s'",
                mysql_real_escape_string($value, $this->databaseLink)
            );
        }

        if ( mysql_query(
            sprintf(
                'INSERT INTO %s () VALUES (%s)',
                $this->table,
                join(',', $sql_row)
            )
        ) == false ) {
            throw new DatabaseException(
                sprintf(
                    'Could not insert row %d: %s ss',
                    $row[0],
                    mysql_error($this->databaseLink)
                )
            );
        } else {
            return true;
        }
    }

    /**
     * Count all the available rows
     * @return integer
     */
    public function count()
    {
        return mysql_num_rows($this->databaseHandle);
    }

    public function createTable($columns)
    {
        $sql_columns = array();
        foreach ($columns as $name=>$type) {
            $sql_columns[] = sprintf("`%s` %s", $name, $type);
        }

        $table_sql = sprintf(
            "CREATE TABLE IF NOT EXISTS %s ( " .
            "id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, " .
            "%s " .
            ")",
            $this->table,
            join(',', $sql_columns)
        );

        return mysql_query($table_sql, $this->databaseLink);
    }

    public function dropTable()
    {
        return mysql_query(sprintf('DROP TABLE `%s`', $this->table));
    }

}