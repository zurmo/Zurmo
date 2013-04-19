<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Test class to test out the GeneralCache
     */
    class GeneralCacheTest extends BaseTest
    {
        public function tearDown()
        {
            GeneralCache::forgetAll();
        }

        public function testCanSetNullValueToCache()
        {
            // If memcache is off this test will fail because memcache will not cache null values.
            if (MEMCACHE_ON)
            {
                GeneralCache::cacheEntry('somethingForTesting', null);
                $value = GeneralCache::getEntry('somethingForTesting');
                $this->assertNull($value);
            }
        }

        /**
         * @depends testCanSetNullValueToCache
         */
        public function testCanSetValueToCache()
        {
            if (MEMCACHE_ON)
            {
                GeneralCache::cacheEntry('somethingForTesting2', 5);
                $value = GeneralCache::getEntry('somethingForTesting2');
                $this->assertEquals(5, $value);
            }
        }

        /**
         * @depends testCanSetValueToCache
         */
        public function testForgetEntry()
        {
            if (MEMCACHE_ON)
            {
                GeneralCache::cacheEntry('somethingForTesting3', 10);
                $value = GeneralCache::getEntry('somethingForTesting3');
                $this->assertEquals(10, $value);

                GeneralCache::forgetEntry('somethingForTesting3');
                try
                {
                    GeneralCache::getEntry('somethingForTesting3');
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    $this->assertTrue(true);
                }
            }
        }

        /**
         * @depends testCanSetValueToCache
         */
        public function testForgetAll()
        {
            if (MEMCACHE_ON)
            {
                GeneralCache::forgetAll();
                try
                {
                    GeneralCache::getEntry('somethingForTesting2');
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    $this->assertTrue(true);
                }
            }
        }

        public function testForgetAllNotDeleteOtherDataFromCache()
        {
            if (MEMCACHE_ON && !PHP_CACHING_ON)
            {
                GeneralCache::cacheEntry('somethingForTesting4', 34);
                $value = GeneralCache::getEntry('somethingForTesting4');
                $this->assertEquals(34, $value);

                $originalAdditionalStringForCachePrefix = GeneralCache::getAdditionalStringForCachePrefix();
                GeneralCache::setAdditionalStringForCachePrefix('ATEST');
                GeneralCache::cacheEntry('somethingForTesting4', 43);
                $value = GeneralCache::getEntry('somethingForTesting4');
                $this->assertEquals(43, $value);

                GeneralCache::forgetAll();
                try
                {
                    GeneralCache::getEntry('somethingForTesting4');
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    $this->assertTrue(true);
                }

                GeneralCache::setAdditionalStringForCachePrefix($originalAdditionalStringForCachePrefix);
                $value = GeneralCache::getEntry('somethingForTesting4');
                $this->assertEquals(34, $value);
            }
        }
    }
?>
