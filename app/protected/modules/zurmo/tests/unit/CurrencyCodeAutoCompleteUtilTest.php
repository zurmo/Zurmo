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

    class CurrencyCodeAutoCompleteUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetByPartialName()
        {
            //Search by code expecting multiple results
            $data = CurrencyCodeAutoCompleteUtil::getByPartialCodeOrName('EU');
            $compareData = array(
                array(
                    'value' => 'EUR',
                    'label' => 'EUR Euro Member Countries',
                ),
                array(
                    'value' => 'MDL',
                    'label' => 'MDL Moldova Leu',
                ),
                array(
                    'value' => 'RON',
                    'label' => 'RON Romania New Leu',
                ),
            );
            $this->assertEquals($compareData, $data);

            //Search by code expecting multiple results, but lowercase. Should produce same results.
            $data = CurrencyCodeAutoCompleteUtil::getByPartialCodeOrName('eu');
            $this->assertEquals($compareData, $data);

            //Search by invalid code or name.
            $data = CurrencyCodeAutoCompleteUtil::getByPartialCodeOrName('exsur');
            $this->assertEquals(array(), $data);

            //Search by valid partial name.
            $compareData = array(
                array(
                    'value' => 'FKP',
                    'label' => 'FKP Falkland Islands (Malvinas) Pound',
                ),
            );
            $data = CurrencyCodeAutoCompleteUtil::getByPartialCodeOrName('falk');
            $this->assertEquals($compareData, $data);
        }
    }
?>
