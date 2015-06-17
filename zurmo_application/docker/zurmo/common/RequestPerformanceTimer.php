<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RequestPerformanceTimer
 *
 * @author bloe
 */
class RequestPerformanceTimer extends PerformanceTimer{
    
    const REQUEST_VAR_NAME_SQL = "sqlTimes";
    const REQUEST_VAR_NAME_MEMCACHE = "memcacheTimes";
    
    protected static $data = array();
    
    protected static $categoryRequestVariableMapping = array(
        PerformanceTimer::CATEGORY_SQL => self::REQUEST_VAR_NAME_SQL,
        PerformanceTimer::CATEGORY_MEMCACHE => self::REQUEST_VAR_NAME_MEMCACHE
    );

    public function saveStoppedTime($duration) {
        if(!isset(self::$categoryRequestVariableMapping[$this->category])){
            throw new Exception("The stopped time could not be saved because the category "
                    . $this->category . " is unknown!");
        }
        $requestVariableName = self::$categoryRequestVariableMapping[$this->category];
        
        if (!isset(self::$data[$requestVariableName])){
          self::$data[$requestVariableName] = array();
        }
        array_push(self::$data[$requestVariableName], $duration);
    }
    
    public static function getDataForCategory($category){
   	$categoryVariableSet = isset(self::$categoryRequestVariableMapping[$category]);
	$dataVariableSet = isset(self::$data[self::$categoryRequestVariableMapping[$category]]);
	
	if (!$categoryVariableSet || !$dataVariableSet){
	        $data = array();
        }else{
                $requestVariableName = self::$categoryRequestVariableMapping[$category];
                $data = self::$data[$requestVariableName];
        }
        return $data;
    }
}
