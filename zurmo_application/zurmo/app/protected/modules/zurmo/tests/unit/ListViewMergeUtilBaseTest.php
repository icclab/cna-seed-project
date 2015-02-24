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

    abstract class ListViewMergeUtilBaseTest extends ZurmoBaseTest
    {
        public $selectedModels          = array();

        public $modelClass;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        protected function processSetPrimaryModelForListViewMerge()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->setFirstModel();
            $this->setSecondModel();
            $modelsList = ListViewMergeUtil::getSelectedModelsListForMerge($this->modelClass,
                                                        array('selectedIds' => $this->selectedModels[0]->id .
                                                                                ',' .   // Not Coding Standard
                                                                                $this->selectedModels[1]->id));
            $formModel  = new ModelsListDuplicateMergedModelForm();
            $formModel->selectedModels = $modelsList;
            ListViewMergeUtil::setPrimaryModelForListViewMerge($formModel,
                                                               array('primaryModelId' => $this->selectedModels[0]->id));
            $this->primaryModel = $formModel->primaryModel;
            $this->assertEquals($this->selectedModels[0]->id, $formModel->primaryModel->id);
        }

        protected function runProcessCopyRelationsAndDeleteNonPrimaryModelsInMerge()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->setSelectedModels();
            $this->setRelatedModels();
            ListViewMergeUtil::processCopyRelationsAndDeleteNonPrimaryModelsInMerge($this->getPrimaryModel(),
                                                                                    array('selectedIds' => $this->selectedModels[0]->id .
                                                                                            ',' .   // Not Coding Standard
                                                                                            $this->selectedModels[1]->id));
            $this->validatePrimaryModelData();
        }

        protected function getPrimaryModel()
        {
            return $this->selectedModels[0];
        }

        protected function validateActivityItem($activityModelClass, $activityName, $relationClassName, $relationFieldName, $relationFieldValue)
        {
            $this->checkActivityItemRelationCount($activityModelClass, $activityName, 1);
            $activities         = $activityModelClass::getByName($activityName);
            $relationClass      = $activities[0]->activityItems->offsetGet(0);
            $this->assertEquals(get_class($relationClass), $relationClassName);
            $this->assertEquals($relationFieldValue, $relationClass->$relationFieldName);
        }

        protected function checkActivityItemRelationCount($activityModelClass, $activityName, $count)
        {
            $activities   = $activityModelClass::getByName($activityName);
            $this->assertCount($count, $activities);
        }

        protected function addProject($relatedFieldName)
        {
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(0, count($primaryModel->projects));
            $project = ProjectTestHelper::createProjectByNameForOwner($this->modelClass . ' Project', Yii::app()->user->userModel);
            $project->$relatedFieldName->add($this->selectedModels[1]);
            assert($project->save()); // Not Coding Standard
        }

        protected function validateProject()
        {
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(1, count($primaryModel->projects));
            $project = $primaryModel->projects[0];
            $this->assertEquals($this->modelClass . ' Project', $project->name);
        }

        protected function addProduct($relatedFieldName)
        {
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(0, count($primaryModel->products));
            $product = ProductTestHelper::createProductByNameForOwner($this->modelClass . ' Product', Yii::app()->user->userModel);
            $product->$relatedFieldName = $this->selectedModels[1];
            $product->save();
        }

        protected function validateProduct()
        {
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(1, count($primaryModel->products));
            $product = $primaryModel->products[0];
            $this->assertEquals($this->modelClass . ' Product', $product->name);
        }

        protected function addMeeting()
        {
            $this->checkActivityItemRelationCount('Meeting', 'First Meeting', 0);
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('First Meeting', Yii::app()->user->userModel, $this->selectedModels[1]);
        }

        protected function validateMeeting($fieldName, $fieldValue)
        {
            $this->validateActivityItem('Meeting', 'First Meeting', $this->modelClass, $fieldName, $fieldValue);
        }

        protected function addNote()
        {
            $this->checkActivityItemRelationCount('Note', 'First Note', 0);
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('First Note', Yii::app()->user->userModel, $this->selectedModels[1]);
        }

        protected function validateNote($fieldName, $fieldValue)
        {
            $this->validateActivityItem('Note', 'First Note', $this->modelClass, $fieldName, $fieldValue);
        }

        protected function addTask()
        {
            $this->checkActivityItemRelationCount('Task', 'First Task', 0);
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('First Task', Yii::app()->user->userModel, $this->selectedModels[1]);
        }

        protected function validateTask($fieldName, $fieldValue)
        {
            $this->validateActivityItem('Task', 'First Task', $this->modelClass, $fieldName, $fieldValue);
        }

        protected function processResolveFormLayoutMetadataForOneColumnDisplay()
        {
            $modelClass       = $this->modelClass;
            $viewClassName    = $modelClass . 'sMergedEditAndDetailsView';
            $layoutMetadata   = ListViewMergeUtil::resolveFormLayoutMetadataForOneColumnDisplay($viewClassName::getMetadata());
            $rows             = $layoutMetadata['global']['panels'][0]['rows'];
            $modifiedElementsData = array();
            foreach ($rows as $row)
            {
                $modifiedElementsData[] = $row['cells'][0]['elements'][0];
            }
            if ($this->modelClass == 'Contact')
            {
                $this->assertEquals('title', $modifiedElementsData[0]['attributeName']);
                $this->assertEquals('DropDown', $modifiedElementsData[0]['type']);

                $this->assertEquals('firstName', $modifiedElementsData[1]['attributeName']);
                $this->assertEquals('Text', $modifiedElementsData[1]['type']);

                $this->assertEquals('lastName', $modifiedElementsData[2]['attributeName']);
                $this->assertEquals('Text', $modifiedElementsData[2]['type']);
            }
        }
    }
?>