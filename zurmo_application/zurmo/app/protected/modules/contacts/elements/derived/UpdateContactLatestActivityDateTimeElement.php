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
     * Class to help manage how the latest activity date time is updated for contacts.
     */
    class UpdateContactLatestActivityDateTimeElement extends Element
    {
        /**
         * Render a checkbox (with a default value if not checked)
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            assert('$this->attribute == "null"');
            $content  = Zurmo::t('ZurmoModule', 'Update When');
            $content .= $this->renderEditableByAttribute('updateLatestActivityDateTimeWhenATaskIsCompleted');
            $content .= $this->renderEditableByAttribute('updateLatestActivityDateTimeWhenANoteIsCreated');
            $content .= $this->renderEditableByAttribute('updateLatestActivityDateTimeWhenAnEmailIsSentOrArchived');
            $content .= $this->renderEditableByAttribute('updateLatestActivityDateTimeWhenAMeetingIsInThePast');
            return $content;
        }

        protected function renderLabel()
        {
            return Yii::app()->format->text(Contact::getAnAttributeLabel('latestActivityDateTime'));
        }

        protected function renderError()
        {
            $content  = $this->renderErrorByAttribute('updateLatestActivityDateTimeWhenATaskIsCompleted');
            $content .= $this->renderErrorByAttribute('updateLatestActivityDateTimeWhenANoteIsCreated');
            $content .= $this->renderErrorByAttribute('updateLatestActivityDateTimeWhenAnEmailIsSentOrArchived');
            $content .= $this->renderErrorByAttribute('updateLatestActivityDateTimeWhenAMeetingIsInThePast');
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderEditableByAttribute($attribute)
        {
            $content  = ZurmoHtml::openTag('div', array('class' => 'multi-select-checkbox-input'));
            $content .= $this->renderEditableCheckBoxByAttribute($attribute);
            $content .= $this->form->labelEx($this->model, $attribute, array('for' => $this->getEditableInputId($attribute)));
            $content .= ZurmoHtml::closeTag('div');
            return $content;
        }

        protected function renderEditableCheckBoxByAttribute($attribute)
        {
            $htmlOptions['uncheckValue'] = false;
            if ($this->model->{$attribute} === true)
            {
                $htmlOptions['checked'] = 'checked';
            }
            $element = $this->form->checkBox($this->model, $attribute, $htmlOptions);
            return $element;
        }

        protected function renderErrorByAttribute($attribute)
        {
            return $this->form->error($this->model, $attribute,
                    array('inputID' => $this->getEditableInputId($attribute)));
        }

        protected function getIdForInputField($attribute, $suffix)
        {
            return $this->resolveInputIdPrefix() . '_' . $attribute . '_'. $suffix;
        }

        protected function getNameForInputField($attribute, $suffix)
        {
            return $this->resolveInputNamePrefix() . '[' . $attribute . '][' . $suffix . ']';
        }
    }
?>
