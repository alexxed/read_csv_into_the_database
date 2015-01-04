<?php

namespace Alexxed;

use Alexxed\Database\Database;
use Alexxed\Database\DatabaseException;
use Utils\ProgressBar;

/**
 * Copies a table from a database to another database
 * @todo store the pid in a file to make sure two processes are not running at the same time
 * @todo for CSV files, you could use a fseek instead of reading lines to resume
 * Class DatabaseCopier
 * @package Alexxed
 */
class DatabaseCopier
{
    protected $positionFilePath;
    protected $resume = true;
    /**
     * Copies all records from a database to another
     * @todo loose coupling
     * @param Database $from_database this is where the rows are copied from
     * @param Database $to_database   this is where the rows are copied to
     * @param boolean  $show_progress show a progress bar
     * @throws DatabaseException
     * @return boolean success of the operation
     */
    public function copy(
        Database $from_database,
        Database $to_database,
        $show_progress = false
    ) {

        if ($from_database->isOpen() == false) {
            throw new DatabaseException('The from database is not opened');
        }

        if ($to_database->isOpen() == false) {
            throw new DatabaseException('The to database is not opened');
        }


        $this->_setPositionFilePath($from_database, $to_database);

        $current_position = 0;
        if ($this->resume) {
            $resume_from = $this->_getPosition();
        } else {
            $resume_from = 0;
        }

        // skip rows if already copied
        if ($resume_from > 0) {
            while ($row = $from_database->read()) {
                if ($resume_from == $current_position) {
                    break;
                }
                $current_position++;
            }
            $current_position = $resume_from;
        }

        if ($show_progress) {
            $progress = new ProgressBar($from_database->count());
        }

        while ($row = $from_database->read()) {
            $to_database->write($row);
            $current_position++;
            $this->_setPosition($current_position);

            if ($show_progress) {
                $progress->display($current_position);
            }
        }

        // The copy operation finished, so remove the position file
        unlink($this->getPositionFilePath());

        return true;
    }

    /**
     * Get the last saved position
     * @return int
     */
    private function _getPosition()
    {
        $positionFilePath = $this->getPositionFilePath();
        if (file_exists($positionFilePath)) {
            return file_get_contents($positionFilePath);
        } else {
            return 0;
        }
    }

    /**
     * Save the next row number to be processed
     * @param integer $position the row number
     * @return boolean
     */
    private function _setPosition($position)
    {
        return file_put_contents($this->getPositionFilePath(), $position);
    }

    /**
     * Get the path to the file where the last row number to be processed was saved
     * @return mixed
     */
    public function getPositionFilePath()
    {
        return $this->positionFilePath;
    }

    /**
     * Sets the path to the file where the next row number to be processed is saved
     * @param Database $from_database this is where the rows are copied from
     * @param Database $to_database   this is where the rows are copied to
     * @return bool
     */
    private function _setPositionFilePath(
        Database $from_database,
        Database $to_database
    ) {
        $this->positionFilePath = sprintf(
            '%s_%s_to_%s.position',
            basename(__FILE__),
            basename($from_database->getDatabase()),
            basename($to_database->getDatabase())
        );

        return true;
    }

    /**
     * Guess the MySQL table columns from the first CSV row
     * @todo move this into its own class
     * @todo replace hardcoded values with getters, e.g. getStringColumnType()
     * @param Database $from_database this is where the rows are copied from
     * @param Database $to_database   this is where the rows are copied to
     * @return array
     */
    public function guessTableStructureFromCsv(
        Database $from_database,
        Database $to_database
    ) {
        $row = $from_database->read();
        $from_database->resetRead();
        $columns = array();
        foreach ($row as $key=>$value) {
            $key = 'col' . $key;
            if (is_numeric($value)) {
                $columns[$key] = 'INTEGER';
            } elseif (is_string($value) && strlen($value) < 255) {
                $columns[$key] = 'VARCHAR(255)';
            } else {
                $columns[$key] = 'TEXT';
            }
        }

        return $columns;
    }

    /**
     * Resumes a previous operation if available
     * @param boolean $resume resume a previous operation if available
     */
    public function setResume($resume)
    {
        $this->resume = $resume;
    }
}