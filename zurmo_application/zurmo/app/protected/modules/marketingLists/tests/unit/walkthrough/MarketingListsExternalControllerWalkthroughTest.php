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

    class MarketingListsExternalControllerWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $subscribeUrl             = '/marketingLists/external/subscribe';

        protected $unsubscribeUrl           = '/marketingLists/external/unsubscribe';

        protected $optOutUrl                = '/marketingLists/external/optOut';

        protected $manageSubscriptionsUrl   = '/marketingLists/external/manageSubscriptions';

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            AllPermissionsOptimizationUtil::rebuild();
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         */
        public function testSubscribeActionThrowCHttpExceptionWithoutHash()
        {
            $this->runControllerWithNoExceptionsAndGetContent($this->subscribeUrl);
        }

        /**
         * @depends testSubscribeActionThrowCHttpExceptionWithoutHash
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         */
        public function testUnsubscribeActionThrowCHttpExceptionWithoutHash()
        {
            $this->runControllerWithNoExceptionsAndGetContent($this->unsubscribeUrl);
        }

        /**
         * @depends testUnsubscribeActionThrowCHttpExceptionWithoutHash
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         */
        public function testOptOutActionThrowCHttpExceptionWithoutHash()
        {
            $this->runControllerWithNoExceptionsAndGetContent($this->optOutUrl);
        }

        /**
         * @depends testOptOutActionThrowCHttpExceptionWithoutHash
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         */
        public function testManageSubscriptionsActionThrowCHttpExceptionWithoutHash()
        {
            $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
        }

        /**
         * @depends testManageSubscriptionsActionThrowCHttpExceptionWithoutHash
         * @expectedException NotSupportedException
         */
        public function testSubscribeActionThrowNotSupportedExceptionWithNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewoe1ieloo';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->subscribeUrl);
        }

        /**
         * @depends testSubscribeActionThrowNotSupportedExceptionWithNonHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testUnsubscribeActionThrowNotSupportedExceptionWithNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewoe1ieloo';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->unsubscribeUrl);
        }

        /**
         * @depends testUnsubscribeActionThrowNotSupportedExceptionWithNonHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testOptOutActionThrowNotSupportedExceptionWithNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewoe1ieloo';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->optOutUrl);
        }

        /**
         * @depends testOptOutActionThrowNotSupportedExceptionWithNonHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testManageSubscriptionsActionThrowNotSupportedExceptionWithNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewoe1ieloo';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
        }

        /**
         * @depends testManageSubscriptionsActionThrowNotSupportedExceptionWithNonHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testSubscribeActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->subscribeUrl);
        }

        /**
         * @depends testSubscribeActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testUnsubscribeActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->unsubscribeUrl);
        }

        /**
         * @depends testUnsubscribeActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testOptOutActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->optOutUrl);
        }

        /**
         * @depends testOptOutActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testManageSubscriptionsActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
        }

        /**
         * @depends testManageSubscriptionsActionThrowNotSupportedExceptionForIndecipherableHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testSubscribeActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $hash                   = StringUtil::resolveHashForQueryStringArray($queryStringArray);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->subscribeUrl);
        }

        /**
         * @depends testSubscribeActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters
         * @expectedException NotSupportedException
         */
        public function testUnsubscribeActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $hash                   = StringUtil::resolveHashForQueryStringArray($queryStringArray);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->unsubscribeUrl);
        }

        /**
         * @depends testUnsubscribeActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters
         * @expectedException NotSupportedException
         */
        public function testOptOutActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $hash                   = StringUtil::resolveHashForQueryStringArray($queryStringArray);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->optOutUrl);
        }

        /**
         * @depends testOptOutActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters
         * @expectedException NotSupportedException
         */
        public function testManageSubscriptionsActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $hash                   = StringUtil::resolveHashForQueryStringArray($queryStringArray);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
        }

        /**
         * @depends testManageSubscriptionsActionThrowNotSupportedExceptionForDecipherableHexadecimalHashWithMissingParameters
         * @expectedException NotFoundException
         */
        public function testSubscribeActionThrowNotFoundExceptionForInvalidMarketingListId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact    = ContactTestHelper::createContactByNameForOwner('contact 01', Yii::app()->user->userModel);
            Yii::app()->user->userModel = null;
            $personId   = $contact->getClassId('Person');
            $hash       = GlobalMarketingFooterUtil::resolveHash($personId, 100, 1, 'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->subscribeUrl);
        }

        /**
         * @depends testSubscribeActionThrowNotFoundExceptionForInvalidMarketingListId
         * @expectedException NotFoundException
         */
        public function testUnsubscribeActionThrowNotFoundExceptionForInvalidMarketingListId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact    = ContactTestHelper::createContactByNameForOwner('contact 02', Yii::app()->user->userModel);
            Yii::app()->user->userModel = null;
            $personId   = $contact->getClassId('Person');
            $hash       = GlobalMarketingFooterUtil::resolveHash($personId, 100, 1, 'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->unsubscribeUrl);
        }

        /**
         * @depends testUnsubscribeActionThrowNotFoundExceptionForInvalidMarketingListId
         * @expectedException NotFoundException
         */
        public function testOptoutActionThrowNotFoundExceptionForInvalidMarketingListId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact    = ContactTestHelper::createContactByNameForOwner('contact 03', Yii::app()->user->userModel);
            Yii::app()->user->userModel = null;
            $personId   = $contact->getClassId('Person');
            $hash       = GlobalMarketingFooterUtil::resolveHash($personId, 100, 1, 'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->optOutUrl);
        }

        /**
         * @depends testOptoutActionThrowNotFoundExceptionForInvalidMarketingListId
         */
        public function testManageSubscriptionsActionDoesNotThrowNotFoundExceptionForInvalidMarketingListIdWithNoMarketingLists()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact    = ContactTestHelper::createContactByNameForOwner('contact 04', Yii::app()->user->userModel);
            Yii::app()->user->userModel = null;
            $personId   = $contact->getClassId('Person');
            $hash       = GlobalMarketingFooterUtil::resolveHash($personId, 100, 1, 'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<title>ZurmoCRM - Manage Subscriptions</title>', $content);
            $this->assertContains('<div class="GridView">', $content);
            $this->assertNotContains('<div id="HeaderLinksView">', $content);
            $this->assertContains('<div id="MarketingListsManageSubscriptionsListView" ' .
                                  'class="MetadataView">', $content);
            $this->assertContains('<div class="wrapper">', $content);
            $this->assertContains('<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                  'My Subscriptions</span></span></h1>', $content);
            $this->assertContains('<div class="wide" ' .
                                  'id="marketingLists-manageSubscriptionsList">', $content);
            $this->assertContains('<colgroup><col style="width:20%" />' .
                                  '<col style="width:80%" /></colgroup>', $content);
            $this->assertContains('<td><a class="simple-link marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed"', $content);
            $this->assertContains('/marketingLists/external/optOut?hash=', $content);
            $this->assertContains('>Unsubscribe All/OptOut</a>', $content);
            $this->assertContains('<footer id="FooterView">', $content);
            $this->assertContains('<a href="http://www.zurmo.com" id="credit-link" ' .
                                  'class="clearfix">', $content);
            $this->assertContains('<span>Copyright &#169; Zurmo Inc., 2014. ' .
                                  'All rights reserved.</span></a>', $content);
        }

        /**
         * @depends testManageSubscriptionsActionDoesNotThrowNotFoundExceptionForInvalidMarketingListIdWithNoMarketingLists
         * @expectedException NotFoundException
         */
        public function testSubscribeActionThrowsNotFoundExceptionForInvalidPersonlId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList  = MarketingListTestHelper::createMarketingListByName('marketingList 01',
                                                                                    'description',
                                                                                    'fromName',
                                                                                    'from@domain.com',
                                                                                    true);
            Yii::app()->user->userModel = null;
            $hash       = GlobalMarketingFooterUtil::resolveHash(100, $marketingList->id, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->subscribeUrl);
        }

        /**
         * @depends testSubscribeActionThrowsNotFoundExceptionForInvalidPersonlId
         * @expectedException NotFoundException
         */
        public function testUnsubscribeActionThrowsNotFoundExceptionForInvalidPersonlId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList  = MarketingListTestHelper::createMarketingListByName('marketingList 02',
                                                                                    'description',
                                                                                    'fromName',
                                                                                    'from@domain.com',
                                                                                    false);
            Yii::app()->user->userModel = null;
            $hash       = GlobalMarketingFooterUtil::resolveHash(100, $marketingList->id, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->unsubscribeUrl);
        }

        /**
         * @depends testUnsubscribeActionThrowsNotFoundExceptionForInvalidPersonlId
         * @expectedException NotFoundException
         */
        public function testOptOutActionThrowsNotFoundExceptionForInvalidPersonlId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList  = MarketingListTestHelper::createMarketingListByName('marketingList 03',
                                                                                    'description',
                                                                                    'fromName',
                                                                                    'from@domain.com',
                                                                                    true);
            Yii::app()->user->userModel = null;
            $hash       = GlobalMarketingFooterUtil::resolveHash(100, $marketingList->id, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->optOutUrl);
        }

        /**
         * @depends testOptOutActionThrowsNotFoundExceptionForInvalidPersonlId
         * @expectedException NotFoundException
         */
        public function testManageSubscriptionsActionThrowsNotFoundExceptionForInvalidPersonlId()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList  = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                    'description',
                                                                                    'fromName',
                                                                                    'from@domain.com',
                                                                                    false);
            Yii::app()->user->userModel = null;
            $hash       = GlobalMarketingFooterUtil::resolveHash(100, $marketingList->id, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
        }

        /**
         * @depends testManageSubscriptionsActionThrowsNotFoundExceptionForInvalidPersonlId
         */
        public function testManageSubscriptionsAction()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact        = ContactTestHelper::createContactByNameForOwner('contact 05', Yii::app()->user->userModel);
            $personId       = $contact->getClassId('Person');
            $subscribedIds  = array();
            for ($index = 1; $index < 5; $index++)
            {
                $marketingListName      = 'marketingList 0' . $index;
                $marketingList          = MarketingList::getByName($marketingListName);
                $marketingList          = $marketingList[0];
                $unsubscribed           = ($index % 2);
                if (!$unsubscribed)
                {
                    $subscribedIds[]    = $marketingList->id;
                }
                MarketingListMemberTestHelper::createMarketingListMember($unsubscribed, $marketingList, $contact);
            }
            Yii::app()->user->userModel = null;
            $hash       = GlobalMarketingFooterUtil::resolveHash($personId, $marketingList->id, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<title>ZurmoCRM - Manage Subscriptions</title>', $content);
            $this->assertContains('<div class="GridView">', $content);
            $this->assertNotContains('<div id="HeaderLinksView">', $content);
            $this->assertContains('<div id="MarketingListsManageSubscriptionsListView" ' .
                                  'class="MetadataView">', $content);
            $this->assertContains('<div class="wrapper">', $content);
            $this->assertContains('<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                  'My Subscriptions</span></span></h1>', $content);
            $this->assertContains('<div class="wide" ' .
                                  'id="marketingLists-manageSubscriptionsList">', $content);
            $this->assertContains('<colgroup><col style="width:20%" />' .
                                  '<col style="width:80%" /></colgroup>', $content);
            $this->assertContains('<td><a class="simple-link marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed"', $content);
            $this->assertContains('/marketingLists/external/optOut?hash=', $content);
            $this->assertContains('>Unsubscribe All/OptOut</a></td>', $content);
            $this->assertContains('<footer id="FooterView">', $content);
            $this->assertContains('<a href="http://www.zurmo.com" id="credit-link" ' .
                                  'class="clearfix">', $content);
            $this->assertContains('<span>Copyright &#169; Zurmo Inc., 2014. ' .
                                  'All rights reserved.</span></a>', $content);
            $this->assertContains('<td>marketingList 02</td>', $content);
            $this->assertContains('<td><div class="switch">', $content);
            $this->assertContains('<div class="switch-state clearfix">', $content);
            $this->assertContains('/marketingLists/external/subscribe?hash=', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('<label for="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('_0">Subscribe</label></div>', $content);
            $this->assertContains('/marketingLists/external/unsubscribe?hash=', $content);
            $this->assertContains('_1">Unsubcribe</label></div></div></td>', $content);
            $this->assertContains('<td>marketingList 01</td>', $content);
            $this->assertContains('<td>marketingList 03</td>', $content);
            $this->assertContains('<td>marketingList 04</td>', $content);
            $this->assertEquals(4, substr_count($content, '_0">Subscribe</label></div>'));
            $this->assertEquals(4, substr_count($content, '_1">Unsubcribe</label></div></div></td>'));
            foreach ($subscribedIds as $subscribedId)
            {
                $this->assertContains('checked="checked" type="radio" name="marketingListsManage' .
                                      'SubscriptionListView-toggleUnsubscribed_' . $subscribedId, $content);
            }
        }

        /**
         * @depends testManageSubscriptionsAction
         */
        public function testSubscribeActionToPrivateMarketingList()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList  = MarketingList::getByName('marketingList 04');
            $this->assertNotEmpty($marketingList);
            $marketingList  = $marketingList[0];
            $contact        = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact        = $contact[0];
            $member         = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                        $contact->id,
                                                                                                        0);
            $this->assertNotEmpty($member);
            $member[0]->delete();
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingList->id, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $this->runControllerWithRedirectExceptionAndGetUrl($this->subscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
        }

        /**
         * @depends testSubscribeActionToPrivateMarketingList
         */
        public function testSubscribeActionToPublicMarketingList()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 03');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    1);
            $this->assertNotEmpty($member);
            $member[0]->delete();
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            // we set modelId to 0 and createNewActivity to true, so if it tries to create activity it will throw NotFoundException
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 0,
                                                                                            'AutoresponderItem', true);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            @$this->runControllerWithRedirectExceptionAndGetUrl($this->subscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
        }

        /**
         * @depends testSubscribeActionToPublicMarketingList
         */
        public function testSubscribeActionToPublicMarketingListAlreadyASubscriberOf()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 03');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    0);
            $this->assertNotEmpty($member);
            $this->assertEquals(0, $member[0]->unsubscribed);
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->subscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
        }

        /**
         * @depends testSubscribeActionToPublicMarketingListAlreadyASubscriberOf
         */
        public function testUnsubscribeActionToPublicMarketingListCreatesActivity()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 03');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $personId           = $contact->getClassId('Person');
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    0);
            $this->assertNotEmpty($member);
            $this->assertEquals(0, $member[0]->unsubscribed);
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('Autoresponder 01',
                                                                                'textContent',
                                                                                'htmlContent',
                                                                                10,
                                                                                Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                true,
                                                                                $marketingList);
            $this->assertNotEmpty($autoresponder);
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('-1 week'));
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem(1, $processDateTime,
                                                                                        $autoresponder, $contact);
            $this->assertNotEmpty($autoresponderItem);
            $autoresponderItemActivities    = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                            AutoresponderItemActivity::TYPE_UNSUBSCRIBE,
                                                                            $autoresponderItem->id,
                                                                            $personId);
            $this->assertEmpty($autoresponderItemActivities);
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId,
                                                                                $marketingListId,
                                                                                $autoresponderItem->id,
                                                                                'AutoresponderItem',
                                                                                true);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            @$this->runControllerWithRedirectExceptionAndGetUrl($this->unsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $autoresponderItemActivities    = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                            AutoresponderItemActivity::TYPE_UNSUBSCRIBE,
                                                                            $autoresponderItem->id,
                                                                            $personId);
            $this->assertNotEmpty($autoresponderItemActivities);
            $this->assertCount(1, $autoresponderItemActivities);
        }

        /**
         * @depends testUnsubscribeActionToPublicMarketingListCreatesActivity
         */
        public function testUnsubscribeActionToPrivateMarketingList()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 04');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $marketingList->addNewMember($contact->id, false, $contact);
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                        $contact->id,
                                                                                                        0);
            $this->assertNotEmpty($member);
            $this->assertEquals(0, $member[0]->unsubscribed);
            $personId       = $contact->getClassId('Person');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('Autoresponder 02',
                                                                                'textContent',
                                                                                'htmlContent',
                                                                                10,
                                                                                Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                true,
                                                                                $marketingList);
            $this->assertNotEmpty($autoresponder);
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('-1 week'));
            $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem(1, $processDateTime,
                                                                                            $autoresponder, $contact);
            $this->assertNotEmpty($autoresponderItem);
            $autoresponderItemActivities    = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                            AutoresponderItemActivity::TYPE_UNSUBSCRIBE,
                                                                            $autoresponderItem->id,
                                                                            $personId);
            $this->assertEmpty($autoresponderItemActivities);
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            @$this->runControllerWithRedirectExceptionAndGetUrl($this->unsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertNotContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                     $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                     'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertNotContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                     $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                     'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $autoresponderItemActivities    = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                        AutoresponderItemActivity::TYPE_UNSUBSCRIBE,
                                                                        $autoresponderItem->id,
                                                                        $personId);
            $this->assertEmpty($autoresponderItemActivities);
        }

        /**
         * @depends testUnsubscribeActionToPrivateMarketingList
         */
        public function testUnsubscribeActionToPublicMarketingListAlreadyUnsubcribedOf()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 03');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    1);
            $this->assertNotEmpty($member);
            $this->assertEquals(1, $member[0]->unsubscribed);
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->unsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
        }

        /**
         * @depends testUnsubscribeActionToPublicMarketingListAlreadyUnsubcribedOf
         */
        public function testUnsubscribeActionToPrivateMarketingListAlreadyUnsubscribedOf()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 04');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $marketingList->addNewMember($contact->id, true, $contact);
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    1);
            $this->assertNotEmpty($member);
            $this->assertEquals(1, $member[0]->unsubscribed);
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->unsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
        }

        /**
         * @depends testUnsubscribeActionToPrivateMarketingListAlreadyUnsubscribedOf
         */
        public function testUnsubscribeActionToPublicMarketingListNotEvenAMemberOf()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 03');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    1);
            $this->assertNotEmpty($member);
            $member[0]->delete();
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<td>marketingList 03</td>', $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->unsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->assertContains('<td>marketingList 03</td>', $content);
        }

        /**
         * @depends testUnsubscribeActionToPublicMarketingListNotEvenAMemberOf
         */
        public function testUnsubscribeActionToPrivateMarketingListNotEvenAMemberOf()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 04');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    1);
            $this->assertNotEmpty($member);
            $member[0]->delete();
            $personId       = $contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->unsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
        }

        /**
         * @depends testUnsubscribeActionToPrivateMarketingListNotEvenAMemberOf
         */
        public function testOptOutAction()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $this->assertEquals(0, $contact->primaryEmail->optOut);
            $personId           = $contact->getClassId('Person');
            $marketingListIds   = array();
            for ($index = 1; $index < 5; $index++)
            {
                $marketingListName  = 'marketingList 0' . $index;
                $marketingList      = MarketingList::getByName($marketingListName);
                $this->assertNotEmpty($marketingList);
                $marketingListIds[] = $marketingList[0]->id;
                if ($index === 4)
                {
                    $marketingList[0]->addNewMember($contact->id, false, $contact);
                }
            }
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListIds[0],
                                                                                        1, 'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<td>marketingList 01</td>', $content);
            $this->assertContains('<td>marketingList 02</td>', $content);
            $this->assertContains('<td>marketingList 03</td>', $content);
            $this->assertContains('<td>marketingList 04</td>', $content);
            $this->assertContains('marketingLists/external/subscribe?hash=', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListIds[0] . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListIds[0], $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListIds[1] . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListIds[1], $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListIds[2] . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListIds[2], $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListIds[3] . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListIds[3], $content);
            @$this->runControllerWithRedirectExceptionAndGetUrl($this->optOutUrl);
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<td>marketingList 01</td>', $content);
            $this->assertContains('<td>marketingList 03</td>', $content);
            $this->assertNotContains('<td>marketingList 02</td>', $content);
            $this->assertNotContains('<td>marketingList 04</td>', $content);
            $this->assertContains('marketingLists/external/subscribe?hash=', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListIds[0] . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListIds[0], $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListIds[2] . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListIds[2], $content);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $this->assertEquals(1, $contact->primaryEmail->optOut);
        }

        /**
         * @depends testOptOutAction
         */
        public function testSubscribeActionAfterOptOutActionDisableOptOut()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $marketingList      = MarketingList::getByName('marketingList 04');
            $this->assertNotEmpty($marketingList);
            $marketingList      = $marketingList[0];
            $marketingListId    = $marketingList->id;
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $this->assertEquals(1, $contact->primaryEmail->optOut);
            $personId           = $contact->getClassId('Person');
            $member             = MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed($marketingList->id,
                                                                                                    $contact->id,
                                                                                                    1);
            $this->assertNotEmpty($member);
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, $marketingListId, 1,
                                                                                            'AutoresponderItem', false);
            $this->setGetArray(array(
                'hash'    => $hash,
            ));
            @$this->runControllerWithRedirectExceptionAndGetUrl($this->subscribeUrl);
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<td>marketingList 01</td>', $content);
            $this->assertContains('<td>marketingList 03</td>', $content);
            $this->assertContains('marketingLists/external/subscribe?hash=', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('id="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_', $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  $marketingListId. '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . $marketingListId, $content);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact            = Contact::getByName('contact 05 contact 05son');
            $this->assertNotEmpty($contact);
            $contact            = $contact[0];
            $this->assertEquals(0, $contact->primaryEmail->optOut);
        }

        /**
         * @depends testSubscribeActionAfterOptOutActionDisableOptOut
         */
        public function testUnsubscribeActionThrowsExitExceptionAfterRenderingPreviewView()
        {
            $this->setGetArray(array(
                'hash'  => 'HashDoesNotMatterHere',
                'preview'   => 1,
            ));
            $content = $this->runControllerWithExitExceptionAndGetContent($this->unsubscribeUrl);
            $this->assertContains('<div class="GridView">', $content);
            $this->assertContains('<div id="MarketingListsExternalActionsPreviewView" ' .
                                  'class="splash-view SplashView">', $content);
            $this->assertContains('<div class="Warning">', $content);
            $this->assertContains('<h2>Not so fast!</h2>', $content);
            $this->assertContains('<div class="large-icon">', $content);
            $this->assertContains('<p>Access denied due to preview mode being active.</p>', $content);
            $this->assertContains('<footer id="FooterView">', $content);
        }

        /**
         * @depends testUnsubscribeActionThrowsExitExceptionAfterRenderingPreviewView
         */
        public function testSubscribeActionThrowsExitExceptionAfterRenderingPreviewView()
        {
            $this->setGetArray(array(
                'hash'  => 'HashDoesNotMatterHere',
                'preview'   => 1,
            ));
            $content = $this->runControllerWithExitExceptionAndGetContent($this->subscribeUrl);
            $this->assertContains('<div class="GridView">', $content);
            $this->assertContains('<div id="MarketingListsExternalActionsPreviewView" ' .
                                  'class="splash-view SplashView">', $content);
            $this->assertContains('<div class="Warning">', $content);
            $this->assertContains('<h2>Not so fast!</h2>', $content);
            $this->assertContains('<div class="large-icon">', $content);
            $this->assertContains('<p>Access denied due to preview mode being active.</p>', $content);
            $this->assertContains('<footer id="FooterView">', $content);
        }

        /**
         * @depends testSubscribeActionThrowsExitExceptionAfterRenderingPreviewView
         */
        public function testOptOutActionThrowsExitExceptionAfterRenderingPreviewView()
        {
            $this->setGetArray(array(
                'hash'  => 'HashDoesNotMatterHere',
                'preview'   => 1,
            ));
            $content = $this->runControllerWithExitExceptionAndGetContent($this->optOutUrl);
            $this->assertContains('<div class="GridView">', $content);
            $this->assertContains('<div id="MarketingListsExternalActionsPreviewView" ' .
                                  'class="splash-view SplashView">', $content);
            $this->assertContains('<div class="Warning">', $content);
            $this->assertContains('<h2>Not so fast!</h2>', $content);
            $this->assertContains('<div class="large-icon">', $content);
            $this->assertContains('<p>Access denied due to preview mode being active.</p>', $content);
            $this->assertContains('<footer id="FooterView">', $content);
        }

        /**
         * @depends testOptOutActionThrowsExitExceptionAfterRenderingPreviewView
         */
        public function testManageSubscriptionsActionThrowsExitExceptionAfterRenderingPreviewView()
        {
            $this->setGetArray(array(
                'hash'  => 'HashDoesNotMatterHere',
                'preview'   => 1,
            ));
            $content = $this->runControllerWithExitExceptionAndGetContent($this->manageSubscriptionsUrl);
            $this->assertContains('<div class="GridView">', $content);
            $this->assertContains('<div id="MarketingListsExternalActionsPreviewView" ' .
                                  'class="splash-view SplashView">', $content);
            $this->assertContains('<div class="Warning">', $content);
            $this->assertContains('<h2>Not so fast!</h2>', $content);
            $this->assertContains('<div class="large-icon">', $content);
            $this->assertContains('<p>Access denied due to preview mode being active.</p>', $content);
            $this->assertContains('<footer id="FooterView">', $content);
        }
    }
?>