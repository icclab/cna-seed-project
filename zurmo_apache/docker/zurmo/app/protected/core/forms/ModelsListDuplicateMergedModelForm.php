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
     * Form used for handling the selected models with list view merge tool
     */
    class ModelsListDuplicateMergedModelForm extends CFormModel
    {
        /**
         * Selected models count for merge.
         */
        const MAX_SELECTED_MODELS_COUNT = 5;

        /**
         * Selected contacts
         *
         * @var array
         */
        public $selectedModels = array();

        /**
         * Primary contact for the merge
         * @var Contact
         */
        public $primaryModel;

        public function rules()
        {
            return array(
                array('selectedModels', 'validateModelsCount'),
                array('primaryModel', 'required'),
            );
        }

        /**
         * Validate the contacts which are selected.
         *
         * @param string $attribute
         * @param array $params
         */
        public function validateModelsCount($attribute, $params)
        {
            if (count($this->selectedModels) > self::MAX_SELECTED_MODELS_COUNT || count($this->selectedModels) == 0)
            {
                $message = Zurmo::t('ZurmoModule', 'Merge is limited to a maximum of  {count} records.',
                                     array('{count}' => self::MAX_SELECTED_MODELS_COUNT));
                Yii::app()->user->setFlash('notification', $message);
                $this->addError('selectedModels', $message);
            }
        }
    }
?>