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
    class SpecialMergeTagsAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testIsSpecialMergeTag()
        {
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('attribute', null));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('attribute', 'something'));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('modelURl', null));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('modelURl', 'something'));
            $this->assertTrue(SpecialMergeTagsAdapter::isSpecialMergeTag('modelUrl', null));
            $this->assertFalse(SpecialMergeTagsAdapter::isSpecialMergeTag('modelUrl', 'something'));
        }

        /**
         * @depends testIsSpecialMergeTag
         */
        public function testResolveModelUrl()
        {
            $contact = ContactTestHelper::createContactByNameForOwner('contact 01', Yii::app()->user->userModel);
            $resolvedModelUrl   = SpecialMergeTagsAdapter::resolve('modelUrl', $contact);
            $this->assertNotNull($resolvedModelUrl);
            $expectedSuffix                 = '/contacts/default/details?id=' . $contact->id;
            $this->assertContains($expectedSuffix, $resolvedModelUrl);
        }

        /**
         * @depends testResolveModelUrl
         */
        public function testResolveBaseUrl()
        {
            $resolvedBaseUrl    = SpecialMergeTagsAdapter::resolve('baseUrl', null);
            $this->assertNotNull($resolvedBaseUrl);
            $this->assertContains('localhost', $resolvedBaseUrl);
        }

        /**
         * @depends testResolveBaseUrl
         */
        public function testResolveApplicationName()
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', 'Demo App');
            $resolvedApplicationName    = SpecialMergeTagsAdapter::resolve('applicationName', null);
            $expectedApplicationName    = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            $this->assertNotNull($resolvedApplicationName);
            $this->assertEquals($expectedApplicationName, $resolvedApplicationName);
        }

        /**
         * @depends testResolveApplicationName
         */
        public function testResolveCurrentYear()
        {
            $resolvedCurrentYear    = SpecialMergeTagsAdapter::resolve('currentYear', null);
            $expectedCurrentYear    = date('Y');
            $this->assertNotNull($resolvedCurrentYear);
            $this->assertEquals($expectedCurrentYear, $resolvedCurrentYear);
        }

        /**
         * @depends testResolveCurrentYear
         */
        public function testResolveLastYear()
        {
            $resolvedLastYear        = SpecialMergeTagsAdapter::resolve('lastYear', null);
            $expectedLastYear       = date('Y') - 1;
            $this->assertNotNull($resolvedLastYear);
            $this->assertEquals($expectedLastYear, $resolvedLastYear);
        }

        public function testResolveAvatars()
        {
            $super = User::getByUsername('super');
            $contact = new Contact();
            $contact->owner = $super;
            $resolvedAvatarImage = SpecialMergeTagsAdapter::resolve('ownersAvatarSmall', $contact);
            $expectedAvatarImage = '<img class="gravatar" width="32" height="32" src="http://www.gravatar.com/avatar/?s=32&amp;r=g&amp;d=mm" alt="Clark Kent" />'; // Not Coding Standard
            $this->assertEquals($expectedAvatarImage, $resolvedAvatarImage);

            $resolvedAvatarImage = SpecialMergeTagsAdapter::resolve('ownersAvatarMedium', $contact);
            $expectedAvatarImage = '<img class="gravatar" width="64" height="64" src="http://www.gravatar.com/avatar/?s=64&amp;r=g&amp;d=mm" alt="Clark Kent" />'; // Not Coding Standard
            $this->assertEquals($expectedAvatarImage, $resolvedAvatarImage);

            $resolvedAvatarImage = SpecialMergeTagsAdapter::resolve('ownersAvatarLarge', $contact);
            $expectedAvatarImage = '<img class="gravatar" width="128" height="128" src="http://www.gravatar.com/avatar/?s=128&amp;r=g&amp;d=mm" alt="Clark Kent" />'; // Not Coding Standard
            $this->assertEquals($expectedAvatarImage, $resolvedAvatarImage);
        }

        /**
         * @depends testResolveAvatars
         */
        public function testResolveOwnersEmailSignature()
        {
            $super = User::getByUsername('super');
            $emailSignature = new EmailSignature();
            $emailSignature->htmlContent = 'my email signature';
            $super->emailSignatures->add($emailSignature);
            $super->save();
            $super->forget();
            $super = User::getByUsername('super');
            $contact = new Contact();
            $contact->owner = $super;
            $resolvedEmailSignature = SpecialMergeTagsAdapter::resolve('ownersEmailSignature', $contact);
            $expectedEmailSignature = 'my email signature';
            $this->assertEquals($expectedEmailSignature, $resolvedEmailSignature);
        }

        /**
         * @depends testIsSpecialMergeTag
         */
        public function testResolveGlobalMarketingFooterHtml()
        {
            $resolvedFooter         = SpecialMergeTagsAdapter::resolve('globalMarketingFooterHtml', null);
            $expectedFooter         = GlobalMarketingFooterUtil::getContentByType(true, true);
            $this->assertNotNull($resolvedFooter);
            $this->assertEquals($expectedFooter, $resolvedFooter);
        }

        /**
        * @depends testResolveGlobalMarketingFooterHtml
        */
        public function testResolveGlobalMarketingFooterHtmlWithValidNestedMergeTag()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array('personId'          => 1,
                                            'marketingListId'   => 2,
                                            'modelId'           => 3,
                                            'modelType'         => 'AutoresponderItem',
                                            'createNewActivity' => true,
                                            'preview'           => false);
            $footer                 = GlobalMarketingFooterUtil::getContentByType(true, true);
            GlobalMarketingFooterUtil::setContentByType($footer, true);
            $resolvedFooter         = SpecialMergeTagsAdapter::resolve('globalMarketingFooterHtml', $model, $params);
            $expectedFooter         = GlobalMarketingFooterUtil::getContentByType(true, false);
            $this->assertNotNull($resolvedFooter);
            // resolved one would be without merge tags, expected would have those.
            $this->assertNotEquals($expectedFooter, $resolvedFooter);
            $this->assertNotContains('PRIMARY^EMAIL', $resolvedFooter);
            $this->assertContains('PRIMARY^EMAIL', $expectedFooter);
            $this->assertNotContains('(None)', $expectedFooter);
        }

        /**
         * @depends testResolveGlobalMarketingFooterHtmlWithValidNestedMergeTag
         */
        public function testResolveGlobalMarketingFooterHtmlWithInvalidNestedMergeTag()
        {
            $model                  = Yii::app()->user->userModel;
            $footer                 = GlobalMarketingFooterUtil::getContentByType(true, true);
            $footer                 .= '<br/>This email was sent to [[INVALID^TAG]].';
            GlobalMarketingFooterUtil::setContentByType($footer, true);
            $resolvedFooter         = SpecialMergeTagsAdapter::resolve('globalMarketingFooterHtml', $model);
            $expectedFooter         = GlobalMarketingFooterUtil::getContentByType(true, false);
            $this->assertNotNull($resolvedFooter);
            $this->assertEquals($expectedFooter, $resolvedFooter);
            $this->assertContains('INVALID^TAG', $resolvedFooter);
        }

        /**
         * @depends testIsSpecialMergeTag
         */
        public function testResolveGlobalMarketingFooterPlainText()
        {
            $resolvedFooter         = SpecialMergeTagsAdapter::resolve('globalMarketingFooterPlainText', null);
            $expectedFooter         = GlobalMarketingFooterUtil::getContentByType(false, true);
            $this->assertNotNull($resolvedFooter);
            $this->assertEquals($expectedFooter, $resolvedFooter);
        }

        /**
         * @depends testResolveGlobalMarketingFooterPlainText
         */
        public function testResolveGlobalMarketingFooterPlainTextWithValidNestedMergeTag()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array('personId'          => 1,
                                            'marketingListId'   => 2,
                                            'modelId'           => 3,
                                            'modelType'         => 'AutoresponderItem',
                                            'createNewActivity' => true,
                                            'preview'           => false);
            $footer                 = GlobalMarketingFooterUtil::getContentByType(false, true);
            GlobalMarketingFooterUtil::setContentByType($footer, false);
            $resolvedFooter         = SpecialMergeTagsAdapter::resolve('globalMarketingFooterPlainText', $model, $params);
            $expectedFooter         = GlobalMarketingFooterUtil::getContentByType(false, false);
            $this->assertNotNull($resolvedFooter);
            // resolved one would be without merge tags, expected would have those.
            $this->assertNotEquals($expectedFooter, $resolvedFooter);
            $this->assertNotContains('PRIMARY^EMAIL', $resolvedFooter);
            $this->assertContains('PRIMARY^EMAIL', $expectedFooter);
            $this->assertNotContains('(None)', $expectedFooter);
        }

        /**
         * @depends testResolveGlobalMarketingFooterPlainTextWithValidNestedMergeTag
         */
        public function testResolveGlobalMarketingFooterPlainTextWithInvalidNestedMergeTag()
        {
            $model                  = Yii::app()->user->userModel;
            $footer                 = GlobalMarketingFooterUtil::getContentByType(false, true);
            $footer                 .= ' This email was sent to [[INVALID^TAG]].';
            GlobalMarketingFooterUtil::setContentByType($footer, false);
            $resolvedFooter         = SpecialMergeTagsAdapter::resolve('globalMarketingFooterPlainText', $model);
            $expectedFooter         = GlobalMarketingFooterUtil::getContentByType(false, false);
            $this->assertNotNull($resolvedFooter);
            $this->assertEquals($expectedFooter, $resolvedFooter);
            $this->assertContains('INVALID^TAG', $resolvedFooter);
        }

        /**
         * @depends testResolveGlobalMarketingFooterPlainTextWithInvalidNestedMergeTag
         */
        public function testResolveUnsubscribeUrlWithoutAnyParams()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array();
            try
            {
                $expected = GlobalMarketingFooterUtil::resolveUnsubscribeUrlByArray($params);
                $this->fail('Missing NotSupportedException due to invalid hash');
            }
            catch (NotSupportedException $e)
            {
            }
            $resolved               = SpecialMergeTagsAdapter::resolve('unsubscribeUrl', $model, $params);
            $this->assertEquals(MergeTagsToModelAttributesAdapter::PROPERTY_NOT_FOUND, $resolved);
        }

        /**
         * @depends testResolveUnsubscribeUrlWithoutAnyParams
         */
        public function testResolveUnsubscribeUrlWithMissingSomeParams()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array('personId' => 1, 'marketingListId' => 2);
            try
            {
                $expected = GlobalMarketingFooterUtil::resolveUnsubscribeUrlByArray($params);
                $this->fail('Missing NotSupportedException due to invalid hash');
            }
            catch (NotSupportedException $e)
            {
            }
            $resolved               = SpecialMergeTagsAdapter::resolve('unsubscribeUrl', $model, $params);
            $this->assertEquals(MergeTagsToModelAttributesAdapter::PROPERTY_NOT_FOUND, $resolved);
        }

        /**
         * @depends testResolveUnsubscribeUrlWithoutAnyParams
         */
        public function testResolveUnsubscribeUrlWithMissingSomeOptionalParams()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array('personId'          => 1,
                                            'marketingListId'   => 2,
                                            'modelId'           => 3,
                                            'modelType'         => 'AutoresponderItem');
            $expected               = GlobalMarketingFooterUtil::resolveUnsubscribeUrlByArray($params);
            $this->assertContains('marketingLists/external/unsubscribe?hash=', $expected);
            $resolved               = SpecialMergeTagsAdapter::resolve('unsubscribeUrl', $model, $params);
            $this->assertContains('marketingLists/external/unsubscribe?hash=', $resolved);
            $expectedHash           = static::extractHashFromUrl($expected);
            $resolvedHash           = static::extractHashFromUrl($resolved);
            $expectedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($expectedHash);
            $resolvedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($resolvedHash);
            $this->assertEquals($expectedReversed, $resolvedReversed);
        }

        /**
         * @depends testResolveUnsubscribeUrlWithMissingSomeOptionalParams
         */
        public function testResolveUnsubscribeUrlWithoutModel()
        {
            $model                  = null;
            $params                 = array('personId'          => 1,
                                            'marketingListId'   => 2,
                                            'modelId'           => 3,
                                            'modelType'         => 'AutoresponderItem',
                                            'createNewActivity' => true,
                                            'preview'           => false);
            $expected               = GlobalMarketingFooterUtil::resolveUnsubscribeUrlByArray($params);
            $this->assertContains('marketingLists/external/unsubscribe?hash=', $expected);
            $resolved               = SpecialMergeTagsAdapter::resolve('unsubscribeUrl', $model, $params);
            $this->assertContains('marketingLists/external/unsubscribe?hash=', $resolved);
            $expectedHash           = static::extractHashFromUrl($expected);
            $resolvedHash           = static::extractHashFromUrl($resolved);
            $expectedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($expectedHash);
            $resolvedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($resolvedHash);
            $this->assertEquals($expectedReversed, $resolvedReversed);
        }

        /**
         * @depends testResolveUnsubscribeUrlWithoutModel
         */
        public function testResolveManageSubscriptionsUrlWithoutAnyParams()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array();
            try
            {
                $expected = GlobalMarketingFooterUtil::resolveManageSubscriptionsUrlByArray($params);
                $this->fail('Missing NotSupportedException due to invalid hash');
            }
            catch (NotSupportedException $e)
            {
            }
            $resolved               = SpecialMergeTagsAdapter::resolve('manageSubscriptionsUrl', $model, $params);
            $this->assertEquals(MergeTagsToModelAttributesAdapter::PROPERTY_NOT_FOUND, $resolved);
        }

        /**
         * @depends testResolveManageSubscriptionsUrlWithoutAnyParams
         */
        public function testResolveManageSubscriptionsUrlWithMissingSomeParams()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array('personId' => 1, 'marketingListId' => 2);
            try
            {
                $expected = GlobalMarketingFooterUtil::resolveManageSubscriptionsUrlByArray($params);
                $this->fail('Missing NotSupportedException due to invalid hash');
            }
            catch (NotSupportedException $e)
            {
            }
            $resolved               = SpecialMergeTagsAdapter::resolve('manageSubscriptionsUrl', $model, $params);
            $this->assertEquals(MergeTagsToModelAttributesAdapter::PROPERTY_NOT_FOUND, $resolved);
        }

        /**
         * @depends testResolveManageSubscriptionsUrlWithoutAnyParams
         */
        public function testResolveManageSubscriptionsUrlWithMissingSomeOptionalParams()
        {
            $model                  = Yii::app()->user->userModel;
            $params                 = array('personId'          => 1,
                                                'marketingListId'   => 2,
                                                'modelId'           => 3,
                                                'modelType'         => 'AutoresponderItem');
            $expected               = GlobalMarketingFooterUtil::resolveManageSubscriptionsUrlByArray($params);
            $this->assertContains('marketingLists/external/manageSubscriptions?hash=', $expected);
            $resolved               = SpecialMergeTagsAdapter::resolve('manageSubscriptionsUrl', $model, $params);
            $this->assertContains('marketingLists/external/manageSubscriptions?hash=', $resolved);
            $expectedHash           = static::extractHashFromUrl($expected);
            $resolvedHash           = static::extractHashFromUrl($resolved);
            $expectedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($expectedHash);
            $resolvedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($resolvedHash);
            $this->assertEquals($expectedReversed, $resolvedReversed);
        }

        /**
         * @depends testResolveManageSubscriptionsUrlWithMissingSomeOptionalParams
         */
        public function testResolveManageSubscriptionsUrlWithoutModel()
        {
            $model                  = null;
            $params                 = array('personId'          => 1,
                                            'marketingListId'   => 2,
                                            'modelId'           => 3,
                                            'modelType'         => 'AutoresponderItem',
                                            'createNewActivity' => true,
                                            'preview'           => false);
            $expected               = GlobalMarketingFooterUtil::resolveManageSubscriptionsUrlByArray($params);
            $this->assertContains('marketingLists/external/manageSubscriptions?hash=', $expected);
            $resolved               = SpecialMergeTagsAdapter::resolve('manageSubscriptionsUrl', $model, $params);
            $this->assertContains('marketingLists/external/manageSubscriptions?hash=', $resolved);
            $expectedHash           = static::extractHashFromUrl($expected);
            $resolvedHash           = static::extractHashFromUrl($resolved);
            $expectedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($expectedHash);
            $resolvedReversed       = EmailMessageActivityUtil::resolveQueryStringArrayForHash($resolvedHash);
            $this->assertEquals($expectedReversed, $resolvedReversed);
        }

        protected static function extractHashFromUrl($url)
        {
            $hashPosition       = strpos($url, '?hash=') + 6;
            $previewPosition    = strpos($url, '&preview');
            $length             = ($previewPosition)? $previewPosition - strlen($url) : strlen($url) - $hashPosition;
            return substr($url, $hashPosition, $length);
        }
    }
?>