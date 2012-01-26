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

    /**
     * Model for storing system supported currencies.
     */
    class Currency extends RedBeanModel
    {
        /**
         * Gets a currency by code.
         * @param $code String Code.
         * @return A model of type currency
         */
        public static function getByCode($code)
        {
            assert('is_string($code)');
            $tableName = self::getTableName('Currency');
            $beans = RedBean_Plugin_Finder::where($tableName, "code = '$code'");
            assert('count($beans) <= 1');
            if (count($beans) == 0)
            {
                throw new NotFoundException();
            }
            return RedBeanModel::makeModel(end($beans), 'Currency');
        }

        /**
         * Override to check if no results are returned and load the baseCurrency as the first currency in that
         * scenario.
         */
        public static function getAll($orderBy = null, $sortDescending = false,
                                        $modelClassName = null, $buildFirstCurrency = true)
        {
            $currencies = parent::getAll($orderBy, $sortDescending, $modelClassName);
            if (count($currencies) > 0 || $buildFirstCurrency == false)
            {
                return $currencies;
            }

            return array(self::makeBaseCurrency());
        }

        public static function makeBaseCurrency()
        {
            $currency             = new Currency();
            $currency->code       = Yii::app()->currencyHelper->getBaseCode();
            $currency->rateToBase = 1;
            $saved                = $currency->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $currency;
        }

        public function __toString()
        {
            if (trim($this->code) == '')
            {
                return Yii::t('Default', '(None)');
            }
            return $this->code;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'code',
                    'rateToBase',
                    'active',
                ),
                'rules' => array(
                    array('code',       'required'),
                    array('code',       'unique'),
                    array('code',       'type', 'type' => 'string'),
                    array('code',       'length', 'min' => 3, 'max' => 3),
                    array('code',       'match',  'pattern' => '/^[A-Z][A-Z][A-Z]$/', // Not Coding Standard
                                                  'message' => 'Code must be a valid currency code.'),
                    array('rateToBase', 'required'),
                    array('rateToBase', 'type', 'type' => 'float'),
                    array('active',     'boolean'),
                    array('active',     'default', 'value' => true),
                ),
                'lastAttemptedRateUpdateTimeStamp'      => null,
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>
