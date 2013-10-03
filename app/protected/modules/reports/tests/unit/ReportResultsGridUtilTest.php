<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ReportResultsGridUtilTest extends ZurmoBaseTest
    {
        public $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
        }

        public function setup()
        {
            parent::setUp();
            $this->user = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testMakeStringForMultipleLinks()
        {
            $account1 = AccountTestHelper::createAccountByNameForOwner('account1', $this->user);
            $result   = ReportResultsGridUtil::makeStringForMultipleLinks('account1', 'Account', 'AccountsModule');
            $this->assertContains   ('a target="new"', $result);
            $this->assertNotContains('tooltip',        $result);

            $account2 = AccountTestHelper::createAccountByNameForOwner('account1', $this->user);
            $result   = ReportResultsGridUtil::makeStringForMultipleLinks('account1', 'Account', 'AccountsModule');
            $this->assertContains('<span class="tooltip">2</span>', $result);
        }

        /**
         * @depends testMakeStringForMultipleLinks
         */
        public function testMakeStringForLinkOrLinks()
        {
            $accounts = Account::getByName('account1');
            $account1 = $accounts[0];
            $account2 = $accounts[1];

            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->setModelAliasUsingTableAliasName('abc');
            $displayAttribute->attributeIndexOrDerivedType = 'name';

            $reportResultsRowData = new ReportResultsRowData(array($displayAttribute), 4);
            $reportResultsRowData->addModelAndAlias($account2, 'abc');
            $result = ReportResultsGridUtil::makeStringForLinkOrLinks('attribute0', $reportResultsRowData, true, 'account1');
            $this->assertContains('<span class="tooltip">2</span>', $result);

            $result = ReportResultsGridUtil::makeStringForLinkOrLinks('attribute0', $reportResultsRowData, false, 'account1');
            $this->assertContains   ('a target="new"', $result);
            $this->assertContains   ('id=' . $account2->id, $result);
            $this->assertNotContains('tooltip',        $result);
        }
    }
?>
