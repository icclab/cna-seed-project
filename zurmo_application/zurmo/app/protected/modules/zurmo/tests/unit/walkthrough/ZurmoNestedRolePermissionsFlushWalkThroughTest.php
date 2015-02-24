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

    class ZurmoNestedRolePermissionsFlushWalkThroughTest extends ZurmoWalkthroughBaseTest
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

        public function testArePermissionsFlushedOnDeletingParentRole()
        {
            // we could have used helpers to do a lot of the following stuff (such as creating users, roles,
            // etc) but we wanted to mimic user's interaction as closely as possible. Hence using walkthroughs
            // for everything

            // create Parent and Child Roles, Create Jim to be member of Child role

            // create parent role
            $this->resetGetArray();
            $this->setPostArray(array('Role' => array(
                'name'  => 'Parent',
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/role/create');
            $parentRole    = Role::getByName('Parent');
            $this->assertNotNull($parentRole);
            $this->assertEquals('Parent', strval($parentRole));
            $parentRoleId  = $parentRole->id;

            // create child role
            $this->resetGetArray();
            $this->setPostArray(array('Role' => array(
                'name'  => 'Child',
                'role' => array('id' => $parentRoleId),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/role/create');
            $childRole      = Role::getByName('Child');
            $this->assertNotNull($childRole);
            $this->assertEquals('Child', strval($childRole));
            $parentRole->forgetAll();
            $parentRole    = Role::getById($parentRoleId);
            $childRoleId   = $childRole->id;
            $childRole->forgetAll();
            $childRole      = Role::getById($childRoleId);
            $this->assertEquals($childRole->id, $parentRole->roles[0]->id);

            // create jim's user
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => 'jim',
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active',
                    'role'                  => array('id' => $childRoleId),
                )));

            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $jim    = User::getByUsername('jim');
            $this->assertNotNull($jim);
            $childRole->forgetAll();
            $childRole = Role::getById($childRoleId);
            $this->assertEquals($childRole->id, $jim->role->id);
            // give jim rights to contact's module
            $jim->setRight('ContactsModule', ContactsModule::getAccessRight());
            $jim->setRight('ContactsModule', ContactsModule::getCreateRight());
            $this->assertTrue($jim->save());
            $jim->forgetAll();
            $jim    = User::getByUsername('jim');

            // create jane's user
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => 'jane',
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active',
                    'role'                  => array('id' => $parentRoleId),
                )));
            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $jane   = User::getByUsername('jane');
            $this->assertNotNull($jane);
            $parentRole->forgetAll();
            $parentRole = Role::getById($parentRoleId);
            $this->assertEquals($parentRole->id, $jane->role->id);
            // give jane rights to contact's module, we need to do this because once the link between parent and child
            // role is broken jane won't be able to access the listview of contacts
            $jane->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->assertTrue($jane->save());
            $jane->forgetAll();
            $jane    = User::getByUsername('jane');

            // create a contact from jim's account
            // create ContactStates
            ContactsModule::loadStartingData();
            // ensure contact states have been created
            $this->assertEquals(6, count(ContactState::GetAll()));
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            // go ahead and create contact with parent role given readwrite.
            $startingState  = ContactsUtil::getStartingState();
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jim',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
            )));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $jimDoeContactId   = intval(substr($url, strpos($url, 'id=') + 3));
            $jimDoeContact     = Contact::getById($jimDoeContactId);
            $this->assertNotNull($jimDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $jimDoeContactId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Owner', $content);

            // create a contact using jane which she would see at all times
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jane');
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jane',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
            )));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $janeDoeContactId   = intval(substr($url, strpos($url, 'id=') + 3));
            $janeDoeContact     = Contact::getById($jimDoeContactId);
            $this->assertNotNull($janeDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $janeDoeContactId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Owner', $content);

            // ensure jim can see that contact everywhere
            // jim should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('Jim Doe</a></td><td>', $content);
            $this->assertNotContains('Jane Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should not have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
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

            // jim should have access to janeDoeContact's edit view
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

            // ensure jane can see that contact everywhere
            // jane should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jane');
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('Jim Doe</a></td><td>', $content);
            $this->assertContains('Jane Doe</a></td><td>', $content);

            // jane should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jane should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jane should have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jane should have access to janeDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // delete child role
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->resetPostArray();
            $this->setGetArray(array('id' => $childRoleId));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/role/delete');
            try
            {
                Role::getByName('Child');
                $this->fail('Child role should be deleted');
            }
            catch (NotFoundException $e)
            {
                $parentRole = null;
            }

            // ensure jim can still see that contact everywhere
            // jim should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('Jim Doe</a></td><td>', $content);
            $this->assertNotContains('Jane Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should not have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
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

            // jim should have access to janeDoeContact's edit view
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

            // ensure jane can not see that contact anywhere
            // jane should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jane');
            $this->resetGetArray();
            // get the page, ensure the name of contact does not show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertNotContains('Jim Doe</a></td><td>', $content);
            $this->assertContains('Jane Doe</a></td><td>', $content);

            // jane should have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jane should have access to janeDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jane should not have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
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

            // jane should not have access to jimDoeContact's edit view
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

        public function testArePermissionsFlushedOnRemovingParentFromChildRole()
        {
            Contact::deleteAll();
            try
            {
                $role  = Role::getByName('Parent');
                $role->delete();
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
            try
            {
                $user   = User::getByUsername('jane');
                $user->delete();
            }
            catch (NotFoundException $e)
            {
            }

            // we could have used helpers to do a lot of the following stuff (such as creating users, roles,
            // etc) but we wanted to mimic user's interaction as closely as possible. Hence using walkthroughs
            // for everything

            // create Parent and Child Roles, Create Jim to be member of Child role

            // create parent role
            $this->resetGetArray();
            $this->setPostArray(array('Role' => array(
                'name'  => 'Parent',
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/role/create');
            $parentRole    = Role::getByName('Parent');
            $this->assertNotNull($parentRole);
            $this->assertEquals('Parent', strval($parentRole));
            $parentRoleId  = $parentRole->id;

            // create child role
            $this->resetGetArray();
            $this->setPostArray(array('Role' => array(
                'name'  => 'Child',
                'role' => array('id' => $parentRoleId),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/role/create');
            $childRole      = Role::getByName('Child');
            $this->assertNotNull($childRole);
            $this->assertEquals('Child', strval($childRole));
            $parentRole->forgetAll();
            $parentRole    = Role::getById($parentRoleId);
            $childRoleId   = $childRole->id;
            $childRole->forgetAll();
            $childRole      = Role::getById($childRoleId);
            $this->assertEquals($childRole->id, $parentRole->roles[0]->id);

            // create jim's user
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => 'jim',
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active',
                    'role'                  => array('id' => $childRoleId),
                )));

            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $jim    = User::getByUsername('jim');
            $this->assertNotNull($jim);
            $childRole->forgetAll();
            $childRole = Role::getById($childRoleId);
            $this->assertEquals($childRole->id, $jim->role->id);
            // give jim rights to contact's module
            $jim->setRight('ContactsModule', ContactsModule::getAccessRight());
            $jim->setRight('ContactsModule', ContactsModule::getCreateRight());
            $this->assertTrue($jim->save());
            $jim->forgetAll();
            $jim    = User::getByUsername('jim');

            // create jane's user
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => 'jane',
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active',
                    'role'                  => array('id' => $parentRoleId),
                )));
            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $jane   = User::getByUsername('jane');
            $this->assertNotNull($jane);
            $parentRole->forgetAll();
            $parentRole = Role::getById($parentRoleId);
            $this->assertEquals($parentRole->id, $jane->role->id);
            // give jane rights to contact's module, we need to do this because once the link between parent and child
            // role is broken jane won't be able to access the listview of contacts
            $jane->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->assertTrue($jane->save());
            $jane->forgetAll();
            $jane    = User::getByUsername('jane');

            // create a contact from jim's account
            // create ContactStates
            ContactsModule::loadStartingData();
            // ensure contact states have been created
            $this->assertEquals(6, count(ContactState::GetAll()));
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            // go ahead and create contact with parent role given readwrite.
            $startingState  = ContactsUtil::getStartingState();
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jim',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
            )));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $jimDoeContactId   = intval(substr($url, strpos($url, 'id=') + 3));
            $jimDoeContact     = Contact::getById($jimDoeContactId);
            $this->assertNotNull($jimDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $jimDoeContactId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Owner', $content);

            // create a contact using jane which she would see at all times
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jane');
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jane',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
            )));
            $url                = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $janeDoeContactId   = intval(substr($url, strpos($url, 'id=') + 3));
            $janeDoeContact     = Contact::getById($jimDoeContactId);
            $this->assertNotNull($janeDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $janeDoeContactId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Owner', $content);

            // ensure jim can see that contact everywhere
            // jim should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('Jim Doe</a></td><td>', $content);
            $this->assertNotContains('Jane Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should not have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
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

            // jim should have access to janeDoeContact's edit view
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

            // ensure jane can see that contact everywhere
            // jane should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jane');
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('Jim Doe</a></td><td>', $content);
            $this->assertContains('Jane Doe</a></td><td>', $content);

            // jane should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jane should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jane should have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jane should have access to janeDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // unlink Parent role from child
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('id' => $childRoleId));
            $this->setPostArray(array('Role' => array(
                'name'  => 'Child',
                'role' => array('id' => ''),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/role/edit');
            $childRole = Role::getByName('Child');
            $this->assertNotNull($childRole);
            $this->assertEquals('Child', strval($childRole));
            $parentRole->forgetAll();
            $parentRole    = Role::getById($parentRoleId);
            $this->assertNotNull($parentRole);
            $this->assertCount(0, $parentRole->roles);

            // ensure jim can still see that contact everywhere
            // jim should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $this->resetGetArray();
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertContains('Jim Doe</a></td><td>', $content);
            $this->assertNotContains('Jane Doe</a></td><td>', $content);

            // jim should have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jim should have access to jimDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jim should not have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
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

            // jim should have access to janeDoeContact's edit view
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

            // ensure jane can not see that contact anywhere
            // jane should have access to see contact on list view
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jane');
            $this->resetGetArray();
            // get the page, ensure the name of contact does not show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->assertNotContains('Jim Doe</a></td><td>', $content);
            $this->assertContains('Jane Doe</a></td><td>', $content);

            // jane should have access to janeDoeContact's detail view
            $this->setGetArray(array('id' => $janeDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');

            // jane should have access to janeDoeContact's edit view
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');

            // jane should not have access to jimDoeContact's detail view
            $this->setGetArray(array('id' => $jimDoeContactId));
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

            // jane should not have access to jimDoeContact's edit view
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