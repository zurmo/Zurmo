<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class DropDownDependencyAttributeFormTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setup();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetAndGetDropDownDependencyAttribute()
        {
            $mappingData = array('mappingData' => array(
                           array('attributeName'        => 'testCountry'),
                           array('attributeName'        => 'testState',
                                 'valuesToParentValues' => array('aaa1' => 'aaaa',
                                                                 'aaa2' => 'aaaa',
                                                                 'bbb1' => 'bbbb',
                                                                 'bbb2' => 'bbbb',
                                                           )
                           ),
                           array('attributeName'        => null,
                                 'valuesToParentValues' => array('aa1' => 'aaa1',
                                                                 'ab1' => 'aaa1',
                                                                 'aa2' => 'aaa2',
                                                                 'ab2' => 'aaa2',
                                                                 'ba1' => 'bbb1',
                                                                 'bb1' => 'bbb1',
                                                                 'ba2' => 'bbb2',
                                                                 'bb2' => 'bbb2'
                                                           )
                           ),
                           array('attributeName' => '')
                           ));

            $testMappingData = array(
                               array('attributeName'        => 'testCountry'),
                               array('attributeName'        => 'testState',
                                     'valuesToParentValues' => array('aaa1' => 'aaaa',
                                                                     'aaa2' => 'aaaa',
                                                                     'bbb1' => 'bbbb',
                                                                     'bbb2' => 'bbbb',
                                                              )
                               ),
                               array('attributeName'        => null),
                               array('attributeName'        => '')
                               );
            $attributeName = "testLocation";
            $attributeForm = new DropDownDependencyAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->mappingData      = $mappingData['mappingData'];

            $attributeForm->sanitizeFromPostAndSetAttributes($mappingData);
            $this->assertEquals($testMappingData, $attributeForm->mappingData);
        }
    }
?>