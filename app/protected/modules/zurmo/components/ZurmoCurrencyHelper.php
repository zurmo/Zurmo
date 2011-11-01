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
     * Application loaded component at run time. @see BeginBehavior - calls load() method.
     */
    class ZurmoCurrencyHelper extends CApplicationComponent
    {
        const ERROR_INVALID_CODE = 1;

        const ERROR_WEB_SERVICE  = 2;

        /**
         * Base currency all currency values have rates against.
         * ISO-4217 code.
         */
        protected $_baseCode;

        /**
         * Which service type to use: GrandTotal(default) or WebServiceX
         * It can be changed in config
         * @var string
         */
        protected $_serviceType;

        /**
         * Currency service utility class, based on $_serviceType
         * @var CurrencyServiceUtil
         */
        protected $currencyService;

        /**
         * This is set from the value in the application common config file.
         */
        public function setBaseCode($value)
        {
            assert('is_string($value)');
            $this->_baseCode = $value;
        }

        public function getBaseCode()
        {
            return $this->_baseCode;
        }

        /**
        * This is set from the value in the application common config file.
        */
        public function setServiceType($value)
        {
            assert('is_string($value)');
            $this->_serviceType = $value;
            $this->setCurrencyService();
        }

        public function getServiceType()
        {
            return $this->_serviceType;
        }

        protected function setCurrencyService()
        {
            $className = $this->getServiceType() . 'CurrencyServiceUtil';
            assert('class_exists($className)');
            $this->currencyService = new $className;
        }

        /**
         * Resolve the active currency for the current user.  If the user does not have a currency, it will fall back
         * to the base system currency.  If the base system currency does not exist, it will attempt to make it.
         * @throws NotSupportedException
         */
        public function getActiveCurrencyForCurrentUser()
        {
            if (Yii::app()->user->userModel->currency->id > 0)
            {
                return Yii::app()->user->userModel->currency;
            }
            try
            {
                $currency = Currency::getByCode($this->getBaseCode());
            }
            catch (NotFoundException $e)
            {
                $currency = Currency::makeBaseCurrency();
            }
            if ($currency->id <= 0)
            {
                throw new NotSupportedException();
            }
            return $currency;
        }

        public function getCodeForCurrentUserForDisplay()
        {
            $code = Yii::app()->user->userModel->currency->code;
            if ($code == null)
            {
                return $this->getBaseCode();
            }
            return $code;
        }

        /**
         * Get the conversion rate from the supplied currency code to the base currency.
         * @param int $fromCode;
         */
        public function getConversionRateToBase($fromCode)
        {
            if ($fromCode == $this->getBaseCode())
            {
                return 1;
            }
            $rate = $this->currencyService->getConversionRateViaWebService($fromCode, $this->getBaseCode());
            if ($rate == null)
            {
                return 1;
            }
            return $rate;
        }

        public function getWebServiceErrorMessage()
        {
            return $this->currencyService->getWebServiceErrorMessage();
        }

        public function getWebServiceErrorCode()
        {
            return $this->currencyService->getWebServiceErrorCode();
        }

        /**
         * Check if the currency rate has been updated within the last 24 hours. If not, then perform a currency
         * update and update the lastAttemptedRateUpdateTimeStamp.
         */
        public function checkAndUpdateCurrencyRates()
        {
            $metadata = Currency::getMetadata();
            if ( $metadata['Currency']['lastAttemptedRateUpdateTimeStamp'] == null ||
                (time() - $metadata['Currency']['lastAttemptedRateUpdateTimeStamp']) > (24 * 60 * 60))
            {
                //code and message or just code ? hmm.
                $currencies = Currency::getAll();
                foreach ($currencies as $currency)
                {
                    if ($currency->code != $this->getBaseCode())
                    {
                        $currency->rateToBase = $this->getConversionRateToBase($currency->code);
                        assert('$currency->rateToBase == null || is_numeric($currency->rateToBase)');
                        $currency->save();
                        //todo: add error message if save fails for some reason.
                    }
                }
                $metadata['Currency']['lastAttemptedRateUpdateTimeStamp'] = time();
                Currency::setMetadata($metadata);
            }
        }

        /**
         * Given a selectedCurrencyId, return an array of available currencies for selection in the user interface.
         * If the selected currency is inactive, include this in the returned data.
         * @param mixed $selectedCurrencyId
         */
        public function getActiveCurrenciesOrSelectedCurrenciesData($selectedCurrencyId)
        {
            assert('$selectedCurrencyId == null || (is_int($selectedCurrencyId) && $selectedCurrencyId > 0)');
            $currencies = Currency::getAll();
            $data       = array();
            foreach ($currencies as $currency)
            {
                if ($currency->active || ($selectedCurrencyId != null && $currency->id == $selectedCurrencyId))
                {
                    $data[$currency->id] = $currency->code;
                }
            }
            return $data;
        }

        /**
         * @return Date/Time of the last attempted rate update.
         */
        public function getLastAttemptedRateUpdateDateTime()
        {
            $metadata = Currency::getMetadata();
            if ($metadata['Currency']['lastAttemptedRateUpdateTimeStamp'] == null)
            {
                return null;
            }
            return Yii::app()->dateFormatter->formatDateTime(
                    $metadata['Currency']['lastAttemptedRateUpdateTimeStamp'], 'short');
        }
    }
?>