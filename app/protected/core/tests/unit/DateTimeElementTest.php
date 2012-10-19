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
    class DateTimeElementTest extends ZurmoBaseTest
    {
        protected $defaultTimeZone;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->defaultTimeZone = date_default_timezone_get();
        }

        public function teardown()
        {
            date_default_timezone_set($this->defaultTimeZone);
            parent::teardown();
        }

        public function testRender()
        {
            $model              = new TestDateTimeModel();
            $model->myDateTime  = '2012-02-24 13:05:32';
            $dateTimeElement    = new DateTimeElement($model, 'myDateTime');

            date_default_timezone_set('EST');
            $content            = $dateTimeElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('UTC');
            $content            = $dateTimeElement->render();
            $this->assertTrue(stripos($content, '2/24/12 1:05 PM') !== false);

            date_default_timezone_set('US/Eastern');
            $content            = $dateTimeElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM')  !== false);

            date_default_timezone_set('America/New_York');
            $content            = $dateTimeElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM')  !== false);

            date_default_timezone_set('Europe/Madrid');
            $content            = $dateTimeElement->render();
            $this->assertTrue(stripos($content, '2/24/12 2:05 PM')  !== false);

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $content = $dateTimeElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 PM')  !== false);
        }
    }
?>
