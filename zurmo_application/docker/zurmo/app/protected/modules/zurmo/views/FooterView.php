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

    class FooterView extends View
    {
        protected  $startTime;

        public function __construct()
        {
            $this->startTime = microtime(true);
        }

        protected function renderContent()
        {
            //Do not remove the Zurmo logo or Zurmo Copyright notice.
            //The interactive user interfaces in original and modified versions
            //of this program must display Appropriate Legal Notices, as required under
            //Section 5 of the GNU Affero General Public License version 3.
            //In accordance with Section 7(b) of the GNU Affero General Public License version 3,
            //these Appropriate Legal Notices must retain the display of the Zurmo
            //logo and Zurmo copyright notice. If the display of the logo is not reasonably
            //feasible for technical reasons, the Appropriate Legal Notices must display the words
            //"Copyright Zurmo Inc. 2014. All rights reserved".
            $copyrightHtml  = '<a href="http://www.zurmo.com" id="credit-link" class="clearfix"><span>' .
                             'Copyright &#169; Zurmo Inc., 2014. All rights reserved.</span></a>';
            $copyrightHtml .= $this->renderPerformance();
            $copyrightHtml .= $this->renderCacheInfo();
            $content = ZurmoHtml::tag('div', array('class' => 'container'), $copyrightHtml);
            return $content;
        }

        protected function getContainerWrapperTag()
        {
            return 'footer';
        }

        protected function renderPerformance()
        {
            $performanceMessage = null;
            if (YII_DEBUG)
            {
                if (SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
                {
                    $endTime      = microtime(true);
                    $endTotalTime = Yii::app()->performance->endClockAndGet();
                    if (defined('XHTML_VALIDATION') && XHTML_VALIDATION)
                    {
                        $performanceMessage .= '<span>Total page view time including validation: <strong>' . number_format(($endTime - $this->startTime), 3) . ' seconds.</strong></span><br />';
                    }
                    else
                    {
                        $performanceMessage .= '<span>Total page view time: <strong>' . number_format(($endTime - $this->startTime), 3) . ' seconds.</strong></span><br />';
                    }
                    $performanceMessage .= '<span>Total page time: <strong>' . number_format(($endTotalTime), 3) . ' seconds.</strong></span><br />';
                }
            }
            else
            {
                if (SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
                {
                    $endTime      = microtime(true);
                    $endTotalTime = Yii::app()->performance->endClockAndGet();
                    $performanceMessage .= 'Load time: <strong>' . number_format(($endTotalTime), 3) . ' seconds.</strong><br />';
                    
                    //$performanceMessage = $this->addHtmlForSqlPerformance($performanceMessage);
                    //$performanceMessage = $this->addHtmlForCachePerformance($performanceMessage);
                }
            }
            if (SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
            {
                if (SHOW_QUERY_DATA)
                {
                    $performanceMessage .= self::makeShowQueryDataContent();
                }
                foreach (Yii::app()->performance->getTimings() as $id => $time)
                {
                    $performanceMessage .= 'Timing: <strong>' . $id . '</strong> total time: <strong>' . number_format(($time), 3) . "</strong></br>";
                }
                $performanceMessage = '<div class="performance-info">' . $performanceMessage . '</div>';
            }
            return $performanceMessage;
        }

        private function addHtmlForSqlPerformance($performanceMessage){
            $totalQueryTime = 0;
            $queryCount = 0;
            $queryTimes = '';
            foreach($_SESSION[SessionPerformanceTimer::SESSION_VAR_NAME_SQL] as $sqlTime){
                $queryTimes .= 'Sql query time: <strong>'. number_format($sqlTime, 6) .' s</strong><br />';
                $totalQueryTime += $sqlTime;
                $queryCount++;
            }
            $performanceMessage .= 'Made ' . $queryCount . ' Queries in ' . number_format($totalQueryTime, 3) . ' seconds <br />';
            $performanceMessage .= $queryTimes;
            return $performanceMessage;
        }
        
        private function addHtmlForCachePerformance($performanceMessage){
            $totalCacheTime = 0;
            $cacheRequests = 0;
            $html = '';
            
            foreach($_SESSION[SessionPerformanceTimer::SESSION_VAR_NAME_MEMCACHE] as $cacheTime){
                $html .= 'Cache request time: ' . number_format($cacheTime, 6) . ' s<br/>';
                $totalCacheTime += $cacheTime;
                $cacheRequests++;
            }
            $performanceMessage .= 'Made ' . $cacheRequests . ' cache requests in ' . number_format($totalCacheTime, 3) . ' seconds <br />';
            return $performanceMessage . $html;
        }

        protected function renderCacheInfo(){
            if(defined('SHOW_CACHE_DUMP') && SHOW_CACHE_DUMP){
                $test = GeneralCache::getEntry('iamatestidentifier',0);
                GeneralCache::cacheEntry('iamatestidentifier', $test + 1);
                $cacheJson = GeneralCache::dumpPhpCachedItemsAsJson();
                $cacheJson = str_replace("{", "{<br/>", $cacheJson);
                $cacheJson = str_replace("}", "}<br/>", $cacheJson);
                $cacheJson = str_replace(",", ",<br/>", $cacheJson);

                return "<strong>PHP-Cache content:</strong><br/>".$cacheJson . "<br />";
            }else{
                return "";
            }
        }
        
        public static function makeShowQueryDataContent()
        {
            $performanceMessage  = static::getTotalAndDuplicateQueryCountContent();
            $duplicateData = Yii::app()->performance->getRedBeanQueryLogger()->getDuplicateQueriesData();
            if (count($duplicateData) > 0)
            {
                $performanceMessage .= '</br></br>' . '<h4>Duplicate Queries:</h4>' . '</br>';
            }
            foreach ($duplicateData as $query => $count)
            {
                $performanceMessage .= 'Count: <strong>' . $count . '</strong>&#160;&#160;&#160;Query: <strong>' . $query . '</strong></br>';
            }
            return $performanceMessage;
        }

        public static function getTotalAndDuplicateQueryCountContent()
        {
            $performanceMessage  = 'Total/Duplicate Queries: <strong>' . Yii::app()->performance->getRedBeanQueryLogger()->getQueriesCount();
            $performanceMessage .= '/' . Yii::app()->performance->getRedBeanQueryLogger()->getDuplicateQueriesCount() . '</strong>';
            return $performanceMessage;
        }
    }
?>
