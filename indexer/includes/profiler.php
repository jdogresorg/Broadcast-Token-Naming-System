<?php
/*********************************************************************
 * profiler.php - Handles tracking execution time
 ********************************************************************/
class Profiler {
    public $start_time;

    // start() - handle starting the timer
    public function __construct() {
        $t = microtime();
        $time = explode (" ", $t);
        $this->start_time = doubleval($time[0])+$time[1];
    }

    // finish() - handles stopping the timer and returning the runtime
    function finish() {
        $t = microtime();
        $time = explode (" ", $t);
        $end_time = doubleval($time[0])+$time[1];
        $process_time= substr(sprintf ("%01.4f", $end_time-$this->start_time), 0, 6);
        return $process_time;
    }
}
