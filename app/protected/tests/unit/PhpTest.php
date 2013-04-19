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

    class EmptyClass
    {
    }

    class Fruit
    {
        public static function getName()
        {
            return 'Fruit';
        }
    };

    class Apple extends Fruit
    {
        public static function getName()
        {
            return parent::getName() . ': Apple';
        }
    };

    class Banana
    {
        private $magic = array();

        public function propertyExists()
        {
            return property_exists($this, 'magic');
        }
    }

    class Bananarama extends Banana
    {
    }

    // This is for testing how Php does things that things
    // in our code must match in terms of behaviour.
    class PhpTest extends BaseTest
    {
        // Defines how Model::testUsingIssetOrEmptyOnPropertiesOfObject should behave.
        public function testEmptyAndIsSetBehaviour()
        {
            $empty = new EmptyClass();
            $this->assertTrue(!isset($empty->nonExistent));

            $empty->nowExisting = 1;
            $this->assertTrue( isset($empty->nowExisting));
            $this->assertTrue(!empty($empty->nowExisting));

            unset($empty->nowExisting);
            $this->assertTrue(!isset($empty->nowExisting));
            $this->assertTrue( empty($empty->nowExisting));

            $empty->empty = '';
            $this->assertTrue( isset($empty->empty));
            $this->assertTrue( empty($empty->empty));

            $empty->empty = 0;
            $this->assertTrue( isset($empty->empty));
            $this->assertTrue( empty($empty->empty));

            $empty->empty = null;
            $this->assertTrue(!isset($empty->empty));
            $this->assertTrue( empty($empty->empty));
        }

        public function testReplaceStaticMethod()
        {
            $this->assertFalse(Account::mangleTableName());
            $this->assertFalse(Person ::mangleTableName());
            $this->assertTrue (User   ::mangleTableName());

            $className = 'Account';
            $this->assertFalse($className::mangleTableName());
            $className = 'Person';
            $this->assertFalse($className::mangleTableName());
            $className = 'User';
            $this->assertTrue ($className::mangleTableName());
        }

        public function testCallingParentOnStatics()
        {
            $this->assertEquals('Fruit',        Fruit::getName());
            $this->assertEquals('Fruit: Apple', Apple::getName());
        }

        // This test fails in Php 5.3.1, tested on Windows,
        // due to http://bugs.php.net/50810
        // It works in Php 5.3.3-7 tested in Linux.
        public function testPropertyExists()
        {
            $bananarama = new Bananarama();
            $this->assertTrue($bananarama->propertyExists());
        }

        public function testPhpSloppiness()
        {
            $this->assertEquals(24,  8  |  16 );
            $this->assertEquals(24,  8  | '16');
            $this->assertEquals(96, '8' | '16');
        }
    }
?>
