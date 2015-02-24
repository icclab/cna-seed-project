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

    class ZurmoNestedGroupPermissionsFlushWalkThroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function setup()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testArePermissionsFlushedOnDeletingParentGroup()
        {
            // we could have used helpers to do a lot of the following stuff (such as creating users, groups,
            // etc) but we wanted to mimic user's interaction as closely as possible. Hence using walkthroughs
            // for everything

            // create Parent and Child Groups, Create Jim to be member of Child group

            // create parent group
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => 'Parent',
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $parentGroup    = Group::getByName('Parent');
            $this->assertNotNull($parentGroup);
            $this->assertEquals('Parent', strval($parentGroup));
            $parentGroupId  = $parentGroup->id;

            // create child group
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => 'Child',
                'group' => array('id' => $parentGroupId),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $childGroup = Group::getByName('Child');
            $this->assertNotNull($childGroup);
            $this->assertEquals('Child', strval($childGroup));
            $parentGroup->forgetAll();
            $parentGroup    = Group::getById($parentGroupId);

            // give child rights for contacts module
            $childGroup->setRight('ContactsModule', ContactsModule::getAccessRight());
            $childGroup->setRight('ContactsModule', ContactsModule::getCreateRight());
            $this->assertTrue($childGroup->save());
            $childGroupId           = $childGroup->id;
            $childGroup->forgetAll();
            $childGroup = Group::getById($childGroupId);
            $this->assertContains($childGroup, $parentGroup->groups);

            // create jim's user
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => 'jim',
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active')));
            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $jim    = User::getByUsername('jim');
            $this->assertNotNull($jim);

            // set jim's group to child group
            $this->setGetArray(array('id' => $childGroup->id));
            $this->setPostArray(array(
                'GroupUserMembershipForm' => array('userMembershipData' => array($jim->id)
                )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/editUserMembership');
            $jim->forgetAll();
            $jim        = User::getByUsername('jim');
            $this->assertNotNull($jim);
            $childGroup->forgetAll();
            $childGroup = Group::getById($childGroupId);
            $this->assertContains($childGroup, $jim->groups);

            // create a contact with permissions to Parent group
            // create ContactStates
            ContactsModule::loadStartingData();
            // ensure contact states have been created
            $this->assertEquals(6, count(ContactState::GetAll()));
            // go ahead and create contact with parent group given readwrite.
            $startingState  = ContactsUtil::getStartingState();
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'John',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
                'explicitReadWriteModelPermissions' => array(
                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                    'nonEveryoneGroup' => $parentGroupId
                ))));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $johnDoeContactId   = intval(substr($url, strpos($url, 'id=') + 3));
            $johnDoeContact     = Contact::getById($johnDoeContactId);
            $this->assertNotNull($johnDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $johnDoeContactId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Parent', $content);

            // create a contact using jim which he would see at all times
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jim',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
            )));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $jimDoeContactId    = intval(substr($url, strpos($url, 'id=') + 3));
            $jimDoeContact      = Contact::getById($jimDoeContactId);
            $this->assertNotNull($jimDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // ensure jim can see that contact everywhere
            // jim should have access to see contact on list view
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('John Doe</a></td><td>', $content);
            $this->assertContains('Jim Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should have access to johnDoeContact's detail view
            $this->setGetArray(array('id' => $johnDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to johnDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // delete Parent group
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->resetPostArray();
            $this->setGetArray(array('id' => $parentGroup->id));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/delete');
            try
            {
                Group::getByName('Parent');
                $this->fail('Parent group should be deleted');
            }
            catch (NotFoundException $e)
            {
                $parentGroup = null;
            }

            // ensure jim can not see that contact anywhere
            // jim should not have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            // get the page, ensure the name of contact does not show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertNotContains('John Doe</a></td><td>', $content);
            $this->assertContains('Jim Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should not have access to johnDoeContact's detail view
            $this->setGetArray(array('id' => $johnDoeContactId));
            try
            {
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
                $this->fail('Accessing details action should have thrown ExitException');
            }
            catch (ExitException $e)
            {
                // just cleanup buffer
                $this->endAndGetOutputBuffer();
            }

            // jim should not have access to johnDoeContact's edit view
            try
            {
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');
                $this->fail('Accessing edit action should have thrown ExitException');
            }
            catch (ExitException $e)
            {
                // just cleanup buffer
                $this->endAndGetOutputBuffer();
            }
        }

        public function testArePermissionsFlushedOnRemovingParentFromChildGroup()
        {
            // cleanup
            Contact::deleteAll();
            try
            {
                $group  = Group::getByName('Child');
                $group->delete();
            }
            catch (NotFoundException $e)
            {
            }
            try
            {
                $user   = User::getByUsername('jim');
                $user->delete();
            }
            catch (NotFoundException $e)
            {
            }

            // we could have used helpers to do a lot of the following stuff (such as creating users, groups,
            // etc) but we wanted to mimic user's interaction as closely as possible. Hence using walkthroughs
            // for everything

            // create Parent and Child Groups, Create Jim to be member of Child group

            // create parent group
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => 'Parent',
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $parentGroup    = Group::getByName('Parent');
            $this->assertNotNull($parentGroup);
            $this->assertEquals('Parent', strval($parentGroup));
            $parentGroupId  = $parentGroup->id;

            // create child group
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => 'Child',
                'group' => array('id' => $parentGroupId),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $childGroup = Group::getByName('Child');
            $this->assertNotNull($childGroup);
            $this->assertEquals('Child', strval($childGroup));
            $parentGroup->forgetAll();
            $parentGroup    = Group::getById($parentGroupId);

            // give child rights for contacts module
            $childGroup->setRight('ContactsModule', ContactsModule::getAccessRight());
            $childGroup->setRight('ContactsModule', ContactsModule::getCreateRight());
            $this->assertTrue($childGroup->save());
            $childGroupId           = $childGroup->id;
            $childGroup->forgetAll();
            $childGroup = Group::getById($childGroupId);
            $this->assertContains($childGroup, $parentGroup->groups);

            // create jim's user
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => 'jim',
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active')));
            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $jim    = User::getByUsername('jim');
            $this->assertNotNull($jim);

            // set jim's group to child group
            $this->setGetArray(array('id' => $childGroup->id));
            $this->setPostArray(array(
                'GroupUserMembershipForm' => array('userMembershipData' => array($jim->id)
                )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/editUserMembership');
            $jim->forgetAll();
            $jim        = User::getByUsername('jim');
            $this->assertNotNull($jim);
            $childGroup->forgetAll();
            $childGroup = Group::getById($childGroupId);
            $this->assertContains($childGroup, $jim->groups);

            // create a contact with permissions to Parent group
            // create ContactStates
            ContactsModule::loadStartingData();
            // ensure contact states have been created
            $this->assertEquals(6, count(ContactState::GetAll()));
            // go ahead and create contact with parent group given readwrite.
            $startingState  = ContactsUtil::getStartingState();
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'John',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
                'explicitReadWriteModelPermissions' => array(
                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                    'nonEveryoneGroup' => $parentGroupId
                ))));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $johnDoeContactId   = intval(substr($url, strpos($url, 'id=') + 3));
            $johnDoeContact     = Contact::getById($johnDoeContactId);
            $this->assertNotNull($johnDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $johnDoeContactId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Parent', $content);

            // create a contact using jim which he would see at all times
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jim',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
            )));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $jimDoeContactId    = intval(substr($url, strpos($url, 'id=') + 3));
            $jimDoeContact      = Contact::getById($jimDoeContactId);
            $this->assertNotNull($jimDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // ensure jim can see that contact everywhere
            // jim should have access to see contact on list view
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('John Doe</a></td><td>', $content);
            $this->assertContains('Jim Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should have access to johnDoeContact's detail view
            $this->setGetArray(array('id' => $johnDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to johnDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // unlink Parent group from child
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('id' => $childGroupId));
            $this->setPostArray(array('Group' => array(
                'name'  => 'Child',
                'group' => array('id' => ''),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/edit');
            $childGroup = Group::getByName('Child');
            $this->assertNotNull($childGroup);
            $this->assertEquals('Child', strval($childGroup));
            $parentGroup->forgetAll();
            $parentGroup    = Group::getById($parentGroupId);
            $this->assertNotContains($childGroup, $parentGroup->groups);

            // ensure jim can not see that contact anywhere
            // jim should not have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            // get the page, ensure the name of contact does not show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertNotContains('John Doe</a></td><td>', $content);
            $this->assertContains('Jim Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should not have access to johnDoeContact's detail view
            $this->setGetArray(array('id' => $johnDoeContactId));
            try
            {
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
                $this->fail('Accessing details action should have thrown ExitException');
            }
            catch (ExitException $e)
            {
                // just cleanup buffer
                $this->endAndGetOutputBuffer();
            }

            // jim should not have access to johnDoeContact's edit view
            try
            {
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');
                $this->fail('Accessing edit action should have thrown ExitException');
            }
            catch (ExitException $e)
            {
                // just cleanup buffer
                $this->endAndGetOutputBuffer();
            }
        }

        protected function runControllerWithNoExceptionsAndGetContent($route, $empty = false)
        {
            // this is same as parent except we do not care about the exception being throw
            // we did this because in this specific test Exit Exception would only be caused if
            // a user did not have access to an action
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            Yii::app()->runController($route);
            $content = $this->endAndGetOutputBuffer();
            $this->doApplicationScriptPathsAllExist();
            if ($empty)
            {
                $this->assertEmpty($content);
            }
            else
            {
                $this->assertNotEmpty($content);
            }
            return $content;
        }
    }
?>