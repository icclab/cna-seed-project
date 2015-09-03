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

    class EnsureUnsubscribeStateIsRegardedWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $contact;

        protected static $marketingList;

        protected static $marketingListMember;

        protected $portletToggleUnsubscribedUrl     = 'marketingLists/defaultPortlet/toggleUnsubscribed';

        protected $portletSubscribeContactsUrl      = 'marketingLists/defaultPortlet/subscribeContacts';

        protected $externalSubscribeUrl             = '/marketingLists/external/subscribe';

        protected $externalUnsubscribeUrl           = '/marketingLists/external/unsubscribe';

        protected $externalManageSubscriptionsUrl   = '/marketingLists/external/manageSubscriptions';

        protected $contactsMassSubscribeUrl         = 'contacts/default/massSubscribe';

        protected $marketingMassSubscribeUrl        = 'marketingLists/member/massSubscribe';

        protected $marketingMassUnsubscribeUrl      = 'marketingLists/member/massUnsubscribe';

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account                = AccountTestHelper::createAccountByNameForOwner('account', $super);
            static::$contact        = ContactTestHelper::createContactWithAccountByNameForOwner('contact', $super, $account);
            static::$marketingList  = MarketingListTestHelper::createMarketingListByName('marketingList', 'description',
                                                                                    'fromName', 'from@domain.com', true);
            static::$marketingListMember    = MarketingListMemberTestHelper::createMarketingListMember(1,
                                                                                                static::$marketingList,
                                                                                                static::$contact);
            AllPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
            $marketingListId    = static::$marketingList->id;
            static::$marketingList->forget();
            static::$marketingList = MarketingList::getById($marketingListId);
        }

        public function testToggleUnsubscribedToSubscribedFromPortletController()
        {
            $this->setGetArray(array('id' => static::$marketingListMember->id));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    $this->portletToggleUnsubscribedUrl,
                                                                    true);
            $this->assertEmpty($content);
            static::$marketingListMember    = MarketingListMember::getById(static::$marketingListMember->id);
            $this->assertEquals(0, static::$marketingListMember->unsubscribed);

            // do it once more to set it back to unsubscribed = 1
            $content                    = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    $this->portletToggleUnsubscribedUrl,
                                                                    true);
            $this->assertEmpty($content);
            static::$marketingListMember    = MarketingListMember::getById(static::$marketingListMember->id);
            $this->assertEquals(1, static::$marketingListMember->unsubscribed);
            $this->assertEquals(1, MarketingListMember::getCount());
        }

        public function testSubscribeContactsForContactTypeFromPortletController()
        {
            $this->setGetArray(array(
                                   'marketingListId'    => static::$marketingList->id,
                                    'id'                => static::$contact->id,
                                    'type'              => 'contact',
                                ));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent($this->portletSubscribeContactsUrl);
            $contentArray               = CJSON::decode($content);
            $this->assertNotEmpty($contentArray);
            $this->assertArrayHasKey('type', $contentArray);
            $this->assertArrayHasKey('message', $contentArray);
            $this->assertEquals('0 subscribed. 1 skipped, already in the list.', $contentArray['message']);
            $this->assertEquals('message', $contentArray['type']);
            $this->assertEquals(1, MarketingListMember::getCount());
        }

        public function testSubscribeContactsForReportTypeFromPortletController()
        {
            $type                       = 'report';
            $report                     = SavedReportTestHelper::makeSimpleContactRowsAndColumnsReport();
            $this->assertNotNull($report);
            $contactCount               = Contact::getCount();
            $this->setGetArray(array(
                                    'marketingListId'    => static::$marketingList->id,
                                    'id'                => $report->id,
                                    'type'              => $type,
                                ));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent($this->portletSubscribeContactsUrl);
            $contentArray               = CJSON::decode($content);
            $this->assertNotEmpty($contentArray);
            $this->assertArrayHasKey('type', $contentArray);
            $this->assertArrayHasKey('message', $contentArray);
            $this->assertEquals('0 subscribed. ' . $contactCount . ' skipped, already in the list.', $contentArray['message']);
            $this->assertEquals('message', $contentArray['type']);
            $this->assertEquals(1, MarketingListMember::getCount());
        }

        public function testSubscribeActionToPublicMarketingListByGuestFromExternalController()
        {
            $personId       = static::$contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, static::$marketingList->id, 0,
                                                                                        'AutoresponderItem', false);
            $this->setGetArray(array('hash'    => $hash));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->externalManageSubscriptionsUrl);
            // ensure he is unsubscribed by default
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            // try to subscribe him
            $this->runControllerWithRedirectExceptionAndGetUrl($this->externalSubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->externalManageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            // ensure he is subscribed now.
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            $this->assertEquals(1, MarketingListMember::getCount());
        }

        public function testUnsubscribeActionToPublicMarketingListByGuestFromExternalController()
        {
            $personId       = static::$contact->getClassId('Person');
            Yii::app()->user->userModel = null;
            $hash           = GlobalMarketingFooterUtil::resolveHash($personId, static::$marketingList->id, 0,
                                                                        'AutoresponderItem', false);
            $this->setGetArray(array( 'hash'    => $hash, ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent($this->externalManageSubscriptionsUrl);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_0" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_1" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            @$this->runControllerWithRedirectExceptionAndGetUrl($this->externalUnsubscribeUrl);
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->externalManageSubscriptionsUrl);
            $this->assertNotEmpty($content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_0" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            $this->assertContains('id="marketingListsManageSubscriptionListView-toggleUnsubscribed_' .
                                  static::$marketingList->id . '_1" checked="checked" type="radio" name="marketingListsManage' .
                                  'SubscriptionListView-toggleUnsubscribed_' . static::$marketingList->id, $content);
            $this->assertEquals(1, MarketingListMember::getCount());
        }

        public function testMassSubscribeAllSelectedFromMemberController()
        {
            $subscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed(
                                                                                            static::$marketingList->id,
                                                                                            0);
            $this->assertEquals(0, $subscribedCount);

            $this->setGetArray(
                array(
                    'selectAll'                                 => '1',           // Not Coding Standard
                    'MarketingListMembersPortletView_page'      => 1,
                    'id'                                        => static::$marketingList->id,
                )
            );
            $this->setPostArray(
                array(
                    'selectedRecordCount'                       => MarketingListMember::getCount()
                )
            );
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->marketingMassSubscribeUrl);
            $expectedSubscribedCountAfterFirstRequest   = 0;
            $actualSubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed(
                                                                                            static::$marketingList->id,
                                                                                            0);
            $this->assertEquals($expectedSubscribedCountAfterFirstRequest, $actualSubscribedCountAfterFirstRequest);
        }

        public function testMassUnsubscribeAllSelectedFromMemberController()
        {
            static::$marketingListMember->unsubscribed  = 0;
            static::$marketingListMember->setScenario(MarketingListMember::SCENARIO_MANUAL_CHANGE);
            static::$marketingListMember->unrestrictedSave();
            $marketingListMemberId          = static::$marketingListMember->id;
            static::$marketingListMember->forgetAll();
            static::$marketingListMember    = MarketingListMember::getById($marketingListMemberId);
            $this->assertEquals(0, static::$marketingListMember->unsubscribed);
            $unsubscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed(
                                                                                            static::$marketingList->id,
                                                                                            1);

            $this->setGetArray(
                array(
                    'selectAll'                                 => '1',           // Not Coding Standard
                    'MarketingListMembersPortletView_page'      => 1,
                    'id'                                        => static::$marketingListMember->id
                )
            );
            $this->setPostArray(
                array(
                    'selectedRecordCount'                       => MarketingListMember::getCount()
                )
            );
            // Run Mass Unsubscribe
            $pageSize       = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithRedirectExceptionAndGetUrl($this->marketingMassUnsubscribeUrl);
            $expectedUnsubscribedCountAfterFirstRequest   = $unsubscribedCount + 1;
            $actualUnsubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed(
                                                                                            static::$marketingList->id,
                                                                                            1);
            $this->assertEquals($expectedUnsubscribedCountAfterFirstRequest, $actualUnsubscribedCountAfterFirstRequest);
        }

        public function testMassSubscribeActionsAllFromContactsController()
        {
            //MassSubscribe view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent($this->contactsMassSubscribeUrl);
            $this->assertContains('<strong>1</strong>&#160;Contact selected for subscription', $content);

            $pageSize           = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->setGetArray(array(
                'selectAll'     => '1',
                'massSubscribe' => '',
                'Contact_page'  => 1));
            $this->setPostArray(array(
                'selectedRecordCount' => MarketingListMember::getCount(),
                'MarketingListMember' => array('marketingList' => array('id' => static::$marketingList->id))
            ));
            $this->runControllerWithRedirectExceptionAndGetUrl($this->contactsMassSubscribeUrl);
            $expectedSubscribedCountAfterFirstRequest   = 0;
            $actualSubscribedCountAfterFirstRequest     = MarketingListMember::getCountByMarketingListIdAndUnsubscribed(static::$marketingList->id, 0);
            $this->assertEquals($expectedSubscribedCountAfterFirstRequest, $actualSubscribedCountAfterFirstRequest);
        }

        public function testSubscribeContactToListUsingWorkflowAction()
        {
            $this->assertEquals(1, static::$marketingList->marketingListMembers->count());
            $action                       = new ActionForWorkflowForm('Contact', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_SUBSCRIBE_TO_LIST;
            $attributes                   = array('marketingList' => array('shouldSetValue'    => '1',
                                                    'type'          => WorkflowActionAttributeForm::TYPE_STATIC,
                                                    'value'         => static::$marketingList->id));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $helper = new WorkflowActionProcessingHelper(88, 'some name', $action, static::$contact, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $marketingListId    = static::$marketingList->id;
            static::$marketingList->forget();
            static::$marketingList = MarketingList::getById($marketingListId);
            $this->assertEquals(1, static::$marketingList->marketingListMembers->count());
            // should remain unsubscribed even through the workflow request was to subscribe him.
            $this->assertEquals(1, static::$marketingList->marketingListMembers[0]->unsubscribed);
            $this->assertEquals(1, MarketingListMember::getCount());
        }

        public function testUnsubscribeContactFromListUsingWorkflowAction()
        {
            $this->assertEquals(1, static::$marketingList->marketingListMembers->count());
            $this->assertEquals(1, MarketingListMember::getCount());
            //Try to unsubscribe the contact, this doesn't do anything useful as contact is already unsubscribed
            $action                       = new ActionForWorkflowForm('Contact', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UNSUBSCRIBE_FROM_LIST;
            $attributes                   = array('marketingList' => array('shouldSetValue'    => '1',
                'type'          => WorkflowActionAttributeForm::TYPE_STATIC,
                'value'         => static::$marketingList->id));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));

            $helper = new WorkflowActionProcessingHelper(88, 'some name', $action, static::$contact, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $marketingListId    = static::$marketingList->id;
            static::$marketingList->forget();
            static::$marketingList = MarketingList::getById($marketingListId);
            $this->assertEquals(1, static::$marketingList->marketingListMembers->count());
            $this->assertEquals(1, static::$marketingList->marketingListMembers[0]->unsubscribed);
            $this->assertEquals(1, MarketingListMember::getCount());
        }
    }
?>