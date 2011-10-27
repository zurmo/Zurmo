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
     *
     * Utilize WebServiceX service provider to get currency rates
     * @link http://www.webservicex.net/ws/WSDetails.aspx?CATID=2&WSID=10
     *
     */
    class WebServiceXCurrencyServiceUtil extends CurrencyServiceUtil
    {
        /**
         * @param string $fromCode
         * @param string $toCode
         * @return float
         */
        public function getConversionRateViaWebService($fromCode, $toCode)
        {
            $this->resetErrors();
            $url  = 'http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=';
            $url .= $fromCode . '&ToCurrency=' . $toCode;
            $ch = curl_init();
            $timeout = 2;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $fileContents = curl_exec($ch);
            $error_info = curl_error($ch);
            curl_close($ch);
            if ($fileContents === false || empty($fileContents))
            {
                $this->webServiceErrorMessage = $error_info;
                $this->webServiceErrorCode    = ZurmoCurrencyHelper::ERROR_WEB_SERVICE;
                return null;
            }
            if (!empty($fileContents) &&
                false !== $xml = @simplexml_load_string($fileContents))
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
            if (stripos($fileContents, 'error') === false)
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
    }
?>