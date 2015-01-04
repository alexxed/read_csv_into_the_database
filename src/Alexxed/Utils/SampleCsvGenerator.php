<?php
namespace Alexxed\Utils;

use Utils\ProgressBar;

class SampleCsvGenerator
{
    /**
     * Create a test CSV file
     * @param string  $csv_file_path path to a CSV file
     * @param integer $rows          number of rows to write
     * @param bool    $show_progress show the progress of the operation
     */
    public function __construct(
        $csv_file_path,
        $rows = 100000,
        $show_progress = false
    ) {
        $csv_handle = fopen($csv_file_path, 'w+');
        $progress = new ProgressBar($rows);
        for ($i = 0; $i < $rows; $i++) {
            $fields = array();
            $fields[] = $i; // unique identifier
            for ($j = 0; $j < 5; $j++) {
                $fields[] = $this->_getRandomInteger(); // integers
            }

            for ($j = 0; $j < 15; $j++) {
                $fields[] = $this->_getRandomString(); // strings
            }

            for ($j = 0; $j < 5; $j++) {
                $fields[] = $this->_getRandomJson(); // json
            }

            fputcsv(
                $csv_handle,
                $fields,
                ',',
                '"'
            );

            if ($show_progress) {
                $progress->display($i);
            }
        }

        fclose($csv_handle);
    }

    /**
     * Generates a random string
     * @return string
     */
    private function _getRandomString()
    {
        return
            substr(
                " abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
                mt_rand(0, 51),
                1
            ) .
            substr(
                md5(time()),
                1
            );
    }

    /**
     * Generates a random integer
     * @return int
     */
    private function _getRandomInteger()
    {
        return mt_rand(1, 1000);
    }

    /**
     * Generate an array of random strings
     * @param integer $size how many elements in the array
     * @return array
     */
    private function _getRandomStringArray($size)
    {
        $result = array();
        for ($i=0; $i<$size; $i++) {
            $result[] = $this->_getRandomString();
        }

        return $result;
    }

    /**
     * Get a random sample JSON string
     * @return mixed
     */
    private function _getRandomJson()
    {
        $array = array(
            'colors' => $this->_getRandomStringArray(100),
            'size' => array(
                'chart' => $this->_getRandomStringArray(50),
                'legend' => $this->_getRandomStringArray(85)
            )
        );

        $object = json_decode(json_encode($array), false);

        return array_rand(
            array(
                json_encode($array) => 1,
                json_encode($object) => 1
            ),
            1
        );
    }
}