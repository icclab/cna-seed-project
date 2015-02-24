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
     * Task module walkthrough tests.
     */
    class TaskCheckItemsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSaveChecklistItemForTaskUsingAjax()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $task = new Task();
            $task->name = 'aTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            //add related task for account using createFromRelation action
            $checkListItemPostData = array('TaskCheckListItem' => array('name' => 'Test Item'));
            $this->setPostArray($checkListItemPostData);

            //Test just going to the create from relation view.
            $redirectUrl = null;
            $this->setGetArray(array('relatedModelId' => $task->id, 'relatedModelClassName' => 'Task',
                                        'relatedModelRelationName' => 'checkListItems', 'redirectUrl' => $redirectUrl));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/taskCheckItems/inlineCreateTaskCheckItemSave', true);
        }

        public function testCreateChecklistItemViewForTaskUsingAjax()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $task = new Task();
            $task->name = 'aTest1';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $this->setGetArray(array('id' => $task->id, 'uniquePageId' => 'TaskCheckItemInlineEditForModelView'));

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/taskCheckItems/inlineCreateTaskCheckItemFromAjax', false);
            $this->assertContains('TaskCheckListItem_name', $content);
        }

        /**
         * @depends testSaveChecklistItemForTaskUsingAjax
         */
        public function testAjaxCheckItemListForRelatedTaskModel()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $tasks  = Task::getByName('aTest');
            $task   = $tasks[0];
            $taskId = $task->id;

            $this->setGetArray(array('relatedModelId' => $task->id, 'relatedModelClassName' => 'Task',
                                        'relatedModelRelationName' => 'checkListItems', 'uniquePageId' => null));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/taskCheckItems/ajaxCheckItemListForRelatedTaskModel');
            $this->assertContains('TaskCheckListItemsForTaskView', $content);
        }

        /**
         * @depends testSaveChecklistItemForTaskUsingAjax
         */
        public function testUpdateStatusViaAjax()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $tasks  = Task::getByName('aTest');
            $task   = $tasks[0];
            $taskId = $task->id;

            $checkListItems = $task->checkListItems;
            $this->setGetArray(array('id' => $checkListItems[0]->id, 'taskId' => $taskId, 'checkListItemCompleted' => '1'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/taskCheckItems/updateStatusViaAjax', true);
            $taskCheckListItem = TaskCheckListItem::getById(intval($checkListItems[0]->id));
            $this->assertTrue((bool)$taskCheckListItem->completed);

            $this->setGetArray(array('id' => $checkListItems[0]->id, 'taskId' => $taskId, 'checkListItemCompleted' => '0'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/taskCheckItems/updateStatusViaAjax', true);
            $taskCheckListItem = TaskCheckListItem::getById(intval($checkListItems[0]->id));
            $this->assertFalse((bool)$taskCheckListItem->completed);
        }
   }
?>