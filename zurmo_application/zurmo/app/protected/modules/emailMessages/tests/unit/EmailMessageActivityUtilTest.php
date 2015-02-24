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

    class EmailMessageActivityUtilTest extends ZurmoBaseTest
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

        /**
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForNonHexadecimalHash()
        {
            $hash       = 'Bo9iemeigh6muath8chu2leThohn8Abimoh5rebaihei4aiM1uFoThaith9eng1sei8aisuHu1ugoophiewo3ohphie1ieloo';
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForNonHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForIndecipherableHexadecimalHash()
        {
            $hash       = 'DEDF8F6C80D20528130EBBFBD293E49C9E2F0CBFDE8995FFE4EEAD8EC8F00B70';
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForIndecipherableHexadecimalHash
         * @expectedException NotSupportedException
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForDecipherableHexadecimalHashWithMissingParameters()
        {
            $queryStringArray = array(
                'keyOne'    => 'valueOne',
                'keyTwo'    => 'valueTwo',
                'keyThree'  => 'ValueThree',
            );
            $hash       = StringUtil::resolveHashForQueryStringArray($queryStringArray);
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
        }

        /**
         * @depends testResolveQueryStringFromUrlAndCreateNewActivityThrowsExceptionForDecipherableHexadecimalHashWithMissingParameters
         */
        public function testResolveQueryStringFromUrlAndCreateNewActivityDoesNotThrowsExceptionForMissingUrlParameter()
        {
            $queryStringArray = array(
                'modelId'   => 1,
                'modelType' => 'ModelClassName',
                'personId'  => 2,
            );
            $hash       = StringUtil::resolveHashForQueryStringArray($queryStringArray);
            $result     = EmailMessageActivityUtil::resolveQueryStringArrayForHash($hash);
            $this->assertTrue(is_array($result));
            $this->assertCount(5, $result);
            $this->assertArrayHasKey('modelId', $result);
            $this->assertArrayHasKey('modelType', $result);
            $this->assertArrayHasKey('personId', $result);
            $this->assertArrayHasKey('url', $result);
            $this->assertArrayHasKey('type', $result);
            $this->assertEquals($queryStringArray['modelId'], $result['modelId']);
            $this->assertEquals($queryStringArray['modelType'], $result['modelType']);
            $this->assertEquals($queryStringArray['personId'], $result['personId']);
            $this->assertNull($result['url']);
            $this->assertNull($result['type']);
        }

        public function testReturnTrueWithNoTracking()
        {
            $content    = 'Sample Content with no links';
            $result     = static::resolveContent($content, false, false);
            $this->assertTrue($result);
            $this->assertNotEquals('Sample Content with no links', $content);
            $this->assertContains('Sample Content with no links', $content);
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testReturnTrueWithNoTracking
         */
        public function testTextContentDoesNotChangeWhenNoLinksArePresent()
        {
            $content    = 'Sample Content with no links';
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertContains('Sample Content with no links', $content);
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testTextContentDoesNotChangeWhenNoLinksArePresent
         */
        public function testReturnsFalseWithFewTrackingUrlsInPlaceAlready()
        {
            $content    = '/tracking/default/track';
            $result     = static::resolveContent($content, true, false);
            $this->assertFalse($result);
        }

        /**
         * @depends testReturnsFalseWithFewTrackingUrlsInPlaceAlready
         */
        public function testTextContentLinksAreConvertedToTracked()
        {
            $content    = <<<LNK
Link: http://www.zurmo.com
Another: http://zurmo.org
www.yahoo.com
LNK;
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertNotContains('http://www.zurmo.com', $content);
            $this->assertNotContains('http://www.zurmo.org', $content);
            $this->assertNotContains('www.yahoo.com', $content);
            $this->assertContains('/tracking/default/track?id=', $content);
            $this->assertEquals(3, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testTextContentLinksAreConvertedToTracked
         */
        public function testTextContentLinkConversionIgnoresHref()
        {
            $content    = 'Link: http://www.zurmo.com , <a href="http://www.zurmo.org">Zurmo</a>';
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertNotContains('http://www.zurmo.com', $content);
            $this->assertContains('<a href="http://www.zurmo.org">', $content);
            $this->assertContains('/tracking/default/track?id=', $content);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testTextContentLinkConversionIgnoresHref
         */
        public function testHtmlContentWithoutAnyLinksAndNoDOMStructureStillGetsEmailOpenTracking()
        {
            $content    = 'Sample content';
            $result     = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals('Sample content', $content);
            $this->assertContains('Sample content', $content);
            $this->assertContains('<img width="1" height="1" src="', $content);
            $this->assertContains('/tracking/default/track?id=', $content);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithoutAnyLinksAndNoDOMStructureStillGetsEmailOpenTracking
         */
        public function testHtmlContentWithMultipleClosingBodyTagsGetOnlyOneEmailOpenTracking()
        {
            $content    = 'Sample content</body></body></body>';
            $result     = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals('Sample content', $content);
            $this->assertContains('Sample content', $content);
            $this->assertContains('</body></body>', $content);
            $this->assertContains('<img width="1" height="1" src="', $content);
            $this->assertEquals(1, substr_count($content, '<img width="1" height="1" src="'));
            $this->assertContains('/tracking/default/track?id=', $content);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithMultipleClosingBodyTagsGetOnlyOneEmailOpenTracking
         */
        public function testHtmlContentWithoutAnyLinksAndSomeDOMStructureStillGetsEmailOpenTracking()
        {
            $content            = '<html><head><title>Page title</title></head><body><p>Sample Content</p></body></html>';
            $originalContent    = $content;
            $result             = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals($originalContent, $content);
            $this->assertContains('<p>Sample Content</p>', $content);
            $this->assertContains('<p>Sample Content</p><br /><img width="1" height="1" src="', $content);
            $this->assertContains('/tracking/default/track?id=', $content);
            $this->assertEquals(1, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithoutAnyLinksAndSomeDOMStructureStillGetsEmailOpenTracking
         */
        public function testHtmlContentWithPlainLinkGetsTracking()
        {
            $content    = <<<HTML
<html>
<head>
<title>
Page Title
</title>
</head>
<body>
<p>Sample Content With Links</p>
<p>Plain Link: http://www.zurmo.com</p>
</body>
</html>
HTML;
            $originalContent    = $content;
            $result             = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals($originalContent, $content);
            $this->assertContains('<p>Sample Content With Links</p>', $content);
            $this->assertNotContains('<p>Plain Link: http://www.zurmo.com</p>', $content);
            $this->assertContains('<img width="1" height="1" src="', $content);
            $this->assertContains('/tracking/default/track?id=', $content);
            $this->assertEquals(2, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        /**
         * @depends testHtmlContentWithPlainLinkGetsTracking
         */
        public function testHtmlContentWithValidHrefAndPlainLinkGetsTracking()
        {
            $content    = <<<HTML
<html>
<head>
<title>
Page Title
</title>
</head>
<body>
<p>Sample Content With Links</p>
<p>Plain Link1: http://www.zurmo1.com</p>
<p>Plain Link2:http://www.zurmo2.com</p>
<p>Plain Link3: http://www.zurmo3.com </p>
<p>Plain Link4:
 http://www.zurmo4.com</p>
<p>Plain Link5:
http://www.zurmo5.com</p>
<p>Plain Link6:
http://www.zurmo6.com </p>
<p>Plain Link7:
http://www.zurmo7.com </p>
<p>Link1: <a href="http://www.zurmo.org">Zurmo</a></p>
<p>Link2: <a href='http://www.sourceforge1.org'>SourceForge</a></p>
<p>Link3: <a href='http://www.sourceforge2.org'>http://www.sourceforge2.org</a></p>
<p>Link4: <a href='http://www.sourceforge3.org'> http://www.sourceforge3.org</a></p>
<p>Link5: <a href='http://www.sourceforge4.org'>http://www.sourceforge4.org </a></p>
<p>Link6: <a href='http://www.sourceforge5.org'> http://www.sourceforge5.org </a></p>
<p>Link7: <a target='_blank' href='http://www.sourceforge6.org' style='color:red;'> http://www.sourceforge6.org </a></p>
<p>Link8: http://www.sourceforge8.org</a></p>
<p>Link9: http://www.sourceforge9.org </a></p>
<p>Link10:
<a href="http://www.sourceforge10.org">http://www.sourceforge10.org</a></p>
<p>Link11: <a
 href='http://www.sourceforge11.org'>http://www.sourceforge11.org</a></p>
<p>Link12: <a href='http://www.sourceforge12.org'>
 http://www.sourceforge12.org</a></p>
<p>Link13: <a href='http://www.sourceforge13.org'>
  http://www.sourceforge13.org</a></p>
<p>Link14: <a href='http://www.sourceforge14.org'>
  http://www.sourceforge14.org </a></p>
<p>Link15: <a href='#localanchor'>New</a></p>
<p>Link16: <a href='http://www.sourceforge16.org/projects#promoted'>Promoted Projects</a></p>
<img src='http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png' alt='Zurmo Logo' />
<link rel="apple-touch-icon" sizes="144x144" href="http://www.zurmo.com/icon.png">
<link rel="stylesheet" type="text/css" href="http://www.zurmo.com/css/keyframes.css">
<link rel="stylesheet" type="text/css" href="http://www.zurmo.com/zurmo/app/index.php/min/serve/g/css/lm/1366956624">
<script type="text/javascript" src="http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697759"></script>
<script type="text/javascript" src="http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697751.js"></script>
</body>
</html>
HTML;
            $originalContent    = $content;
            $result             = static::resolveContent($content);
            $this->assertTrue($result);
            $this->assertNotEquals($originalContent, $content);
            $this->assertEquals(19, substr_count($content, '/tracking/default/track?id='));
            $this->assertContains('<p>Sample Content With Links</p>', $content);
            $this->assertContains('http://www.zurmo1.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo1.com'));
            $this->assertContains('http://www.zurmo2.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo2.com'));
            $this->assertContains('http://www.zurmo3.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo3.com'));
            $this->assertContains('http://www.zurmo4.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo4.com'));
            $this->assertContains('http://www.zurmo5.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo5.com'));
            $this->assertContains('http://www.zurmo6.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo6.com'));
            $this->assertContains('http://www.zurmo7.com', $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo7.com'));
            $this->assertNotContains('http://www.zurmo.org', $content);
            $this->assertContains("SourceForge", $content);
            $this->assertNotContains(" href='http://www.sourceforge1.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge2.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge3.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge4.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge5.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge6.org'", $content);
            $this->assertContains("http://www.sourceforge2.org", $content);
            $this->assertContains("http://www.sourceforge3.org", $content);
            $this->assertContains("http://www.sourceforge4.org", $content);
            $this->assertContains("http://www.sourceforge5.org", $content);
            $this->assertContains("http://www.sourceforge6.org", $content);
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge2.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge3.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge4.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge5.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge6.org'));
            $this->assertContains("http://www.sourceforge8.org", $content);
            $this->assertContains("http://www.sourceforge9.org", $content);
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge8.org'));
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge9.org'));
            $this->assertNotContains(" href='http://www.sourceforge1.org'", $content);
            $this->assertContains("http://www.sourceforge10.org", $content);
            $this->assertEquals(1, substr_count($content, 'http://www.sourceforge10.org'));
            $this->assertContains('Link10:' . "\n" . '<a href="', $content);
            $this->assertEquals(1, substr_count($content, 'Link10:' . "\n" . '<a href="'));
            $this->assertContains("Link7: <a target='_blank' ", $content);
            $this->assertEquals(1, substr_count($content, "Link7: <a target='_blank' "));
            $this->assertContains(" style='color:red;'> ", $content);
            $this->assertEquals(1, substr_count($content, " style='color:red;'> "));
            $this->assertContains("http://www.sourceforge11.org", $content);
            $this->assertEquals(2, substr_count($content, 'http://www.sourceforge11.org'));
            $this->assertNotContains(" href='http://www.sourceforge12.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge13.org'", $content);
            $this->assertNotContains(" href='http://www.sourceforge14.org'", $content);
            $this->assertContains("http://www.sourceforge12.org", $content);
            $this->assertContains("http://www.sourceforge13.org", $content);
            $this->assertContains("http://www.sourceforge14.org", $content);
            $this->assertContains("<p>Link15: <a href='#localanchor'>New</a></p>", $content);
            $this->assertNotContains("http://www.sourceforge16.org/projects#promoted", $content);
            $this->assertContains("http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png", $content);
            $this->assertEquals(1, substr_count($content,
                                                    'http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png'));
            $this->assertContains("http://www.zurmo.com/icon.png", $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo.com/icon.png'));
            $this->assertContains("http://www.zurmo.com/css/keyframes.css", $content);
            $this->assertEquals(1, substr_count($content, 'http://www.zurmo.com/css/keyframes.css'));
            $this->assertContains("http://www.zurmo.com/zurmo/app/index.php/min/serve/g/css/lm/1366956624", $content);
            $this->assertEquals(1, substr_count($content,
                                            'http://www.zurmo.com/zurmo/app/index.php/min/serve/g/css/lm/1366956624'));
            $this->assertContains("http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697759", $content);
            $this->assertEquals(1,
                    substr_count($content, 'http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697759'));
            $this->assertContains("http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697751.js", $content);
            $this->assertEquals(1,
                    substr_count($content, 'http://www.zurmo.com/zurmo/app/index.php/min/serve/g/js/lm/1366697751.js'));
            $this->assertContains('<img width="1" height="1" src=', $content);
            $this->assertEquals(1, substr_count($content, '<img width="1" height="1" src='));
            $this->assertContains('/marketingLists/external/', $content);
            $this->assertEquals(2, substr_count($content, '/marketingLists/external/'));
        }

        public function testResolveQueryStringArrayForHashWithAndWithoutUrlInQueryString()
        {
            // test without url
            $modelId                    = 1;
            $modelType                  = 'AutoresponderItem';
            $personId                   = 10;

            $className                  = 'EmailMessageActivityUtil';
            $resolveBaseQueryStringArrayFunction = static::getProtectedMethod('ContentTrackingUtil', 'resolveBaseQueryStringArray');
            $withoutUrlQueryStringArray = $resolveBaseQueryStringArrayFunction->invokeArgs(null,
                                                                                            array($modelId,
                                                                                                $modelType,
                                                                                                $personId));
            $this->assertNotEmpty($withoutUrlQueryStringArray);
            $this->assertCount(3, $withoutUrlQueryStringArray);
            $this->assertArrayHasKey('modelId', $withoutUrlQueryStringArray);
            $this->assertArrayHasKey('modelType', $withoutUrlQueryStringArray);
            $this->assertArrayHasKey('personId', $withoutUrlQueryStringArray);
            $withoutUrlQueryStringArrayHash = StringUtil::resolveHashForQueryStringArray($withoutUrlQueryStringArray);
            $withoutUrlQueryStringArrayDecoded = $className::resolveQueryStringArrayForHash($withoutUrlQueryStringArrayHash);
            $this->assertTrue(is_array($withoutUrlQueryStringArrayDecoded));
            $this->assertCount(5, $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('modelId', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('modelType', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('personId', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('url', $withoutUrlQueryStringArrayDecoded);
            $this->assertArrayHasKey('type', $withoutUrlQueryStringArrayDecoded);
            $this->assertEquals($withoutUrlQueryStringArray['modelId'], $withoutUrlQueryStringArrayDecoded['modelId']);
            $this->assertEquals($withoutUrlQueryStringArray['modelType'], $withoutUrlQueryStringArrayDecoded['modelType']);
            $this->assertEquals($withoutUrlQueryStringArray['personId'], $withoutUrlQueryStringArrayDecoded['personId']);
            $this->assertNull($withoutUrlQueryStringArrayDecoded['url']);
            $this->assertNull($withoutUrlQueryStringArrayDecoded['type']);

            // try same thing with url in the query string array.
            $withUrlQueryStringArray = CMap::mergeArray($withoutUrlQueryStringArray,
                                                                    array('url'     => 'http://www.zurmo.com',
                                                                            'type'  => null));
            $withUrlQueryStringArrayHash = StringUtil::resolveHashForQueryStringArray($withUrlQueryStringArray);
            $withUrlQueryStringArrayDecoded = $className::resolveQueryStringArrayForHash($withUrlQueryStringArrayHash);
            $this->assertEquals($withUrlQueryStringArray, $withUrlQueryStringArrayDecoded);
        }

        public function testTextContentGetsCustomFooterAppended()
        {
            GlobalMarketingFooterUtil::setContentByType('PlainTextFooter', false);
            $content    = 'This is some text content';
            $result     = static::resolveContent($content, true, false);
            $this->assertTrue($result);
            $this->assertContains('This is some text content', $content);
            $this->assertContains('PlainTextFooter', $content);
            $this->assertNotContains('/marketingLists/external/', $content);
        }

        /**
         * @depends testTextContentGetsCustomFooterAppended
         */
        public function testHtmlContentGetsCustomFooterAppended()
        {
            GlobalMarketingFooterUtil::setContentByType('RichTextFooter', true);
            $content    = 'This is some html content';
            $result     = static::resolveContent($content, true, true);
            $this->assertTrue($result);
            $this->assertContains('This is some html content', $content);
            $this->assertContains('RichTextFooter', $content);
            $this->assertNotContains('/marketingLists/external/', $content);
        }

        protected static function resolveContent(& $content, $tracking = true, $isHtmlContent = true)
        {
            return (GlobalMarketingFooterUtil::resolveContentGlobalFooter($content, $isHtmlContent) &&
                    static::resolveContentForMergeTags($content) &&
                    ContentTrackingUtil::resolveContentForTracking($tracking, $content, 1, 'AutoresponderItem',
                                                                                1, $isHtmlContent));
        }

        protected static function resolveContentForMergeTags(& $content)
        {
            $language               = null;
            $errorOnFirstMissing    = true;
            $templateType           = EmailTemplate::TYPE_CONTACT;
            $invalidTags            = array();
            $textMergeTagsUtil      = MergeTagsUtilFactory::make($templateType, $language, $content);
            $params                 = GlobalMarketingFooterUtil::resolveFooterMergeTagsArray(1, 2, 3,
                                                                                            'AutoresponderITem', true,
                                                                                            false);
            $content                = $textMergeTagsUtil->resolveMergeTags(Yii::app()->user->userModel,
                                                                            $invalidTags,
                                                                            $language,
                                                                            $errorOnFirstMissing,
                                                                            $params);
            return ($content !== false);
        }
    }
?>