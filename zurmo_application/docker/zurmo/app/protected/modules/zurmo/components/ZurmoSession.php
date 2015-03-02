<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ZurmoSession extends CHttpSession
    {
        private $cacheSessionHandler;
        private $dbSessionHandler;
        
        public static function createSessionToken($sessionId, $userPass)
        {
            $token = md5($sessionId . $userPass);
            return $token;
        }
        
        public function __construct() {
            $this->cacheSessionHandler = new ZurmoCacheHttpSession();
            Yii::log('created cache session handler in zurmosession', CLogger::LEVEL_TRACE, 'session');
            $this->dbSessionHandler = new CDbHttpSession();
            Yii::log('created database session handler in zurmosession', CLogger::LEVEL_TRACE, 'session');
        }
        
        public function init()
        {
            $this->cacheSessionHandler->init();
            Yii::log('initialized cache session handler in zurmosession', CLogger::LEVEL_TRACE, 'session');
            parent::init();
        }
        
        public function getIsInitialized()
	{
            return $this->cacheSessionHandler->getIsInitialized() && $this->dbSessionHandler->getIsInitialized();
	}
        
        /**
	 * Returns a value indicating whether to use custom session storage.
	 * This method overrides the parent implementation and always returns true.
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return true;
	}

	/**
	 * Updates the current session id with a newly generated one.
	 * Please refer to {@link http://php.net/session_regenerate_id} for more details.
	 * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
	 * @since 1.1.8
	 */
	public function regenerateID($deleteOldSession=false)
	{
            Yii::log('regenerate session id, delete old?: ' . $deleteOldSession, CLogger::LEVEL_INFO, 'session');
            $oldSessionId = session_id();
            Yii::log('destroy session '. $oldSessionId .' in cache', CLogger::LEVEL_INFO, 'session');
            $this->cacheSessionHandler->destroySession($oldSessionId);
            Yii::log('regenerate session id in database', CLogger::LEVEL_INFO, 'session');
            $this->dbSessionHandler->regenerateID($deleteOldSession);
	}


	/**
	 * Session open handler.
	 * Do not call this method directly.
	 * @param string $savePath session save path
	 * @param string $sessionName session name
	 * @return boolean whether session is opened successfully
	 */
	public function openSession($savePath,$sessionName)
	{
            Yii::log('open session in db, savePath=' . $savePath . ',sessionName=' . $sessionName, CLogger::LEVEL_INFO, 'session');
            $this->dbSessionHandler->openSession($savePath, $sessionName);
            Yii::log('open session in cache, savePath=' . $savePath . ',sessionName=' . $sessionName, CLogger::LEVEL_INFO, 'session');
            $this->cacheSessionHandler->openSession($savePath, $sessionName);
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
            Yii::log('read session ' . $id, CLogger::LEVEL_INFO, 'session');
            $data = '';
            try{
                Yii::log('try to read session from cache ' . $id, CLogger::LEVEL_INFO, 'session');
                $data = $this->cacheSessionHandler->readSession($id);
                Yii::log('got [' . $data . '] from cache for session ' . $id, CLogger::LEVEL_INFO, 'session');
            }catch(Exception $e){
                //ignore cache miss
                Yii::log('cache could not be accessed id=' . $id . ',Exception='.$e, CLogger::LEVEL_INFO, 'session');
            }
            
            if($data === ''){
                Yii::log('could not get data for session from cache id=' . $id . ' try database', CLogger::LEVEL_INFO, 'session');
                $data = $this->dbSessionHandler->readSession($id);
                Yii::log('read from database [' . $data . '],id='.$id, CLogger::LEVEL_INFO, 'session');
            }
            return $data;
	}

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id,$data)
	{
            //error_log is used instead of Yii:log because it doesn't work here
            error_log('write data=[' . $data . '] to session '.$id, E_NOTICE);
            $success = false;
            try{
                error_log('try to write data to cache ' . $id,E_NOTICE);
                $success = $this->cacheSessionHandler->writeSession($id,$data);
                error_log('cache write success?: ' . $success . ', id='.$id, E_NOTICE);
            }catch(Exception $e){
                // ignore cache miss
                error_log('cache could not be accessed id=' . $id . ',Exception='.$e,E_NOTICE);
            }
            
            error_log('write session to database id=' . $id, E_NOTICE);
            $success = $this->dbSessionHandler->writeSession($id,$data);
            error_log('database reported success?: ' . $success . ',id='.$id, E_NOTICE);
            return $success;
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
            Yii::log('session ' . $id . ' requested to be destroyed. try to remove from cache first', CLogger::LEVEL_INFO, 'session');
            $success = $this->cacheSessionHandler->destroySession($id);
            Yii::log('cache destroyed session successfully: ' . $success . ',id='.$id, CLogger::LEVEL_INFO, 'session');
            Yii::log('try destroying session in database ' . $id, CLogger::LEVEL_INFO, 'session');
            $success = $this->dbSessionHandler->destroySession($id);
            Yii::log('database destroyed session ' . $id . ' successfully: '.$success, CLogger::LEVEL_INFO, 'session');
            return $success;
	}
        
	/**
	 * Session GC (garbage collection) handler.
	 * Do not call this method directly.
	 * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
	 * @return boolean whether session is GCed successfully
	 */
	public function gcSession($maxLifetime)
	{
            Yii::log('session is garbage collected maxLifetime=' . $maxLifetime, CLogger::LEVEL_INFO, 'session');
            $this->dbSessionHandler->gcSession($maxLifetime);
            Yii::log('session was garbage collected in database', CLogger::LEVEL_INFO, 'session');
	}
        
        public function setConnectionId($connectionId){
            $this->dbSessionHandler->connectionID = $connectionId;
        }
        
        public function getConnectionId(){
            
            return $this->dbSessionHandler->connectionID;
        }
        
        public function setAutoCreateSessionTable($autoCreateSessionTable){
            $this->dbSessionHandler->autoCreateSessionTable = $autoCreateSessionTable;
        }
        
        public function getAutoCreateSessionTable(){
            return $this->dbSessionHandler->autoCreateSessionTable;
        }
        
        public function getCacheID(){
            return $this->cacheSessionHandler->cacheID;
        }
        
        public function setCacheID($cacheID){
            $this->cacheSessionHandler->cacheID = $cacheID;
        }
        
    }
?>
