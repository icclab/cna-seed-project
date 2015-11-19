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

    class FileModel extends Item
    {
        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'size',
                    'type',
                ),
                'relations' => array(
                    'fileContent' => array(static::HAS_ONE,  'FileContent', static::NOT_OWNED),
                ),
                'rules' => array(
                    array('fileContent', 'required'),
                    array('name',        'required'),
                    array('name',        'type',    'type' => 'string'),
                    array('name',        'length',  'min'  => 1, 'max' => 100),
                    array('size',        'required'),
                    array('size',        'type',    'type' => 'integer'),
                    array('type',        'required'),
                    array('type',        'type',    'type' => 'string'),
                    array('type',        'length',  'min'  => 1, 'max' => 128),
                    array('type',        'fileTypeValidator'),

                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'fileContent',
                ),
            );
            return $metadata;
        }

        public function fileTypeValidator($attribute, $params)
        {
            return true;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'name' => Zurmo::t('Core', 'Name', array(), null, $language),
                    'size' => Zurmo::t('Core', 'Size',  array(), null, $language),
                    'type' => Zurmo::t('Core', 'Type',  array(), null, $language),
                )
            );
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if ($this->fileContent !== null)
                {
                    return $this->fileContent->save();
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        protected function deleteRelatedFileContentIfNotRelatedToAnyOtherFileModel()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'    => 'fileContent',
                    'relatedModelData' => array(
                        'attributeName' => 'id',
                        'operatorType'  => 'equals',
                        'value'         => $this->fileContent->id,
                    )
                ),
            );
            $searchAttributeData['structure'] = '1';
            $class = get_class($this);
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($class);
            $where             = RedBeanModelDataProvider::makeWhere($class, $searchAttributeData, $joinTablesAdapter);
            if (static::getCount($joinTablesAdapter, $where, $class) == 1)
            {
                return $this->fileContent->delete();
            }
            return true;
        }

        protected function deleteOwnedRelatedModels($modelClassName)
        {
            // THIS IS A HACK. We want to save space by not duplicating fileContent's blob so we have to live with this.

            // This is to fix the dangling FileContent that remain there when deleting a model that owns files
            // Example: ModelWithAttachmentTest.testModelWithAttachmentTestItem

            // we use $this->deleteOwnedRelatedModels so when deleting FileModel directly this gets
            // invoked anyway under unrestrictedDelete, no need to call same function in beforeDelete of FileModel

            // When deleting a model that owns Files, this gets invoked as a result of RedbeanModel.2245
            // We can't change RedBeanModel.2238 and RedBeanModel.2245 to delete() because that would
            // throw exception, by that point we are deleting an OwnedModel instance which can't be deleted
            // from outside and hence this fix.
            if (get_class($this) == $modelClassName)
            {
                // get rid of fileContent that belong only to this model before going ahead and trashing it.
                if (!$this->deleteRelatedFileContentIfNotRelatedToAnyOtherFileModel())
                {
                    throw new FailedToDeleteModelException("Unable to delete related FileContent");
                }
            }
            parent::deleteOwnedRelatedModels($modelClassName);
        }
    }
?>