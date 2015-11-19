<?php

/**
 * Description of PerformanceTimer
 *
 * @author bloe
 */
abstract class PerformanceTimer {
    
   private $startTime;
   private $stopTime;
   protected $category; 
   
   const CATEGORY_SQL = "sql";
   const CATEGORY_MEMCACHE = "memcache";
   
   public function __construct($category) {
       $this->category = $category;
   }
   
   public function startTimer()
   {
       $this->startTime = microtime(true);
   }
   
   public function stopTimer()
   {
       $this->stopTime = microtime(true);
   }
   
   public function saveTime()
   {
       if(!isset($this->startTime)) throw new Exception("Error, cannot save time: Timer has never been started!");
       if(!isset($this->stopTime)) throw new Exception("Error, cannot save time: Timer has never been stopped!");
       
       $duration = $this->stopTime - $this->startTime;
       $this->saveStoppedTime($duration);
   }
   
   abstract function saveStoppedTime($duration);
    
}
