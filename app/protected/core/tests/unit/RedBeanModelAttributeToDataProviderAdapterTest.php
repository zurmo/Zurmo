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

    class RedBeanModelAttributeToDataProviderAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testAllMethodsOnAttributeOnSameModel()
        {
            $adapter = new RedBeanModelAttributeToDataProviderAdapter('A', 'a');
            $this->assertEquals('A',  $adapter->getModelClassName());
            $this->assertEquals('a',  $adapter->getAttribute());
            $this->assertEquals(null, $adapter->getRelatedAttribute());
            $this->assertEquals('a',  $adapter->getModelTableName());
            $this->assertEquals('A',  $adapter->getAttributeModelClassName());
            $this->assertEquals('a',  $adapter->getAttributeTableName());
            $this->assertEquals('a',  $adapter->getColumnName());
            $this->assertFalse(       $adapter->isRelation());
            $this->assertFalse(       $adapter->hasRelatedAttribute());
        }

        public function testAllMethodsOnAttributeOnCastedUpModel()
        {
            $adapter = new RedBeanModelAttributeToDataProviderAdapter('B', 'a');
            $this->assertEquals('B',  $adapter->getModelClassName());
            $this->assertEquals('a',  $adapter->getAttribute());
            $this->assertEquals(null, $adapter->getRelatedAttribute());
            $this->assertEquals('b',  $adapter->getModelTableName());
            $this->assertEquals('A',  $adapter->getAttributeModelClassName());
            $this->assertEquals('a',  $adapter->getAttributeTableName());
            $this->assertEquals('a',  $adapter->getColumnName());
            $this->assertFalse(       $adapter->isRelation());
            $this->assertFalse(       $adapter->hasRelatedAttribute());
        }

        public function testAllMethodsOnAttributeOnSameModelAndRelatedAttribute()
        {
            $adapter = new RedBeanModelAttributeToDataProviderAdapter('I', 'j', 'jMember');
            $this->assertEquals('I',        $adapter->getModelClassName());
            $this->assertEquals('j',        $adapter->getAttribute());
            $this->assertEquals('jMember',  $adapter->getRelatedAttribute());
            $this->assertEquals('i',        $adapter->getModelTableName());
            $this->assertEquals('I',        $adapter->getAttributeModelClassName());
            $this->assertEquals('i',        $adapter->getAttributeTableName());
            $this->assertEquals('j_id',     $adapter->getColumnName());
            $this->assertTrue(              $adapter->isRelation());
            $this->assertTrue(              $adapter->hasRelatedAttribute());
            $this->assertEquals('J',        $adapter->getRelationModelClassName());
            $this->assertEquals('J',        $adapter->getRelatedAttributeModelClassName());
            $this->assertEquals('j',        $adapter->getRelationTableName());
            $this->assertEquals('j',        $adapter->getRelatedAttributeTableName());
            $this->assertEquals('jmember',  $adapter-> getRelatedAttributeColumnName());
            $this->assertFalse(             $adapter->isRelatedAttributeRelation());
        }

        public function testRelatedAttributesSortUsesTwoAttributes()
        {
            $adapter = new RedBeanModelAttributeToDataProviderAdapter('A', 'a');
            $this->assertFalse(         $adapter->sortUsesTwoAttributes());
            $this->assertEquals('a',    $adapter->getColumnNameByPosition(0));
            try
            {
                $adapter->getColumnNameByPosition(1);
            }
            catch (InvalidArgumentException $exception)
            {
                $this->assertEquals('Attribute position is not valid', $exception->getMessage());
            }

            $adapter = new RedBeanModelAttributeToDataProviderAdapter('Q', 'a');
            $this->assertTrue(                         $adapter->sortUsesTwoAttributes());
            $this->assertEquals('a',                   $adapter->getColumnNameByPosition(0));
            $this->assertEquals('junk',                $adapter->getColumnNameByPosition(1));
            $this->assertEquals('uniquerequiredemail', $adapter->getColumnNameByPosition(2));
            try
            {
                $adapter->getColumnNameByPosition(3);
            }
            catch (InvalidArgumentException $exception)
            {
                $this->assertEquals('Attribute position is not valid', $exception->getMessage());
            }

            $adapter = new RedBeanModelAttributeToDataProviderAdapter('QQ', 'q');
            $this->assertTrue(                         $adapter->relatedAttributesSortUsesTwoAttributes());
            $this->assertEquals('q_id',                $adapter->getColumnNameByPosition(0));
            $this->assertEquals('junk',                $adapter->getColumnNameByPosition(1));
            $this->assertEquals('uniquerequiredemail', $adapter->getColumnNameByPosition(2));

            $adapter = new RedBeanModelAttributeToDataProviderAdapter('QQ', 'qRequired');
            $this->assertTrue(                         $adapter->relatedAttributesSortUsesTwoAttributes());
            $this->assertEquals('erequired_q_id',      $adapter->getColumnNameByPosition(0));
            $this->assertEquals('junk',                $adapter->getColumnNameByPosition(1));
            $this->assertEquals('uniquerequiredemail', $adapter->getColumnNameByPosition(2));

            $adapter = new RedBeanModelAttributeToDataProviderAdapter('QQ', 'qUnique');
            $this->assertTrue(                         $adapter->relatedAttributesSortUsesTwoAttributes());
            $this->assertEquals('eunique_q_id',        $adapter->getColumnNameByPosition(0));
            $this->assertEquals('junk',                $adapter->getColumnNameByPosition(1));
            $this->assertEquals('uniquerequiredemail', $adapter->getColumnNameByPosition(2));

            $adapter = new RedBeanModelAttributeToDataProviderAdapter('QQ', 'qMany');
            $this->assertTrue(                         $adapter->relatedAttributesSortUsesTwoAttributes());
            $this->assertEquals('q_id',                $adapter->getColumnNameByPosition(0));
            $this->assertEquals('junk',                $adapter->getColumnNameByPosition(1));
            $this->assertEquals('uniquerequiredemail', $adapter->getColumnNameByPosition(2));
        }
    }
?>