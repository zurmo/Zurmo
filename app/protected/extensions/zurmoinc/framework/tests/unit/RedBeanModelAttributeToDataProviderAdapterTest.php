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
    }
?>
