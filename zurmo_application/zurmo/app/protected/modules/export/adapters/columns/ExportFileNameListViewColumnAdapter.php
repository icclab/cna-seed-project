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
     * Column adapter for status value for export list view.
     */
    class ExportFileNameListViewColumnAdapter extends TextListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            return array(
                    'name'  => $this->attribute,
                    'value' => 'ExportFileNameListViewColumnAdapter::renderFileNameCol($data)',
                    'type'  => 'raw',
            );
        }

        /**
         * Renders file name column.
         * @param ExportItem $data
         * @return string
         * @throws NotSupportedException
         */
        public static function renderFileNameCol($data)
        {
            $filename    = ZurmoHtml::tag('strong', array(), $data->exportFileName . '.' . $data->exportFileType);
            $owner       = ZurmoHtml::tag('a', array('class' => 'simple-link', 'href' => Yii::app()->createUrl('users/default/details',
                                                                                         array('id' => $data->owner->id))), $data->owner->getFullName());
            $content     = ZurmoHtml::tag('span', array('class' => 'exported-filename'), $filename . ' · ' . $owner);
            $content    .= self::renderStatus($data);
            $content    .= self::renderCancelButton($data);
            return $content;
        }

        /**
         * Renders status.
         * @param ExportItem $data
         * @return string
         * @throws NotSupportedException
         */
        protected static function renderStatus($data)
        {
            $status         = '<div class="continuum"><div class="clearfix">';
            $isCompleted    = (int)$data->isCompleted;
            $jobStatus      = (int)$data->isJobRunning;
            $dataProvider   = unserialize($data->serializedData);
            if ($isCompleted == 1)
            {
                $status .= '<div class="export-item-stage-status stage-true"><i>●</i><span>' . Zurmo::t('Core', 'Completed') . '</span></div>';
            }
            elseif ($isCompleted == 0 && $jobStatus == 1)
            {
                $status .= '<div class="export-item-stage-status stage-running"><i>●</i><span>' . Zurmo::t('ExportModule', 'Running') .
                    ' ' .  $data->processOffset . '/' . $dataProvider->getPagination()->getPageSize() . '</span></div>';
            }
            elseif ($isCompleted == 0 && $jobStatus == 0)
            {
                $status .= '<div class="export-item-stage-status stage-pending"><i>●</i><span>' . Zurmo::t('ExportModule', 'Pending')
                    . ' ' . $data->processOffset . '/' . $dataProvider->getPagination()->getPageSize() . '</span></div>';
            }
            else
            {
                throw new NotSupportedException();
            }
            $status         .= '</div></div>';
            return $status;
        }

        /**
         * Renders cancel button.
         * @param ExportItem $data
         * @return string
         * @throws NotSupportedException
         */
        public static function renderCancelButton($data)
        {
            $value = (int)$data->isJobRunning;
            $url   = Yii::app()->createUrl('export/default/cancel', array('id' => $data->id));
            if ($value == 0)
            {
                $cancelBtn  = ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Cancel')), $url, array('class' => 'secondary-button'));
                if ((int)$data->cancelExport == 0)
                {
                    return $cancelBtn;
                }
                else
                {
                    return ZurmoHtml::tag('span', array('class' => 'cancelled-export'), Zurmo::t('ExportModule', 'Cancelled'));
                }
            }
            elseif ($value == 1)
            {
                $cancelBtn  = ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Cancel')), $url, array('class' => 'secondary-button disabled'));
                return $cancelBtn;
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>