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

    class MarketingListDefaultControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AllPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            // Test all default controller actions that do not require any POST/GET variables to be passed.
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');

            MarketingListTestHelper::createMarketingListByName('MarketingListName', 'MarketingList Description',
                'first', 'first@zurmo.com');
            MarketingListTestHelper::createMarketingListByName('MarketingListName2', 'MarketingList Description2',
                'second', 'second@zurmo.com');

            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertContains('anyMixedAttributes', $content);
            $this->assertContains('MarketingListName', $content);
            $this->assertContains('MarketingListName2', $content);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertNotContains('anyMixedAttributes', $content);
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListSearchAction()
        {
            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'MarketingListsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'xyz',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertContains('No results found', $content);

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'MarketingListsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'Marketing',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertContains('2 result(s)', $content);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            $this->assertContains('Clark Kent', $content);

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'MarketingListsSearchForm' => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => 'Marketing',
                    'selectedListAttributes'     => array('name', 'createdByUser', 'fromAddress', 'fromName'),
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertContains('2 result(s)', $content);
            $this->assertEquals(2, substr_count($content, 'MarketingListName'));
            $this->assertContains('Clark Kent', $content);
            $this->assertEquals(3, substr_count($content, 'Clark Kent'));
            $this->assertContains('@zurmo.com', $content);
            $this->assertEquals(4, substr_count($content, '@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'first@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'second@zurmo.com'));

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'clearingSearch'            =>  1,
                'MarketingListsSearchForm'  => array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => '',
                    'selectedListAttributes'     => array('name', 'createdByUser', 'fromAddress', 'fromName'),
                    'dynamicClauses'             => array(array(
                        'attributeIndexOrDerivedType'   => 'fromAddress',
                        'structurePosition'             => 1,
                        'fromAddress'                   => 'second@zurmo.com',
                    )),
                    'dynamicStructure'          => '1',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertContains('1 result(s)', $content);
            $this->assertEquals(1, substr_count($content, 'MarketingListName2'));
            $this->assertContains('Clark Kent', $content);
            $this->assertEquals(2, substr_count($content, 'Clark Kent'));
            $this->assertContains('@zurmo.com', $content);
            $this->assertEquals(2, substr_count($content, '@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'second@zurmo.com'));

            StickyReportUtil::clearDataByKey('MarketingListsSearchForm');
            $this->setGetArray(array(
                'clearingSearch'            =>  1,
                'MarketingListsSearchForm'  =>  array(
                    'anyMixedAttributesScope'    => array('All'),
                    'anyMixedAttributes'         => '',
                    'selectedListAttributes'     => array('name', 'createdByUser', 'fromAddress', 'fromName'),
                    'dynamicClauses'             => array(array(
                        'attributeIndexOrDerivedType'   => 'fromName',
                        'structurePosition'             => 1,
                        'fromName'                   => 'first',
                    )),
                    'dynamicStructure'          => '1',
                ) ,
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/list');
            $this->assertContains('1 result(s)', $content);
            $this->assertEquals(1, substr_count($content, 'MarketingListName'));
            $this->assertContains('Clark Kent', $content);
            $this->assertEquals(2, substr_count($content, 'Clark Kent'));
            $this->assertContains('@zurmo.com', $content);
            $this->assertEquals(2, substr_count($content, '@zurmo.com'));
            $this->assertEquals(2, substr_count($content, 'first@zurmo.com'));
        }

        /**
         * @depends testSuperUserListAction
         */
        public function testSuperUserCreateAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertContains('Create Marketing List', $content);
            $this->assertContains('<label for="MarketingList_name" class="required">Name ' .
                                  '<span class="required">*</span></label>', $content);
            $this->assertContains('<label for="MarketingList_description">Description</label>', $content);
            $this->assertContains('<label for="MarketingList_fromName">From Name</label>', $content);
            $this->assertContains('<label for="MarketingList_fromAddress">From Address</label>', $content);
            $this->assertContains('<span class="z-label">Cancel</span>', $content);
            $this->assertContains('<span class="z-label">Save</span>', $content);

            $this->resetGetArray();
            $this->setPostArray(array('MarketingList' => array(
                'name'          => '',
                'description'   => '',
                'fromName'      => '',
                'fromAddress'   => '',
                )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/create');
            $this->assertContains('class="errorMessage">Name cannot be blank.</div>', $content);
            $this->assertContains('<input id="MarketingList_name" name="MarketingList[name]" type="text"' .
                                  ' maxlength="64" value="" class="error"', $content);
            $this->assertContains('<label class="error required" for="MarketingList_name">Name ' .
                                  '<span class="required">*</span></label>', $content);
            $this->resetGetArray();
            $this->setPostArray(array('MarketingList' => array(
                'name'            => 'New MarketingListName using Create',
                'description'     => 'New MarketingList Description using Create',
                'fromName'        => 'Zurmo Sales',
                'fromAddress'     => 'sales@zurmo.com',
                )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/create');
            $marketingList = MarketingList::getByName('New MarketingListName using Create');
            $this->assertEquals(1, count($marketingList));
            $this->assertTrue  ($marketingList[0]->id > 0);
            $this->assertEquals('sales@zurmo.com', $marketingList[0]->fromAddress);
            $this->assertEquals('Zurmo Sales', $marketingList[0]->fromName);
            $this->assertEquals('New MarketingList Description using Create', $marketingList[0]->description);
            $this->assertTrue  ($marketingList[0]->owner == $this->user);
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/details', array('id' => $marketingList[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $marketingList = MarketingList::getAll();
            $this->assertEquals(3, count($marketingList));
        }

        public function testSuperUserDetailsAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'MarketingListName2');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/details');
            $this->assertContains('MarketingListName2', $content);
            $this->assertEquals(3, substr_count($content, 'MarketingListName2'));
            $this->assertContains('<span class="button-label">Details</span>', $content);
            $this->assertContains('<strong class="marketing-list-subscribers-stats">' .
                                  '0 Subscribed</strong>', $content);
            $this->assertContains('<strong class="marketing-list-unsubscribers-stats">' .
                                  '0 Unsubscribed</strong>', $content);
            $this->assertContains('MarketingList Description2', $content);
            $this->assertContains('<span class="button-label">Options</span>', $content);
            $this->assertContains('>Edit</a></li>', $content);
            $this->assertContains('>Delete</a></li>', $content);
            $this->assertContains('<h3>Contacts/Leads</h3>', $content);
            $this->assertContains('<span class="button-label">Add Contact/Lead</span>', $content);
            $this->assertContains('From Contacts/Leads</label>', $content);
            $this->assertContains('From Report</label>', $content);
            $this->assertContains('<span class="button-label">Subscribe</span>', $content);
            $this->assertContains('<span class="button-label">Unsubscribe</span>', $content);
            $this->assertContains('<span class="button-label">Delete</span>', $content);
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserEditAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName using Create');
            $this->setGetArray(array('id' => $marketingListId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/edit');
            $this->assertContains('New MarketingListName using Create', $content);
            $this->assertEquals(2, substr_count($content, 'New MarketingListName using Create'));
            $this->assertContains('New MarketingList Description using Create', $content);
            $this->assertContains('Zurmo Sales', $content);
            $this->assertContains('sales@zurmo.com', $content);
            $this->assertNotContains('Create Marketing List', $content);

            $this->setPostArray(array('MarketingList' => array(
                'name'            => 'New MarketingListName',
                'description'     => 'New MarketingList Description',
                'fromName'        => 'Zurmo Support',
                'fromAddress'     => 'support@zurmo.com',
            )));
            $redirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/edit');
            $marketingList = MarketingList::getByName('New MarketingListName');
            $this->assertEquals(1, count($marketingList));
            $this->assertTrue  ($marketingList[0]->id > 0);
            $this->assertEquals('support@zurmo.com', $marketingList[0]->fromAddress);
            $this->assertEquals('Zurmo Support', $marketingList[0]->fromName);
            $this->assertEquals('New MarketingList Description', $marketingList[0]->description);
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/details', array('id' => $marketingList[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $marketingList = MarketingList::getAll();
            $this->assertEquals(3, count($marketingList));
        }

        /**
         * @depends testSuperUserEditAction
         */
        public function testSuperUserDeleteAction()
        {
            $marketingListId = self::getModelIdByModelNameAndName ('MarketingList', 'New MarketingListName');

            // Delete a marketingList.
            $this->setGetArray(array('id' => $marketingListId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('marketingLists/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('marketingLists/default/index');
            $this->assertEquals($redirectUrl, $compareRedirectUrl);
            $marketingLists = MarketingList::getAll();
            $this->assertEquals(2, count($marketingLists));
        }

        public function testMarketingListDashboardGroupByActions()
        {
            $portlets = Portlet::getAll();
            foreach ($portlets as $portlet)
            {
                if ($portlet->viewType = 'MarketingListOverallMetrics')
                {
                    $marketingListPortlet = $portlet;
                }
            }
            $marketingLists = MarketingList::getAll();

            $this->setGetArray(array(
                        'portletId'         => $portlet->id,
                        'uniqueLayoutId'    => 'MarketingListDetailsAndRelationsViewLeftBottomView',
                        'portletParams'     => array('relationModelId'  => $marketingLists[0]->id,
                                                     'relationModuleId' => 'marketingLists',
                            ),
                    ));
            $this->setPostArray(array(
                        'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_DAY)
                    ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            $this->setPostArray(array(
                        'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
                    ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
            $this->setPostArray(array(
                        'MarketingOverallMetricsForm' => array('groupBy' => MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
                    ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/modalConfigSave');
        }

        public function testAutoComplete()
        {
            $this->setGetArray(array('term' => 'inexistant'));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/autoComplete');
            $decodedContent     = CJSON::decode($content);
            $this->assertNotEmpty($decodedContent);
            $this->assertArrayHasKey(0, $decodedContent);
            $decodedContent     = $decodedContent[0];
            $this->assertArrayHasKey('id', $decodedContent);
            $this->assertArrayHasKey('value', $decodedContent);
            $this->assertArrayHasKey('label', $decodedContent);
            $this->assertNull($decodedContent['id']);
            $this->assertNull($decodedContent['value']);
            $this->assertNotNull($decodedContent['label']);
            $this->assertEquals('No results found', $decodedContent['label']);

            $this->setGetArray(array('term' => 'Mark'));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/autoComplete');
            $decodedContent     = CJSON::decode($content);
            $this->assertNotEmpty($decodedContent);
            $this->assertArrayHasKey(0, $decodedContent);
            $this->assertArrayHasKey(1, $decodedContent);
            $result1     = $decodedContent[0];
            $result2     = $decodedContent[1];

            $this->assertArrayHasKey('id', $result1);
            $this->assertArrayHasKey('value', $result1);
            $this->assertArrayHasKey('label', $result1);
            $this->assertNotNull($result1['id']);
            $this->assertEquals($result1['value'], $result1['label']);
            $this->assertNotNull($result1['label']);
            $this->assertEquals('MarketingListName', $result1['label']);

            $this->assertArrayHasKey('id', $result2);
            $this->assertArrayHasKey('value', $result2);
            $this->assertArrayHasKey('label', $result2);
            $this->assertNotNull($result2['id']);
            $this->assertEquals($result2['value'], $result2['label']);
            $this->assertNotNull($result2['label']);
            $this->assertEquals('MarketingListName2', $result2['label']);
        }

        public function testGetInfoToCopyToCampaign()
        {
            $marketingListId    = self::getModelIdByModelNameAndName('MarketingList', 'MarketingListName');
            $marketingList      = MarketingList::getById($marketingListId);
            $this->setGetArray(array('id' => $marketingListId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'marketingLists/default/getInfoToCopyToCampaign');
            $decodedContent     = CJSON::decode($content);
            $this->assertNotEmpty($decodedContent);
            $this->assertArrayHasKey('fromName', $decodedContent);
            $this->assertArrayHasKey('fromAddress', $decodedContent);
            $this->assertEquals($marketingList->fromName, $decodedContent['fromName']);
            $this->assertEquals($marketingList->fromAddress, $decodedContent['fromAddress']);
        }

        public function testModalList()
        {
            $this->setGetArray(array(
               'modalTransferInformation'   => array(
                   'sourceIdFieldId'    =>  'Campaign_marketingList_id',
                   'sourceNameFieldId'  =>  'Campaign_marketingList_name',
                   'modalId'            =>  'modalContainer-edit-form',
               )
            ));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/default/modalList');
            $this->assertContains('<div id="ModalView">', $content);
            $this->assertContains('<div id="MarketingListsModalSearchAndListView" ' .
                                  'class="ModalSearchAndListView GridView">', $content);
            $this->assertContains('<div id="MarketingListsModalSearchView" class="SearchView ModelView' .
                                  ' ConfigurableMetadataView MetadataView">', $content);
            $this->assertContains('<div class="wide form">', $content);
            $this->assertContains('<form id="search-formmodal" method="post">', $content);
            $this->assertContains('</div><div class="search-view-0"', $content);
            $this->assertContains('<table><tr><th></th><td colspan="3">', $content);
            $this->assertContains('<select class="ignore-style ignore-clearform" id="MarketingListsSearch' .
                                  'Form_anyMixedAttributesScope" multiple="multiple" ' .
                                  'style="display:none;" size="4" name="MarketingListsSearchForm' .
                                  '[anyMixedAttributesScope][]">', $content);
            $this->assertContains('<option value="All" selected="selected">All</option>', $content);
            $this->assertContains('<option value="name">Name</option>', $content);
            $this->assertContains('<input class="input-hint anyMixedAttributes-input" ' .
                                  'onfocus="$(this).select();" size="80" id="MarketingListsSearchForm' .
                                  '_anyMixedAttributes" name="MarketingListsSearchForm' .
                                  '[anyMixedAttributes]" type="text"', $content);
            $this->assertContains('</div><div class="search-form-tools">', $content);
            $this->assertContains('<a id="clear-search-linkmodal" style="display:none;" href="#">' .
                                  'Clear</a>', $content);
            $this->assertContains('<input id="clearingSearch-search-formmodal" type="hidden" ' .
                                  'name="clearingSearch"', $content);
            $this->assertContains('</div></form>', $content);
            $this->assertContains('<div id="modalContainer-search-formmodal"></div>', $content);
            $this->assertContains('<div id="MarketingListsModalListView" class="ModalListView ListView ' .
                                  'ModelView ConfigurableMetadataView MetadataView">', $content);
            $this->assertContains('<div class="cgrid-view type-marketingLists" id="list-viewmodal">', $content);
            $this->assertContains('<div class="summary">1-2 of 2 result(s).</div>', $content);
            $this->assertContains('<table class="items">', $content);
            $this->assertContains('<th id="list-viewmodal_c0">', $content);
            $this->assertContains('<a class="sort-link" href="', $content);
            $this->assertContains('marketingLists/default/modalList?modalTransferInformation%5BsourceId' . // Not Coding Standard
                                  'FieldId%5D=Campaign_marketingList_id&amp;modalTransferInformation%5B' . // Not Coding Standard
                                  'sourceNameFieldId%5D=Campaign_marketingList_name&amp;modalTransfer' .  // Not Coding Standard
                                  'Information%5BmodalId%5D=modalContainer-edit-form&amp;MarketingList' . // Not Coding Standard
                                  '_sort=name">Name</a></th></tr>', $content); // Not Coding Standard
            $this->assertContains('<tr class="odd">', $content);
            $this->assertContains('MarketingListName</a></td></tr>', $content);
            $this->assertContains('<tr class="even">', $content);
            $this->assertContains('MarketingListName2</a></td></tr>', $content);
            $this->assertContains('<div class="pager horizontal">', $content);
            $this->assertContains('<li class="refresh hidden">', $content);
            $this->assertContains('marketingLists/default/modalList?modalTransferInformation%5Bsource'.    // Not Coding Standard
                                  'IdFieldId%5D=Campaign_marketingList_id&amp;modalTransferInformation'.  // Not Coding Standard
                                  '%5BsourceNameFieldId%5D=Campaign_marketingList_name&amp;modal' .       // Not Coding Standard
                                  'TransferInformation%5BmodalId%5D=modalContainer-edit-form">' .         // Not Coding Standard
                                  'refresh</a></li></ul>', $content);
            $this->assertContains('</div><div class="list-preloader">', $content);
            $this->assertContains('<span class="z-spinner"></span></div>', $content);
        }
    }
?>