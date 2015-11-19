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

    /**
     * Log sql queries into file.
     * ZurmoRedBeanPluginQueryLogger doesn't contain all data we need to log, so we had to extend this class.
     * Code is optimized, so data are written only once to file, in EndRequestBehavior
     */
    class ZurmoRedBeanQueryFileLogger extends ZurmoFileLogger implements RedBean_ILogger
    {
        /**
         * @var string log file name
         */
        protected $logFile = 'redBeanSqlQuery.log';

        /**
         * Save sql query logs into file
         */
        public function processLogs()
        {
            $logFile = $this->getLogPath() . DIRECTORY_SEPARATOR . $this->getLogFile();
            if (@filesize($logFile) > $this->getMaxFileSize()*1024)
            {
                $this->rotateFiles();
            }
            $fp = @fopen($logFile, 'a');
            @flock($fp, LOCK_EX);
            @fwrite($fp, $this->getRequestInfoDetails());
            @fwrite($fp, $this->logs);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }

        /**
         * Create header info for query logs
         * @return string
         */
        protected function getRequestInfoDetails()
        {
            $requestInfoString = '';
            if (isset(Yii::app()->request))
            {
                if (Yii::app() instanceof WebApplication)
                {
                    $pathInfo = Yii::app()->request->getPathInfo();
                    $queryInfo = Yii::app()->request->getQueryString();
                }
                else
                {
                    $pathInfo  = '';
                    $queryInfo = '';
                }
                $requestInfoString .= '--------------------------------' .         PHP_EOL;
                $requestInfoString .= 'Request Date: ' . date('F j, Y, g:i:s a') . PHP_EOL;
                $requestInfoString .= 'Request Url: '  . $pathInfo .               PHP_EOL;
                $requestInfoString .= 'Query String: ' . $queryInfo .              PHP_EOL;
                $requestInfoString .= '-------------------------------' .          PHP_EOL;
            }
            return $requestInfoString;
        }
    }
?>