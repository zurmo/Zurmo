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

    class RedBeanModelsCacheTest extends BaseTest
    {
        public function testCaching()
        {
            $a = new A();
            $a->a = 1;
            $a->uniqueRequiredEmail = 'a@zurmoinc.com';
            $this->assertTrue($a->save());
            $originalAHash = spl_object_hash($a);

            $id = $a->id;
            unset($a);

            $a = A::getById($id);
            $fromPhpAHash = spl_object_hash($a);
            unset($a);

            RedBeanModelsCache::forgetAll(true);
            $a = A::getById($id);
            $this->assertEquals(1,                $a->a);
            $this->assertEquals('a@zurmoinc.com', $a->uniqueRequiredEmail);
            $fromMemcacheAHash = spl_object_hash($a);

            $this->assertEquals   ($fromPhpAHash,      $originalAHash);
            $this->assertNotEquals($fromMemcacheAHash, $originalAHash);
        }
    }
?>
