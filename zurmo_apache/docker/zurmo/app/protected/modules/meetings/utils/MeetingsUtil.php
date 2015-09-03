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

    class MeetingsUtil
    {
        /**
         * @param Meeting $meeting
         * @param string $link
         * @return string
         */
        public static function renderDaySummaryContent(Meeting $meeting, $link)
        {
            $content = null;
            $title       = '<h3>' . $meeting->name . '<span>' . $link . '</span></h3>';
            $dateContent = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($meeting->startDateTime);
            $localEndDateTime = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($meeting->endDateTime);
            if ($localEndDateTime != null)
            {
                $dateContent .= ' - ' . $localEndDateTime;
            }
            $dateContent .= '<br/>';
            $content .= self::renderActivityItemsContentsExcludingContacts($meeting);
            if (count($meeting->activityItems) > 0 || count($meeting->userAttendees) > 0)
            {
                $attendeesContent = null;
                $contactLabels = self::getExistingContactRelationsLabels($meeting->activityItems);
                foreach ($contactLabels as $label)
                {
                    if ($attendeesContent != null)
                    {
                        $attendeesContent .= '<br/>';
                    }
                    $attendeesContent .= $label;
                }
                foreach ($meeting->userAttendees as $user)
                {
                    if ($attendeesContent != null)
                    {
                        $attendeesContent .= '<br/>';
                    }
                    $params             = array('label' => strval($user), 'redirectUrl' => null, 'wrapLabel' => false);
                    $moduleClassName    = $user->getModuleClassName();
                    $moduleId           = $moduleClassName::getDirectoryName();
                    $element            = new DetailsLinkActionElement('default', $moduleId, $user->id, $params);
                    $attendeesContent  .= '<i class="icon-'.strtolower(get_class($user)).'"></i> ' . $element->render();
                }
                if ($attendeesContent != null )
                {
                    $content .= $attendeesContent . '<br/>';
                }
            }
            $content = $title . $dateContent . ZurmoHtml::tag('div', array('class' => 'meeting-details'), $content);
            if ($meeting->location != null)
            {
                $content .=  ZurmoHtml::tag('strong', array(), Zurmo::t('ZurmoModule', 'Location')) . '<br/>';
                $content .= $meeting->location;
                $content .= '<br/>';
            }
            if ($meeting->description != null)
            {
                $content .= ZurmoHtml::tag('strong', array(), Zurmo::t('ZurmoModule', 'Description')) . '<br/>';
                $content .= $meeting->description;
            }
            return ZurmoHtml::tag('div', array('class' => 'meeting-summary'), $content);
        }

        protected static function getExistingContactRelationsLabels($activityItems)
        {
            $existingContacts = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
            foreach ($activityItems as $item)
            {
                try
                {
                    $contact = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $params             = array('label' => strval($contact), 'redirectUrl' => null, 'wrapLabel' => false);
                        $moduleClassName    = $contact->getModuleClassName();
                        $moduleId           = $moduleClassName::getDirectoryName();
                        $element            = new DetailsLinkActionElement('default', $moduleId, $contact->id, $params);
                        $existingContacts[] = '<i class="icon-'.strtolower(get_class($contact)).'"></i> ' . $element->render();
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return $existingContacts;
        }

        protected static function getNonExistingContactRelationsLabels($activityItems)
        {
            $existingContacts = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Contact');
            foreach ($activityItems as $item)
            {
                try
                {
                    $contact = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $params          = array('label' => strval($contact), 'redirectUrl' => null, 'wrapLabel' => false);
                        $moduleClassName = $contact->getModuleClassName();
                        $moduleId        = $moduleClassName::getDirectoryName();
                        $element          = new DetailsLinkActionElement('default', $moduleId, $contact->id, $params);
                        $existingContacts[] = '<i class="icon-'.strtolower(get_class($contact)).'"></i> ' . $element->render();
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return $existingContacts;
        }

        protected static function renderActivityItemsContentsExcludingContacts(Meeting $meeting)
        {
            $activityItemsModelClassNamesData = ActivitiesUtil::getActivityItemsModelClassNamesDataExcludingContacts();
            $content = null;
            foreach ($activityItemsModelClassNamesData as $relationModelClassName)
            {
                $activityItemForm = null;
                //ASSUMES ONLY A SINGLE ATTACHED ACTIVITYITEM PER RELATION TYPE.
                foreach ($meeting->activityItems as $item)
                {
                    try
                    {
                        $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
                        $castedDownModel           = $item->castDown(array($modelDerivationPathToItem));
                        if ($content != null)
                        {
                            $content .= '<br/> ';
                        }
                        $params          = array('label' => strval($castedDownModel), 'redirectUrl' => null, 'wrapLabel' => false);
                        $moduleClassName = $castedDownModel->getModuleClassName();
                        $moduleId        = $moduleClassName::getDirectoryName();
                        $element          = new DetailsLinkActionElement('default', $moduleId, $castedDownModel->id, $params);
                        //Render icon
                        $content .= '<i class="icon-'.strtolower(get_class($castedDownModel)).'"></i> ';
                        $content .= $element->render();
                        break;
                    }
                    catch (NotFoundException $e)
                    {
                    }
                }
            }
            if ($content != null)
            {
                $content .= '<br/>';
            }
            return $content;
        }

        /**
         * Gets full calendar item data.
         * @return string
         */
        public function getCalendarItemData()
        {
            $name             = $this->name;
            $location         = $this->location;
            $startDateTime    = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $this->startDateTime,
                                    DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                                    DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH,
                                    true);
            $endDateTime      = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $this->endDateTime,
                                    DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                                    DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH,
                                    true);
            $language         = Yii::app()->languageHelper->getForCurrentUser();
            $translatedAttributeLabels = self::translatedAttributeLabels($language);
            return array($translatedAttributeLabels['name']            => $name,
                         $translatedAttributeLabels['location']        => $location,
                         $translatedAttributeLabels['startDateTime']   => $startDateTime,
                         $translatedAttributeLabels['endDateTime']     => $endDateTime);
        }
    }
?>