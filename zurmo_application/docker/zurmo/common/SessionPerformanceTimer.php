<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This performance timer saves the stopped time in the session array
 *
 * @author bloe
 */
class SessionPerformanceTimer extends PerformanceTimer{
    
    const SESSION_VAR_NAME_SQL = "sqlTimes";
    const SESSION_VAR_NAME_MEMCACHE = "memcacheTimes";
    
    protected static $categorySessionVariableMapping = array(
        PerformanceTimer::CATEGORY_SQL => self::SESSION_VAR_NAME_SQL,
        PerformanceTimer::CATEGORY_MEMCACHE => self::SESSION_VAR_NAME_MEMCACHE
    );


    public function saveStoppedTime($duration) {
        if(!isset(self::$categorySessionVariableMapping[$this->category])){
            throw new Exception("The stopped time could not be saved because the category "
                    . $this->category . " is unknown!");
        }
        $sessionVariableName = self::$categorySessionVariableMapping[$this->category];
        
        if (!isset($_SESSION[$sessionVariableName])){
          $_SESSION[$sessionVariableName] = array();
        }
        array_push($_SESSION[$sessionVariableName], $duration);
    }
    
    public static function resetSessionVariables(){
        foreach (self::$categorySessionVariableMapping as $sessionVariableName) {
            $_SESSION[$sessionVariableName] = array();
        }
    }
    
    public static function getDataForCategory($category){
        $sessionVariableName = self::$categorySessionVariableMapping[$category];
        $data = $_SESSION[$sessionVariableName];
        return $data;
    }

}
