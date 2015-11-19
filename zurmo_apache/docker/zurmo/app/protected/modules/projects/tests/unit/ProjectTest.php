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

    class ProjectTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

//        public function testDemoDataMaker()
//        {
//            $demoDataHelper = new DemoDataHelper();
//            $demoDataHelper->setRangeByModelName('User', 1, 10);
//            $projectDemoDataMaker = new ProjectsDemoDataMaker();
//            $projectDemoDataMaker->makeAll($demoDataHelper);
//        }

        public function testCreateAndGetProjectById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user                       = Yii::app()->user->userModel;

            $project                  = new Project();
            $project->name            = 'Project 1';
            $project->owner           = $user;
            $project->description     = 'Description';

            $user = UserTestHelper::createBasicUser('Steven');
            $account = new Account();
            $account->owner       = $user;
            $account->name        = DataUtil::purifyHtml("Tom & Jerry's Account");
            $this->assertTrue($account->save());
            $id = $account->id;
            unset($account);
            $account = Account::getById($id);
            $this->assertEquals("Tom & Jerry's Account", $account->name);
            //$project->accounts->add($account);
            $contact = ContactTestHelper::createContactByNameForOwner('Jerry', $user);
            //$project->contacts->add($contact);
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('Jerry Opp', $user);
            //$project->opportunities->add($opportunity);
            $this->assertTrue($project->save());
            $this->assertEquals(1, count($project->auditEvents));
            $id                       = $project->id;
            $project->forget();
            unset($project);
            $project                  = Project::getById($id);
            ProjectZurmoControllerUtil::resolveProjectManyManyAccountsFromPost($project, array('accountIds' => $account->id));
            ProjectZurmoControllerUtil::resolveProjectManyManyContactsFromPost($project, array('contactIds' => $contact->id));
            ProjectZurmoControllerUtil::resolveProjectManyManyOpportunitiesFromPost($project, array('opportunityIds' => $opportunity->id));
            $this->assertEquals('Project 1', $project->name);
            $this->assertEquals('Description', $project->description);
            $this->assertEquals(1, $project->accounts->count());
            $this->assertEquals(1, $project->contacts->count());
            $this->assertEquals(1, $project->opportunities->count());
            //Try saving a second project
            $project                  = new Project();
            $project->name            = 'Project 2';
            $project->owner           = $user;
            $project->description     = 'Description';
            $this->assertTrue($project->save());
            $this->assertEquals(1, count($project->auditEvents));
        }

        /**
         * @depends testCreateAndGetProjectById
         */
        public function testGetProjectsByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $projects                   = Project::getByName('Project 1');
            $this->assertEquals(1, count($projects));
            $this->assertEquals('Project 1', $projects[0]->name);
        }

        /**
         * @depends testCreateAndGetProjectById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $projects                   = Project::getByName('Project 1');
            $this->assertEquals(1, count($projects));
            $this->assertEquals('Project',  $projects[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Projects', $projects[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetProjectsByName
         */
        public function testGetProjectByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $projects                   = Project::getByName('Red Widget 1');
            $this->assertEquals(0, count($projects));
        }

        /**
         * @depends testCreateAndGetProjectById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $projects                   = Project::getAll();
            $this->assertEquals(2, count($projects));
        }

        public function testDeleteProject()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $projects                   = Project::getAll();
            $this->assertEquals(2, count($projects));
            $projects[1]->delete();
        }

        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $projects                   = Project::getAll();
            $this->assertEquals(1, count($projects));
        }

        public function testProjectSaveWithPermissions()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user             = Yii::app()->user->userModel;
            $everyoneGroup    = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $postData         = array(
                                        'name' => 'ProjectPermissionTest',
                                        'description' => 'Test Description',
                                        'owner' => array('id' => $user->id),
                                        'explicitReadWriteModelPermissions' => array(
                                                'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP,
                                                'nonEveryoneGroup' => ''
                                            )

                                    );
            $model                  = new Project();
            $sanitizedPostData      = PostUtil::sanitizePostByDesignerTypeForSavingModel($model, $postData);
            if ($model instanceof SecurableItem)
            {
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::resolveByPostDataAndModelThenMake($sanitizedPostData, $model);
            }
            else
            {
                $explicitReadWriteModelPermissions = null;
            }

            $readyToUseData                = ExplicitReadWriteModelPermissionsUtil::
                                                 removeIfExistsFromPostData($sanitizedPostData);

            $sanitizedOwnerData            = PostUtil::sanitizePostDataToJustHavingElementForSavingModel(
                                                 $readyToUseData, 'owner');
            $sanitizedDataWithoutOwner     = PostUtil::
                                                 removeElementFromPostDataForSavingModel($readyToUseData, 'owner');
            $model->setAttributes($sanitizedDataWithoutOwner);
            if ($model->validate())
            {
                $modelToStringValue = strval($model);
                if ($sanitizedOwnerData != null)
                {
                    $model->setAttributes($sanitizedOwnerData);
                }
                if ($model instanceof OwnedSecurableItem)
                {
                    $passedOwnerValidation = $model->validate(array('owner'));
                }
                else
                {
                    $passedOwnerValidation = true;
                }
                if ($passedOwnerValidation && $model->save(false))
                {
                    if ($explicitReadWriteModelPermissions != null)
                    {
                        $success = ExplicitReadWriteModelPermissionsUtil::
                                    resolveExplicitReadWriteModelPermissions($model, $explicitReadWriteModelPermissions);
                        //todo: handle if success is false, means adding/removing permissions save failed.
                    }
                    $savedSuccessfully = true;
                }
            }
            else
            {
            }
            $this->assertEquals('ProjectPermissionTest', $model->name);
        }
    }
?>