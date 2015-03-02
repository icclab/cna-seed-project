<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ZurmoCacheHttpSession
 *
 * @author bloe
 */
class ZurmoCacheHttpSession extends CCacheHttpSession {
	
    /**
	 * Initializes the application component.
	 * This method overrides the parent implementation by checking if cache is available.
	 */
	public function init()
	{
		$this->_cache=Yii::app()->getComponent($this->cacheID);
		if(!($this->_cache instanceof ICache))
			throw new CException(Yii::t('yii','CCacheHttpSession.cacheID is invalid. Please make sure "{id}" refers to a valid cache application component.',
				array('{id}'=>$this->cacheID)));
	}
}
