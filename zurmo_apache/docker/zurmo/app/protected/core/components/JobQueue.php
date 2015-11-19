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
     * Helper class for managing job queues. Override as needed.
     */
    class JobQueue extends CApplicationComponent
    {
        const MAX_DELAY_NOISE_IN_SECONDS = 15;

        protected $queuedJobs = array();

        /**
         * @param string $jobType
         * @param int $delay - seconds to delay job
         * @param array $params
         */
        public function add($jobType, $delay = 0, $params = array())
        {
            assert('is_string($jobType)');
            assert('is_int($delay)');
            assert('is_array($params)');
            if (!$this->isJobTypeWithDelayAndParamsAlreadyExistInQueueAndReplaceItInQueue($jobType, $delay, $params, true))
            {
                $this->queuedJobs[$delay][] = array(
                    'jobType' => $jobType,
                    'params' => $params
                );
            }
        }

        /**
         * Check if job type already exist in job queue, with same delay and params
         * @param string $jobType
         * @param int $delay
         * @param array $params
         * @param bool $replaceExistingJobInQueueWithLatterOne
         * @return bool
         */
        protected function isJobTypeWithDelayAndParamsAlreadyExistInQueueAndReplaceItInQueue($jobType, $delay, $params,
                                                                                           $replaceExistingJobInQueueWithLatterOne = false)
        {
            assert('is_string($jobType)');
            assert('is_int($delay)');
            assert('is_array($params)');
            assert('is_bool($replaceExistingJobInQueueWithLatterOne)');
            foreach ($this->queuedJobs as $existingJobDelay => $queuedJobs)
            {
                foreach ($queuedJobs as $key => $queuedJob)
                {
                    if ($queuedJob['jobType'] == $jobType &&
                        $this->isDelayWithinAcceptableTolerance($existingJobDelay, $delay, self::MAX_DELAY_NOISE_IN_SECONDS))
                    {
                        if ($queuedJob['params'] == $params)
                        {
                            if ($replaceExistingJobInQueueWithLatterOne)
                            {
                                unset($this->queuedJobs[$existingJobDelay][$key]);
                                if (empty($this->queuedJobs[$existingJobDelay]))
                                {
                                    unset($this->queuedJobs[$existingJobDelay]);
                                }
                                $this->queuedJobs[$delay][] = array(
                                    'jobType' => $jobType,
                                    'params' => $params
                                );
                            }
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        /**
         * Check if new job delay is in bounds of another
         * @param int $existingJobDelay
         * @param int $delay
         * @param int $noise
         * @return bool
         */
        protected function isDelayWithinAcceptableTolerance($existingJobDelay, $delay, $noise)
        {
            if ($existingJobDelay <= $delay  &&
                $existingJobDelay + $noise >= $delay)
            {
                return true;
            }
            return false;
        }

        public function getAll()
        {
            return $this->queuedJobs;
        }

        public function deleteAll()
        {
            $this->queuedJobs = array();
        }

        /**
         * Override if there is processing to complete. see @EndRequestBehavior
         */
        public function processAll()
        {
        }

        /**
         * Override and toggle as needed.
         * @return bool
         */
        public function isEnabled()
        {
            return false;
        }

        public function getQueueJobLabel()
        {
            return Zurmo::t('JobsManagerModule', 'Queue Job');
        }

        public function getQueueJobAgainLabel()
        {
            return Zurmo::t('JobsManagerModule', 'Queue Job Again');
        }

        public function processByJobTypeAndDelay($jobType, $delay, MessageLogger $messageLogger)
        {
        }

        /**
         * For a given model, and dateTime attribute, resolve to add a job by the job type. The delay is calculated
         * based on the value of the dateTime attribute
         * @param RedBeanModel $model
         * @param $attributeName
         * @param $jobType
         */
        public function resolveToAddJobTypeByModelByDateTimeAttribute(RedBeanModel $model, $attributeName, $jobType)
        {
            assert('is_string($attributeName)');
            assert('is_string($jobType)');
            if ($model->getIsNewModel() || isset($model->originalAttributeValues[$attributeName]))
            {
                if (DateTimeUtil::isDateTimeStringNull($model->{$attributeName}))
                {
                    $secondsFromNow       = 0;
                }
                else
                {
                    $processDateTimeStamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp($model->{$attributeName});
                    $secondsFromNow       = $processDateTimeStamp - time();
                }
                if ($secondsFromNow <= 0)
                {
                    $delay = 0;
                }
                else
                {
                    $delay = $secondsFromNow;
                }
                Yii::app()->jobQueue->add($jobType, $delay + 5);
            }
        }
    }
?>