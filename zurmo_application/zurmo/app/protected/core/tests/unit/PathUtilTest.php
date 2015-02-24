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

    class PathUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testGetAllClassNamesByPathAlias()
        {
            $alias = 'application.core.tests.unit';
            $classes = PathUtil::getAllClassNamesByPathAlias($alias);
            $this->assertNotEmpty($classes);
            $this->assertContains('PathUtilTest', $classes);
        }

        /**
         * @depends testGetAllClassNamesByPathAlias
         */
        public function testGetAllModelClassNames()
        {
            $models = PathUtil::getAllModelClassNames();
            $this->assertNotEmpty($models);
            $this->assertContains('RedBeanModel', $models);
            $this->assertContains('OwnedModel', $models);
        }

        /**
         * @depends testGetAllModelClassNames
         */
        public function testGetAllCanHaveBeanModelClassNames()
        {
            $models = PathUtil::getAllCanHaveBeanModelClassNames();
            $this->assertNotEmpty($models);
            $this->assertNotContains('RedBeanModel', $models);
            $this->assertNotContains('OwnedModel', $models);
        }

        /**
         * @depends testGetAllModelClassNames
         */
        public function testGetAllMungableModelClassNames()
        {
            $modelClassNames = PathUtil::getAllMungableModelClassNames();
            $compareData = array('Account', 'Campaign', 'Contact', 'Conversation', 'EmailMessage', 'EmailTemplate', 'ExportItem',
                                 'GameReward', 'MarketingList', 'Meeting', 'Mission', 'Note', 'Opportunity', 'SavedReport',
                                 'Product', 'SocialItem', 'Task', 'ContactWebForm', 'Project', 'SavedCalendar');
            $this->assertEquals($compareData, $modelClassNames);
            $modelClassNames2 = PathUtil::getAllMungableModelClassNames();
            $this->assertEquals($modelClassNames, $modelClassNames2);
            $modelClassNames3 = PathUtil::getAllMungableModelClassNames();
            $this->assertEquals($modelClassNames2, $modelClassNames3);
        }
    }
?>