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
    * Designer Module Walkthrough of Projects.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation search, edit and delete of the Project based on the custom fields.
    */
    class ProjectsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();

            //Create a Project for testing.
            ProjectTestHelper::createProjectByNameForOwner('superProject', $super);

            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testSuperUserProjectDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Project Modules Menu.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for Project module.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Project module.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Project',  ProjectsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Projects', ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('project',  ProjectsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('projects', ProjectsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserProjectDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('ProjectsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('ProjectsModule', 'currency');
            $this->createDateCustomFieldByModule                ('ProjectsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('ProjectsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('ProjectsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('ProjectsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('ProjectsModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('ProjectsModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('ProjectsModule', 'citylist');
            $this->createIntegerCustomFieldByModule             ('ProjectsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('ProjectsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('ProjectsModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('ProjectsModule', 'calcnumber');
            $this->createDropDownDependencyCustomFieldByModule  ('ProjectsModule', 'dropdowndep');
            $this->createPhoneCustomFieldByModule               ('ProjectsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('ProjectsModule', 'radio');
            $this->createTextCustomFieldByModule                ('ProjectsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('ProjectsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('ProjectsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForProjectsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to ProjectEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectEditAndDetailsView'));
            $layout = ProjectsDesignerWalkthroughHelperUtil::getProjectEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);

            //Add all fields to ProjectsSearchView.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsSearchView'));
            $layout = ProjectsDesignerWalkthroughHelperUtil::getProjectsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);

            //Add all fields to ProjectsListView.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsListView'));
            $layout = ProjectsDesignerWalkthroughHelperUtil::getProjectsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);

            //Add all fields to ProjectsRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'ProjectsModule',
                                     'viewClassName'   => 'ProjectsRelatedListView'));
            $layout = ProjectsDesignerWalkthroughHelperUtil::getProjectsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForProjectsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superProjectId = self::getModelIdByModelNameAndName ('Project', 'superProject');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/create');
            $this->setGetArray(array('id' => $superProjectId));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForProjectsModule
         */
        public function testCreateAProjectAfterTheCustomFieldsArePlacedForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormatForInput(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormatForInput(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            //Retrieve the account id and the super account id.
            $superUserId = $super->id;

            //Create a new project based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Project' => array(
                            'name'                              => 'myNewProject',
                            'owner'                             => array('id' => $superUserId),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'checkboxCstm'                      => '1',
                            'currencyCstm'                      => array('value'    => 45,
                                                                         'currency' => array('id' => $baseCurrency->id)),
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'decimalCstm'                       => '123',
                            'picklistCstm'                      => array('value' => 'a'),
                            'multiselectCstm'                   => array('values' => array('ff', 'rr')),
                            'tagcloudCstm'                      => array('values' => array('writing', 'gardening')),
                            'countrylistCstm'                   => array('value'  => 'bbbb'),
                            'statelistCstm'                     => array('value'  => 'bbb1'),
                            'citylistCstm'                      => array('value'  => 'bb1'),
                            'integerCstm'                       => '12',
                            'phoneCstm'                         => '259-784-2169',
                            'radioCstm'                         => array('value' => 'd'),
                            'textCstm'                          => 'This is a test Text',
                            'textareaCstm'                      => 'This is a test TextArea',
                            'urlCstm'                           => 'http://wwww.abc.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('projects/default/create');

            //Check the details if they are saved properly for the custom fields.
            $projectId = self::getModelIdByModelNameAndName('Project', 'myNewProject');
            $project   = Project::getById($projectId);

            //Retrieve the permission of the project.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($project);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($project->name                       , 'myNewProject');
            $this->assertEquals($project->owner->id                  , $superUserId);
            $this->assertEquals(1                                    , count($readWritePermitables));
            $this->assertEquals(0                                    , count($readOnlyPermitables));
            $this->assertEquals($project->checkboxCstm               , '1');
            $this->assertEquals($project->currencyCstm->value        , 45);
            $this->assertEquals($project->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($project->dateCstm                   , $dateAssert);
            $this->assertEquals($project->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($project->decimalCstm                , '123');
            $this->assertEquals($project->picklistCstm->value        , 'a');
            $this->assertEquals($project->integerCstm                , 12);
            $this->assertEquals($project->phoneCstm                  , '259-784-2169');
            $this->assertEquals($project->radioCstm->value           , 'd');
            $this->assertEquals($project->textCstm                   , 'This is a test Text');
            $this->assertEquals($project->textareaCstm               , 'This is a test TextArea');
            $this->assertEquals($project->urlCstm                    , 'http://wwww.abc.com');
            $this->assertEquals($project->countrylistCstm->value     , 'bbbb');
            $this->assertEquals($project->statelistCstm->value       , 'bbb1');
            $this->assertEquals($project->citylistCstm->value        , 'bb1');
            $this->assertContains('ff'                                   , $project->multiselectCstm->values);
            $this->assertContains('rr'                                   , $project->multiselectCstm->values);
            $this->assertContains('writing'                              , $project->tagcloudCstm->values);
            $this->assertContains('gardening'                            , $project->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Project');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat($metadata->getFormula(), $project);
            $this->assertEquals(1476                                     , intval(str_replace(',', '', $testCalculatedValue))); // Not Coding Standard
        }

        /**
         * @depends testCreateAProjectAfterTheCustomFieldsArePlacedForProjectsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProjectsModuleAfterCreatingTheProject()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the account id and the super user id.
            $superUserId    = $super->id;

            //Search a created project using the customfield.
            $this->resetPostArray();
            $this->setGetArray(array('ProjectsSearchForm' => array(
                                                'name'               => 'myNewProject',
                                                'owner'              => array('id' => $superUserId),
                                                'decimalCstm'        => '123',
                                                'integerCstm'        => '12',
                                                'phoneCstm'          => '259-784-2169',
                                                'textCstm'           => 'This is a test Text',
                                                'textareaCstm'       => 'This is a test TextArea',
                                                'urlCstm'            => 'http://wwww.abc.com',
                                                'checkboxCstm'       => array('value'  =>  '1'),
                                                'currencyCstm'       => array('value'  =>  45),
                                                'picklistCstm'       => array('value'  =>  'a'),
                                                'multiselectCstm'    => array('values' => array('ff', 'rr')),
                                                'tagcloudCstm'       => array('values' => array('writing', 'gardening')),
                                                'countrylistCstm'    => array('value'  => 'bbbb'),
                                                'statelistCstm'      => array('value'  => 'bbb1'),
                                                'citylistCstm'       => array('value'  => 'bb1'),
                                                'radioCstm'          => array('value'  =>  'd'),
                                                'dateCstm__Date'     => array('type'   =>  'Today'),
                                                'datetimeCstm__DateTime' => array('type'   =>  'Today')),
                                                'ajax' =>  'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');
            $this->assertContains("myNewProject", $content);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProjectsModuleAfterCreatingTheProject
         */
        public function testEditOfTheProjectForTheTagCloudFieldAfterRemovingAllTagsPlacedForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormatForInput(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormatForInput(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id, the super user id and project Id.
            $superUserId                      = $super->id;
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            $project                          = Project::getByName('myNewProject');
            $projectId                        = $project[0]->id;
            $this->assertEquals(2, $project[0]->tagcloudCstm->values->count());

            //Edit a new Project based on the custom fields.
            $this->setGetArray(array('id' => $projectId));
            $this->setPostArray(array('Project' => array(
                            'name'                              => 'myEditProject',
                            'owner'                             => array('id' => $superUserId),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'checkboxCstm'                      => '0',
                            'currencyCstm'                      => array('value'       => 40,
                                                                         'currency'    => array(
                                                                             'id' => $baseCurrency->id)),
                            'decimalCstm'                       => '12',
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'picklistCstm'                      => array('value'  => 'b'),
                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
                            'tagcloudCstm'                      => array('values' =>  array()),
                            'countrylistCstm'                   => array('value'  => 'aaaa'),
                            'statelistCstm'                     => array('value'  => 'aaa1'),
                            'citylistCstm'                      => array('value'  => 'ab1'),
                            'integerCstm'                       => '11',
                            'phoneCstm'                         => '259-784-2069',
                            'radioCstm'                         => array('value' => 'e'),
                            'textCstm'                          => 'This is a test Edit Text',
                            'textareaCstm'                      => 'This is a test Edit TextArea',
                            'urlCstm'                           => 'http://wwww.abc-edit.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('projects/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $projectId = self::getModelIdByModelNameAndName('Project', 'myEditProject');
            $project   = Project::getById($projectId);

            //Retrieve the permission of the project.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($project);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($project->name                       , 'myEditProject');
            $this->assertEquals($project->owner->id                  , $superUserId);
            $this->assertEquals(1                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($project->checkboxCstm               , '0');
            $this->assertEquals($project->currencyCstm->value        , 40);
            $this->assertEquals($project->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($project->dateCstm                   , $dateAssert);
            $this->assertEquals($project->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($project->decimalCstm                , '12');
            $this->assertEquals($project->picklistCstm->value        , 'b');
            $this->assertEquals($project->integerCstm                , 11);
            $this->assertEquals($project->phoneCstm                  , '259-784-2069');
            $this->assertEquals($project->radioCstm->value           , 'e');
            $this->assertEquals($project->textCstm                   , 'This is a test Edit Text');
            $this->assertEquals($project->textareaCstm               , 'This is a test Edit TextArea');
            $this->assertEquals($project->urlCstm                    , 'http://wwww.abc-edit.com');
            $this->assertEquals($project->dateCstm                   , $dateAssert);
            $this->assertEquals($project->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($project->countrylistCstm->value     , 'aaaa');
            $this->assertEquals($project->statelistCstm->value       , 'aaa1');
            $this->assertEquals($project->citylistCstm->value        , 'ab1');
            $this->assertContains('gg'                                   , $project->multiselectCstm->values);
            $this->assertContains('hh'                                   , $project->multiselectCstm->values);
            $this->assertEquals(0                                        , $project->tagcloudCstm->values->count());
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Project');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat($metadata->getFormula(), $project);
            $this->assertEquals(132                                      , intval(str_replace(',', '', $testCalculatedValue))); // Not Coding Standard
        }

        /**
         * @depends testEditOfTheProjectForTheTagCloudFieldAfterRemovingAllTagsPlacedForProjectsModule
         */
        public function testEditOfTheProjectForTheCustomFieldsPlacedForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormatForInput(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormatForInput(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id, the super user id and project Id.
            $superUserId                      = $super->id;
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
            $project                          = Project::getByName('myEditProject');
            $projectId                        = $project[0]->id;

            //Edit a new Project based on the custom fields.
            $this->setGetArray(array('id' => $projectId));
            $this->setPostArray(array('Project' => array(
                            'name'                              => 'myEditProject',
                            'owner'                             => array('id' => $superUserId),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'checkboxCstm'                      => '0',
                            'currencyCstm'                      => array('value'   => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'decimalCstm'                       => '12',
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'picklistCstm'                      => array('value'  => 'b'),
                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
                            'tagcloudCstm'                      => array('values' =>  array('reading', 'surfing')),
                            'countrylistCstm'                   => array('value'  => 'aaaa'),
                            'statelistCstm'                     => array('value'  => 'aaa1'),
                            'citylistCstm'                      => array('value'  => 'ab1'),
                            'integerCstm'                       => '11',
                            'phoneCstm'                         => '259-784-2069',
                            'radioCstm'                         => array('value' => 'e'),
                            'textCstm'                          => 'This is a test Edit Text',
                            'textareaCstm'                      => 'This is a test Edit TextArea',
                            'urlCstm'                           => 'http://wwww.abc-edit.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('projects/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $projectId = self::getModelIdByModelNameAndName('Project', 'myEditProject');
            $project   = Project::getById($projectId);

            //Retrieve the permission of the project.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($project);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($project->name                       , 'myEditProject');
            $this->assertEquals($project->owner->id                  , $superUserId);
            $this->assertEquals(1                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($project->checkboxCstm               , '0');
            $this->assertEquals($project->currencyCstm->value        , 40);
            $this->assertEquals($project->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($project->dateCstm                   , $dateAssert);
            $this->assertEquals($project->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($project->decimalCstm                , '12');
            $this->assertEquals($project->picklistCstm->value        , 'b');
            $this->assertEquals($project->integerCstm                , 11);
            $this->assertEquals($project->phoneCstm                  , '259-784-2069');
            $this->assertEquals($project->radioCstm->value           , 'e');
            $this->assertEquals($project->textCstm                   , 'This is a test Edit Text');
            $this->assertEquals($project->textareaCstm               , 'This is a test Edit TextArea');
            $this->assertEquals($project->urlCstm                    , 'http://wwww.abc-edit.com');
            $this->assertEquals($project->dateCstm                   , $dateAssert);
            $this->assertEquals($project->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($project->countrylistCstm->value     , 'aaaa');
            $this->assertEquals($project->statelistCstm->value       , 'aaa1');
            $this->assertEquals($project->citylistCstm->value        , 'ab1');
            $this->assertContains('gg'                                   , $project->multiselectCstm->values);
            $this->assertContains('hh'                                   , $project->multiselectCstm->values);
            $this->assertContains('reading'                              , $project->tagcloudCstm->values);
            $this->assertContains('surfing'                              , $project->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Project');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat($metadata->getFormula(), $project);
            $this->assertEquals(132                                      , intval(str_replace(',', '', $testCalculatedValue))); // Not Coding Standard
        }

        /**
         * @depends testEditOfTheProjectForTheCustomFieldsPlacedForProjectsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProjectsModuleAfterEditingTheProject()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the account id, the super user id and project Id.
            $superUserId    = $super->id;

            //Search a created Project using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'ProjectsSearchForm' =>
                            ProjectsDesignerWalkthroughHelperUtil::fetchProjectsSearchFormGetData($superUserId),
                        'ajax'                    =>  'list-view')
            );
            //TODO Need to ask Jason
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');
            $this->assertContains("myEditProject", $content);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProjectsModuleAfterEditingTheProject
         */
        public function testDeleteOfTheProjectUserForTheCustomFieldsPlacedForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Get the project id from the recently edited project.
            $projectId = self::getModelIdByModelNameAndName('Project', 'myEditProject');

            //Set the project id so as to delete the project.
            $this->setGetArray(array('id' => $projectId));
            $this->runControllerWithRedirectExceptionAndGetUrl('projects/default/delete');

            //Check wether the project is deleted.
            $project = Project::getByName('myEditProject');
            $this->assertEquals(0, count($project));
        }

        /**
         * @depends testDeleteOfTheProjectUserForTheCustomFieldsPlacedForProjectsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProjectsModuleAfterDeletingTheProject()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the account id, the super user id and project Id.
            $superUserId    = $super->id;

            //Search a created Project using the customfields.
            $this->resetGetArray();
            $this->setGetArray(array(
                        'ProjectsSearchForm' =>
                            ProjectsDesignerWalkthroughHelperUtil::fetchProjectsSearchFormGetData($superUserId),
                        'ajax'                    =>  'list-view')
            );
            //TODO Need to ask Jason
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default');

            //Assert that the edit Project does not exits after the search.
            $this->assertContains("No results found", $content);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProjectsModuleAfterDeletingTheProject
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForProjectsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'rea'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertContains("reading", $content);
        }

        /**
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForProjectsModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForProjectsModule()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $this->assertEquals('en', $languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));

            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'surf'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertContains("surfing fr", $content);
        }
    }
?>