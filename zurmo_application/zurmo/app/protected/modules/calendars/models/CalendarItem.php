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
      * Model representing a calendar item to be displayed in the calendar view.
      */
    class CalendarItem
    {
        const MAXIMUM_TITLE_LENGTH = 30;

        /**
         * @var string
         */
        protected $title;

        /**
         * @var string
         */
        protected $startDateTime;

        /**
         * @var string
         */
        protected $endDateTime;

        /**
         * @var int
         */
        protected $calendarId;

        /**
         * @var string
         */
        protected $modelClass;

        /**
         * @var int
         */
        protected $modelId;

        /**
         * @var string
         */
        protected $moduleClassName;

        /**
         * @var string
         */
        protected $color;

        public function getTitle()
        {
            return $this->title;
        }

        public function getStartDateTime()
        {
            return $this->startDateTime;
        }

        public function getEndDateTime()
        {
            return $this->endDateTime;
        }

        public function setTitle($title)
        {
            $this->title = $title;
        }

        public function setStartDateTime($startDateTime)
        {
            $this->startDateTime = $startDateTime;
        }

        public function setEndDateTime($endDateTime)
        {
            $this->endDateTime = $endDateTime;
        }

        public function getCalendarId()
        {
            return $this->calendarId;
        }

        public function setCalendarId($calendarId)
        {
            $this->calendarId = $calendarId;
        }

        public function getModelClass()
        {
            return $this->modelClass;
        }

        public function getModelId()
        {
            return $this->modelId;
        }

        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        public function setModelClass($modelClass)
        {
            $this->modelClass = $modelClass;
        }

        public function setModelId($modelId)
        {
            $this->modelId = $modelId;
        }

        public function setModuleClassName($moduleClassName)
        {
            $this->moduleClassName = $moduleClassName;
        }

        public function getColor()
        {
            return $this->color;
        }

        public function setColor($color)
        {
            $this->color = $color;
        }
    }
?>