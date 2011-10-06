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

    /**
     * Currency model.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class CurrencySuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $currencies = Currency::getAll();
            $this->assertEquals(1, count($currencies));
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/currency');
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/currency/configurationList');

            //Test first a bad currency code.
            $this->setGetArray(array('Currency' => array(
                'code' => 'BAD')));
            $this->runControllerWithNoExceptionsAndGetContent     ('zurmo/currency/configurationList');
            //Secondly test a valid currency code.
            $this->resetGetArray();
            $this->setPostArray(array('Currency' => array(
                'code' => 'EUR')));
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/currency/configurationList');
            //Test that the currency is actually saved.
            $currencies = Currency::getAll();
            $this->assertEquals(2, count($currencies));

            //Now delete the newly created currency.
            $currency = Currency::getByCode('EUR');
            $this->setGetArray(array('id' => $currency->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/currency/delete');
            //Confirm there is only one currency left.
            $currencies = Currency::getAll();
            $this->assertEquals(1, count($currencies));
        }

        public function testSuperUserModifyActiveCurrenciesInCollection()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Create second currency.
            $currency = new Currency();
            $currency->code = 'EUR';
            $currency->rateToBase = 1.5;
            $saved = $currency->save();
            $this->assertTrue($saved);
            $EURCurrencyId = $currency->id;
            $currencies = Currency::getAll();
            $this->assertEquals(2, count($currencies));
            $this->assertEquals(1, $currencies[0]->active);
            $this->assertEquals(1, $currencies[1]->active);

            //Make EUR inactive.
            $this->resetGetArray();
            $this->setPostArray(array('CurrencyCollection' => array(
                'EUR' => array('active' => ''), 'USD' => array('active' => '1'))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/currency/configurationList');
            $this->assertTrue(strpos($content, 'Changes to active currencies changed successfully.') !==false);

            //Confirm that the EUR is inactive and the USD is still active.
            $currency = Currency::getByCode('EUR');
            $this->assertEquals(0, $currency->active);
            $currency = Currency::getByCode('USD');
            $this->assertEquals(1, $currency->active);

            //Attempt to also make the USD inactive, this should fail since at least one currency must be active.
            $this->resetGetArray();
            $this->setPostArray(array('CurrencyCollection' => array(
                'EUR' => array('active' => ''), 'USD' => array('active' => ''))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/currency/configurationList');
            $this->assertTrue(strpos($content, 'You must have at least one active currency.') !==false);

            //Confirm that the EUR is inactive and the USD is still active.
            $currency = Currency::getByCode('EUR');
            $this->assertEquals(0, $currency->active);
            $currency = Currency::getByCode('USD');
            $this->assertEquals(1, $currency->active);
        }
    }
?>