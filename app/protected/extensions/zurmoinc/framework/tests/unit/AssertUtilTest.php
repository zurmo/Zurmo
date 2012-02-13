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

    if (assert_options(ASSERT_ACTIVE))
    {
        class AssertUtilTest extends BaseTest
        {
            public function testAssertUtilAllAndAny()
            {
                $names1   = array('bill', 'ben');
                $names2   = array('bill', 1);
                $names3   = array(0, 1);
                $letters1 = array('a', 'b', 'c', 'd');
                $letters2 = array('a', 'B', 'c', 'd');
                $letters3 = array(1, 2, 'a');
                $letters4 = array(1, 2, 3);

                $this->assertTrue (AssertUtil::all($names1,   'is_string'));
                $this->assertFalse(AssertUtil::all($names2,   'is_string'));
                $this->assertFalse(AssertUtil::all($names3,   'is_string'));
                $this->assertFalse(AssertUtil::all($names1,   'isA', 'AssertUtilTest'));
                $this->assertTrue (AssertUtil::all($letters1, 'ctype_lower'));
                $this->assertFalse(AssertUtil::all($letters2, 'ctype_lower'));
                $this->assertFalse(AssertUtil::all($letters3, 'ctype_lower'));
                $this->assertFalse(AssertUtil::all($letters4, 'ctype_lower'));
                $this->assertFalse(AssertUtil::all($letters1, 'isA', 'AssertUtilTest'));

                $this->assertTrue (AssertUtil::any($names1,   'is_string'));
                $this->assertTrue (AssertUtil::any($names2,   'is_string'));
                $this->assertFalse(AssertUtil::any($names3,   'is_string'));
                $this->assertFalse(AssertUtil::any($names1,   'isA', 'AssertUtilTest'));
                $this->assertTrue (AssertUtil::any($letters1, 'ctype_lower'));
                $this->assertTrue (AssertUtil::any($letters2, 'ctype_lower'));
                $this->assertTrue (AssertUtil::any($letters3, 'ctype_lower'));
                $this->assertFalse(AssertUtil::any($letters4, 'ctype_lower'));
                $this->assertTrue (AssertUtil::any($letters1, 'isA', 'AssertUtilTest'));
            }

            public static function isA($value)
            {
                return $value == 'a';
            }
        }
    }
?>
