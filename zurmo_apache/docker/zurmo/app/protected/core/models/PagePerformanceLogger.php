<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PagePerformanceLogger
 *
 * @author bloe
 */
class PagePerformanceLogger extends ZurmoFileLogger{
    
    const SPLIT_CHARACTER = ' ';
    
    public function processLogs() {
        $requestId = $this->getRequestId();
        $duration = Yii::app()->performance->endClockAndGet();
        
        $formattedNumber = number_format($duration, 6);
        $this->writeLine($formattedNumber, $requestId);
        
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
}
