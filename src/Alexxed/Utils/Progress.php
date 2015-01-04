<?php
namespace Utils;

/**
 * Class ProgressBar
 * @package Utils
 */
class ProgressBar
{
    protected $total;
    protected $startTime;

    /**
     * Initialize the ProgressBar with the total number of records
     * @param integer $total The total number of records
     */
    public function __construct($total)
    {
        $this->total = $total;
        $this->startTime = time();
    }

    /**
     * Get the progress bar as a string
     * @param integer $current The current record
     * @return string
     */
    public function get($current)
    {
        $progress = '';
        for ($i = 1; $i < 11; $i++) {
            if (($current * 10) / $this->total <= $i) {
                $progress .= '-';
            } else {
                $progress .= '+';
            }
        }

        $records_per_second = $current/(max(time() - $this->startTime, 1));

        return sprintf(
            "\rProgress: [%s], %s", $progress,
            sprintf(
                '%d%% done, %d/%d copied, %d records per second, estimated finish in %d minutes',
                intval(($current * 100) / $this->total),
                $current,
                $this->total,
                floor($records_per_second),
                floor($this->total/max($records_per_second, 1)/60)
            )
        );
    }

    /**
     * Display the progress bar by flushing the output buffer
     * @param integer $current The current record
     * @return null
     */
    public function display($current)
    {
        echo $this->get($current);
        @ob_flush();
    }
}