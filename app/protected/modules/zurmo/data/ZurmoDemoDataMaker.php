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
     * Class that builds zurmo demo data models.
     */
    class ZurmoDemoDataMaker extends DemoDataMaker
    {
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');

            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 1.5;
            $saved = $currency->save();
            assert('$saved');
            $currency = new Currency();
            $currency->code       = 'CAD';
            $currency->rateToBase = 1.1;
            $saved = $currency->save();
            assert('$saved');
            $currency = new Currency();
            $currency->code       = 'JPY';
            $currency->rateToBase = .75;
            $saved = $currency->save();
            assert('$saved');

            $currencies = Currency::getAll('id');
            $demoDataHelper->setRangeByModelName('Currency', $currencies[0]->id, $currencies[count($currencies)-1]->id);
        }

        public function populateModel(& $model)
        {
            throw notImplementedException();
        }
    }
?>