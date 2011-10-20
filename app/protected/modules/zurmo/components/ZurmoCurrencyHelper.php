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

        protected $webServiceErrorMessage;

        protected $webServiceErrorCode;

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

        public function getActiveCurrencyForCurrentUser()
        {
            if(Yii::app()->user->userModel->currency->id > 0)
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
            if($currency->id <= 0)
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
            $rate = $this->getConversionRateViaWebService($fromCode, $this->getBaseCode());
            if ($rate == null)
            {
                return 1;
            }
            return $rate;
        }

        /**
         * @param $error - string by reference to attach error to if needed.
         * @return rate as a float, otherwise null if there is some sort of error
         */
        protected function getConversionRateViaWebService($fromCode, $toCode)
        {
            $url  = 'http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=';
            $url .= $fromCode . '&ToCurrency=' . $toCode;
            $ch = curl_init();
            $timeout = 2;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            if ($file_contents === false || empty($file_contents))
            {
                $this->webServiceErrorMessage = curl_error($ch);
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_WEB_SERVICE;
                return null;
            }
            curl_close($ch);
            if (!empty($file_contents) &&
                false !== $xml = @simplexml_load_string($file_contents))
            {
                if (is_object($xml) && $xml instanceof SimpleXMLElement)
                {
                    $xmlAsArray = (array)$xml;
                    return $xmlAsArray[0];
                }
                elseif (is_array($xml))
                {
                    return $xml[0];
                }
                else
                {
                    return null; //todo: throw exception
                }
            }
            if (stripos($file_contents, 'error') === false)
            {
                $this->webServiceErrorMessage = Yii::t('Default', 'Invalid currency code');
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_INVALID_CODE;
            }
            else
            {
                $this->webServiceErrorMessage = Yii::t('Default', 'There was an error with the web service.');
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_WEB_SERVICE;
            }
            return null;
        }

        public function getWebServiceErrorMessage()
        {
            return $this->webServiceErrorMessage;
        }

        public function getWebServiceErrorCode()
        {
            return $this->webServiceErrorCode;
        }

        /**
         * After you make a call to a method that envokes a webService, reset the errors.
         * @see getConversionRateViaWebService
         */
        public function resetErrors()
        {
            $this->webServiceErrorMessage  = null;
            $this->webServiceErrorCode     = null;
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