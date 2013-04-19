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

    class DateTimeUserElementTest extends ZurmoBaseTest
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

        public function testDateTimeCreatedUserElement()
        {
            $model = new TestDateTimeUserModel();
            $model->createdDateTime = '2012-02-24 13:05:32';
            $createdDateTimeUserElement = new DateTimeCreatedUserElement($model, 'null');

            date_default_timezone_set('EST');
            $content = $createdDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('UTC');
            $content = $createdDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 1:05 PM') !== false);

            date_default_timezone_set('US/Eastern');
            $content = $createdDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('America/New_York');
            $content = $createdDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('Europe/Madrid');
            $content = $createdDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 2:05 PM') !== false);

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $content = $createdDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 PM') !== false);
        }

        public function testDateTimeModifiedUserElement()
        {
            $model      = new TestDateTimeUserModel();
            $model->modifiedDateTime = '2012-02-24 13:05:32';
            $modifiedDateTimeUserElement = new DateTimeModifiedUserElement($model, 'null');

            date_default_timezone_set('EST');
            $content    = $modifiedDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('UTC');
            $content    = $modifiedDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 1:05 PM') !== false);

            date_default_timezone_set('US/Eastern');
            $content    = $modifiedDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('America/New_York');
            $content    = $modifiedDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 AM') !== false);

            date_default_timezone_set('Europe/Madrid');
            $content    = $modifiedDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 2:05 PM') !== false);

            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $content    = $modifiedDateTimeUserElement->render();
            $this->assertTrue(stripos($content, '2/24/12 8:05 PM') !== false);
        }
    }
?>
