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
        private static $currencyIdRowsByCode   = array();
        private static $cachedCurrencyIdByCode = array();
        private static $cachedCurrencyById     = array();
        private static $allCachedCurrencies    = array();
        protected $isSavableFromRelation       = false;

        public static function getById($id, $modelClassName = null)
        {
            assert('$modelClassName == "Currency"');
            if(isset(self::$cachedCurrencyById[$id]))
            {
                return self::$cachedCurrencyById[$id];
            }
            $currency = parent::getById($id, $modelClassName);
            self::$cachedCurrencyById[$id] = $currency;
            return $currency;
        }

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
                    'active',
                    'code',
                    'rateToBase',
                ),
                'rules' => array(
                    array('active',     'boolean'),
                    array('active',     'default', 'value' => true),
                    array('code',       'required'),
                    array('code',       'unique'),
                    array('code',       'type', 'type' => 'string'),
                    array('code',       'length', 'min' => 3, 'max' => 3),
                    array('code',       'match',  'pattern' => '/^[A-Z][A-Z][A-Z]$/', // Not Coding Standard
                                                  'message' => 'Code must be a valid currency code.'),
                    array('rateToBase', 'required'),
                    array('rateToBase', 'type', 'type' => 'float'),
                ),
                'lastAttemptedRateUpdateTimeStamp'      => null,
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getCachedCurrencyByCode($code)
        {
            assert('is_string($code)');
            if(isset(self::$cachedCurrencyIdByCode[$code]) &&
               self::$cachedCurrencyById[self::$cachedCurrencyIdByCode[$code]])
               {
                    return self::$cachedCurrencyById[self::$cachedCurrencyIdByCode[$code]];
               }
               return null;
        }

        public static function setCachedCurrency(Currency $currency)
        {
            assert('$currency->id > 0');
            self::$cachedCurrencyIdByCode[$currency->code]     = $currency->id;
            self::$cachedCurrencyById[$currency->id]           = $currency;
        }

        public function isUniqueAttributeValue($attributeName, $value)
        {
            if($attributeName != 'code')
            {
                return parent::isUniqueAttributeValue($attributeName, $value);
            }
            assert('$value !== null');
            if(isset(static::$currencyIdRowsByCode[$value]))
            {
                $rows = static::$currencyIdRowsByCode[$value];
            }
            else
            {
                $modelClassName = $this->attributeNameToBeanAndClassName[$attributeName][1];
                $tableName = self::getTableName($modelClassName);
                $rows = R::getAll('select id from ' . $tableName . " where $attributeName = ?", array($value));
                static::$currencyIdRowsByCode[$value] = $rows;
            }
            return count($rows) == 0 || count($rows) == 1 && $rows[0]['id'] == $this->id;
        }

        public function save($runValidation = true, array $attributeNames = null)
        {
            $backTrace = debug_backtrace();
            for ($i = 0; $i < count($backTrace); $i++)
            {
                if (isset($backTrace[$i]['object']))
                {
                    if(get_class($backTrace[$i]['object']) == 'CurrencyValue')
                    {
                        return true;
                    }
                }
            }
            $saved = parent::save($runValidation, $attributeNames);
            self::resetCaches();
            return $saved;
        }

        public static function getAllCachedCurrencies()
        {
            if(empty(self::$allCachedCurrencies))
            {
                return null;
            }
            return self::$allCachedCurrencies;
        }

        public static function setAllCachedCurrencies($currencies)
        {
            self::$allCachedCurrencies = $currencies;
        }

        public static function resetCaches()
        {
            self::$currencyIdRowsByCode   = array();
            self::$cachedCurrencyIdByCode = array();
            self::$cachedCurrencyById     = array();
            self::$allCachedCurrencies    = array();
        }
    }
?>