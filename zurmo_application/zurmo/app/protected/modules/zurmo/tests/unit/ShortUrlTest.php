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
    class ShortUrlTest extends ZurmoBaseTest
    {
        public static function getDependentTestModelClassNames()
        {
            return array('ShortUrlForTest');
        }

        public function testResolveHashByUrl()
        {
            $url  = 'http://www.zurmo.com';
            $hash = ShortUrl::resolveHashByUrl($url);
            $this->assertEquals(ShortUrl::HASH_LENGTH, strlen($hash));
            $shortUrls = ShortUrl::getAll();
            $this->assertCount (1,    $shortUrls);
            $this->assertEquals($url, $shortUrls[0]->url);

            //The same url should return the same hash and not create a new one
            $hash2 = ShortUrl::resolveHashByUrl($url);
            $this->assertEquals($hash, $hash2);
            $this->assertCount (1,     ShortUrl::getAll());

            //New url should have new hash
            $url2  = 'http://www.zurmo.org';
            $hash2 = ShortUrl::resolveHashByUrl($url2);
            $this->assertNotEquals($hash, $hash2);
            $this->assertCount (2,     ShortUrl::getAll());

            ShortUrl::deleteAll();
        }

        public function testGetHashByUrl()
        {
            $url  = 'http://www.zurmo.com';
            try
            {
                ShortUrl::getHashByUrl($url);
                $this->assertTrue(false);
            }
            catch (NotFoundException $exception)
            {
                $this->assertTrue(true);
            }

            $shortUrl = new ShortUrl();
            $shortUrl->hash = 'abcdefghij';
            $shortUrl->url  = $url;
            $shortUrl->save();
            $hash = ShortUrl::getHashByUrl($url);
            $this->assertEquals('abcdefghij', $hash);

            ShortUrl::deleteAll();
        }

        public function testDeleteOld()
        {
            $shortUrlForTest = new ShortUrlForTest();
            $shortUrlForTest->setStubCreatedDateTime(time() - 200 * 24 * 60 * 60);
            $shortUrlForTest->hash = 'abcdefghij';
            $shortUrlForTest->url  = 'http://www.zurmo.com';
            $this->assertTrue($shortUrlForTest->save());

            $shortUrlForTest2 = new ShortUrlForTest();
            $shortUrlForTest2->setStubCreatedDateTime(time() - 3 * 24 * 60 * 60);
            $shortUrlForTest2->hash = '1234567890';
            $shortUrlForTest2->url  = 'http://www.zurmo.org';
            $this->assertTrue($shortUrlForTest2->save());

            $this->assertEquals(2, $shortUrlForTest::getCount());
            ShortUrlForTest::deleteOld();
            $this->assertEquals(1, $shortUrlForTest::getCount());
            $shortUrlsForTest = ShortUrlForTest::getAll();
            $this->assertEquals($shortUrlForTest2, $shortUrlsForTest[0]);
        }
    }
?>