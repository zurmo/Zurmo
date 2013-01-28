<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
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
