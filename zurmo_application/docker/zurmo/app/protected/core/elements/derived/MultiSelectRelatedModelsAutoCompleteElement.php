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

    abstract class MultiSelectRelatedModelsAutoCompleteElement extends Element implements DerivedElementInterface
    {
        /**
         * @return string
         */
        abstract protected function getFormName();

        /**
         * @return string
         */
        abstract protected function getUnqualifiedNameForIdField();

        /**
         * @return string
         */
        abstract protected function getUnqualifiedIdForIdField();

        /**
         * Asserts that element is attached to a form with a model type that we are indeed expecting
         */
        abstract protected function assertModelType();

        /**
         * Returns the source url widget should hit to request data for autocomplete
         * @return mixed
         */
        abstract protected function getWidgetSourceUrl();

        /**
         * Returns the relation name we would query when generated existing Ids and Labels
         * @return string
         */
        abstract protected function getRelationName();

        /**
         * Returns the hint text display in widget
         * @return string
         */
        abstract protected function getWidgetHintText();

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        protected function getEditableInputId($attributeName = null, $relationAttributeName = null)
        {
            return $this->getFormName() . $this->getUnqualifiedIdForIdField();
        }

        protected function getEditableInputName($attributeName = null, $relationAttributeName = null)
        {
            return $this->getFormName() . $this->getUnqualifiedNameForIdField();
        }

        /**
         * Returns rendered content for display as nonEditable.
         * @return null|string
         */
        protected function renderControlNonEditable()
        {
            $content            = null;
            $existingRecords    = $this->getExistingIdsAndLabels();
            foreach ($existingRecords as $existingRecord)
            {
                if ($content != null)
                {
                    $content .= ', ';
                }
                $content .= $existingRecord[$this->getWidgetPropertyToSearch()];
            }
            return $content;
        }

        /**
         * Returns the name for the widget clip
         * @return string
         */
        protected function getClipName()
        {
            return get_class($this);
        }

        /**
         * Return path alias to the multiselect autocomplete widget
         * @return string
         */
        protected function getWidgetPathAlias()
        {
            return 'application.core.widgets.MultiSelectAutoComplete';
        }

        /**
         * Returns widget options. To set default options in class, override this method
         * @return array
         */
        protected function getWidgetOptions()
        {
            return array();
        }

        /**
         * Returns an array with 'disabled' populated according to element parameters
         * @return array
         */
        protected function getWidgetDefaultHtmlOptions()
        {
            return array('disabled' => $this->getDisabledValue());
        }

        /**
         * Returns json encoded string of the existing Ids and Labels bound to model
         * @return string
         */
        protected function getJsonEncodedIdsAndLabels()
        {
            return CJSON::encode($this->getExistingIdsAndLabels());
        }

        /**
         * Returns the property widget should search for in response.
         * @return string
         */
        protected function getWidgetPropertyToSearch()
        {
            return 'name';
        }

        /**
         * Returns default widget options. Do not override this method,
         * override the methods it calls to collect data, or override getWidgetOptions()
         * @return array
         */
        protected final function getDefaultWidgetOptions()
        {
            return array(
                'hintText'                  => $this->getWidgetHintText(),
                'htmlOptions'               => $this->getHtmlOptions(),
                'id'                        => $this->getEditableInputId(),
                'jsonEncodedIdsAndLabels'   => $this->getJsonEncodedIdsAndLabels(),
                'name'                      => $this->getEditableInputName(),
                'onAdd'                     => $this->getOnAddContent(),
                'onDelete'                  => $this->getOnDeleteContent(),
                'sourceUrl'                 => $this->getWidgetSourceUrl(),
                'propertyToSearch'          => $this->getWidgetPropertyToSearch(),
            );
        }

        /**
         * Returns the rendered content for editable type
         * @return mixed
         */
        protected function renderControlEditable()
        {
            $this->assertModelType();
            $cClipWidget = new CClipWidget();
            $clipName   = $this->getClipName();
            $widgetPath = $this->getWidgetPathAlias();
            $defaultWidgetOptions = $this->getDefaultWidgetOptions();
            $customWidgetOptions = $this->getWidgetOptions();
            $widgetOptions = CMap::mergeArray($defaultWidgetOptions, $customWidgetOptions);
            $cClipWidget->beginClip($clipName);
            $cClipWidget->widget($widgetPath, $widgetOptions);
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips[$clipName];
            return $content;
        }

        /**
         * Returns the js event handler to fire whenever a new item gets added
         * @return null
         */
        protected function getOnAddContent()
        {
            return null;
        }

        /**
         * Returns the js event handler to fire whenever an item gets deleted.
         * @return null
         */
        protected function getOnDeleteContent()
        {
            return null;
        }

        /**
         * Renders Error
         * @return string|null
         */
        protected function renderError()
        {
            return null;
        }

        /**
         * Returns label
         * @return string
         */
        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        /**
         * Returns formatted display name
         * @return string
         */
        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text($this->getDisplayName());
        }

        /**
         * Returns the default ids and labels that should already be there, probably readonly too.
         * @return array
         */
        protected function getDefaultExistingIdsAndLabel()
        {
            return array();
        }

        /**
         * Resolve an array with id and name using the sent model
         * @param $model
         * @return array
         */
        protected function resolveIdAndNameByModel(RedBeanModel $model)
        {
            return array(
                'id'    => $model->id,
                $this->getWidgetPropertyToSearch()  => $this->resolveModelNameForRendering($model),
            );
        }

        /**
         * Resolves model's name for rendering.
         * @param RedBeanModel $model
         * @return string
         */
        protected function resolveModelNameForRendering(RedBeanModel $model)
        {
            return strval($model);
        }

        /**
         * Computes Related records for current model using relationName, returns an array
         * @return array
         */
        protected function getRelatedRecords()
        {
            $relation           = $this->getRelationName();
            $relatedRecords     = $this->model->$relation;
            return $relatedRecords;
        }

        /**
         * Returns an array with the Ids and Labels of records already bound to the model attached to element
         * @return array
         */
        protected function getExistingIdsAndLabels()
        {
            $relatedRecords     = $this->getRelatedRecords();
            $existingRecords    = array();
            $default            = $this->getDefaultExistingIdsAndLabel();
            if (!empty($default))
            {
                $existingRecords[]  = $default;
            }
            foreach ($relatedRecords as $relatedRecord)
            {
                $existingRecord = $this->resolveIdAndNameByModel($relatedRecord);
                if (!empty($existingRecord))
                {
                    $existingRecords[]  = $existingRecord;
                }
            }
            return $existingRecords;
        }
    }
?>