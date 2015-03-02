<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PerformanceLogger
 *
 * @author bloe
 */
class PerformanceLogger extends ZurmoFileLogger {
    
    protected $collapsed = true;
    protected $category = '';
    
    const SPLIT_CHARACTER = ' ';
    
    public function processLogs() {
        $requestId = $this->getRequestId();
        $data = RequestPerformanceTimer::getDataForCategory($this->category);
        
        $sum = 0.0;
        foreach ($data as $line){
            if(!$this->collapsed){
                $formattedNumber = number_format($line, 6);
                $this->writeLine($formattedNumber, $requestId);
            }else{
                $sum += $line;
            }
        }
        
        if($this->collapsed){
            $this->writeLine($sum, $requestId);
        }
        
        parent::processLogs();
    }
    
    protected function writeLine($time, $requestId){
        parent::addLog($requestId . self::SPLIT_CHARACTER . $time);
    }

    protected function getRequestId(){
        if(isset($_SERVER['UNIQUE_ID'])){
            return $_SERVER['UNIQUE_ID'];
        }
        return '';
    }
    
    public function getCollapsed(){
        return $this->collapsed;
    }
    
    public function setCollapsed($collapsed){
        $this->collapsed = $collapsed;
    }
    
    public function setCategory($category){
        $this->category = $category;
    }
    
    public function getCategory(){
        return $this->category;
    }
}
