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
     * Improved Locale class to overcome some issues in Yii's CLocale.
     * Needed to override almost the entire class as the CLocale's constructor and variables are protected.
     * Any of the parent functions that uses the private attributes are overriden.
     */
    class ZurmoLocale extends CLocale
    {
        /**
         * @var string the directory that contains the locale data. If this property is not set,
         * the locale data will be loaded from 'framework/i18n/data'.
         * @since 1.1.0
         */
        public static $dataPath;

        private $_id;
        private $_data;
        private $_dateFormatter;
        private $_numberFormatter;

        /**
         * Returns the instance of the specified locale.
         * Since the constructor of ZurmoLocale is protected, you can only use
         * this method to obtain an instance of the specified locale.
         * @param string $id the locale ID (e.g. en_US)
         * @return ZurmoLocale the locale instance
         */
        public static function getInstance($id)
        {
            static $locales = array();
            if (isset($locales[$id]))
            {
                return $locales[$id];
            }
            else
            {
                return $locales[$id] = new ZurmoLocale($id);
            }
        }

        /**
         * @return array IDs of the locales which the framework can recognize
         */
        public static function getLocaleIDs()
        {
            static $locales;
            if ($locales === null)
            {
                $locales = array();
                $dataPath = self::$dataPath === null ? YII_PATH . DIRECTORY_SEPARATOR . 'i18n'.DIRECTORY_SEPARATOR . 'data' : self::$dataPath;
                $folder = @opendir($dataPath);
                while (($file = @readdir($folder)) !== false)
                {
                    $fullPath = $dataPath . DIRECTORY_SEPARATOR . $file;
                    if (substr($file, -4) ==='.php' && is_file($fullPath))
                    {
                        $locales[] = substr($file, 0, -4);
                    }
                }
                closedir($folder);
                sort($locales);
            }
            return $locales;
        }

        /**
         * Constructor.
         * Since the constructor is protected, please use {@link getInstance}
         * to obtain an instance of the specified locale.
         * @param string $id the locale ID (e.g. en_US)
         */
         protected function __construct($id)
         {
            $this->_id = self::getCanonicalID($id);
            $dataPath = self::$dataPath === null ? YII_PATH . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'data' : self::$dataPath;
            $dataFile = $dataPath . DIRECTORY_SEPARATOR . $this->_id . '.php';
            if (is_file($dataFile))
            {
                $this->_data = require($dataFile);
            }
            else
            {
                throw new CException(Zurmo::t('yii', 'Unrecognized locale "{locale}".', array('{locale}' => $id)));
            }
         }

        /**
         * @return string the locale ID (in canonical form)
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * @return CNumberFormatter the number formatter for this locale
         */
        public function getNumberFormatter()
        {
            if ($this->_numberFormatter === null)
            {
                $this->_numberFormatter = new CNumberFormatter($this);
            }
            return $this->_numberFormatter;
        }

        /**
         * @return CDateFormatter the date formatter for this locale
         */
        public function getDateFormatter()
        {
            if ($this->_dateFormatter === null)
            {
                $this->_dateFormatter = new CDateFormatter($this);
            }
            return $this->_dateFormatter;
        }

        /**
         * @param string $currency 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
         * @return string the localized currency symbol. Null if the symbol does not exist.
         */
        public function getCurrencySymbol($currency)
        {
            return isset($this->_data['currencySymbols'][$currency]) ? $this->_data['currencySymbols'][$currency] : null;
        }

        /**
         * @param string $name symbol name
         * @return string symbol
         */
        public function getNumberSymbol($name)
        {
            return isset($this->_data['numberSymbols'][$name]) ? $this->_data['numberSymbols'][$name] : null;
        }

        /**
         * @return string the decimal format
         */
        public function getDecimalFormat()
        {
            return $this->_data['decimalFormat'];
        }

        /**
         * @return string the currency format
         */
        public function getCurrencyFormat()
        {
            return $this->_data['currencyFormat'];
        }

        /**
         * @return string the percent format
         */
        public function getPercentFormat()
        {
            return $this->_data['percentFormat'];
        }

        /**
         * @return string the scientific format
         */
        public function getScientificFormat()
        {
            return $this->_data['scientificFormat'];
        }

        /**
         * @param integer $month month (1-12)
         * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
         * @param boolean $standAlone whether the month name should be returned in stand-alone format
         * @return string the month name
         */
        public function getMonthName($month, $width = 'wide', $standAlone = false)
        {
            if ($standAlone)
            {
                return isset($this->_data['monthNamesSA'][$width][$month]) ? $this->_data['monthNamesSA'][$width][$month] : $this->_data['monthNames'][$width][$month];
            }
            else
            {
                return isset($this->_data['monthNames'][$width][$month]) ? $this->_data['monthNames'][$width][$month] : $this->_data['monthNamesSA'][$width][$month];
            }
        }

        /**
         * Returns the month names in the specified width.
         * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
         * @param boolean $standAlone whether the month names should be returned in stand-alone format
         * @return array month names indexed by month values (1-12)
         */
        public function getMonthNames($width = 'wide', $standAlone = false)
        {
            if ($standAlone)
            {
                return isset($this->_data['monthNamesSA'][$width]) ? $this->_data['monthNamesSA'][$width] : $this->_data['monthNames'][$width];
            }
            else
            {
                return isset($this->_data['monthNames'][$width]) ? $this->_data['monthNames'][$width] : $this->_data['monthNamesSA'][$width];
            }
        }

        /**
         * @param integer $day weekday (0-6, 0 means Sunday)
         * @param string $width weekday name width.  It can be 'wide', 'abbreviated' or 'narrow'.
         * @param boolean $standAlone whether the week day name should be returned in stand-alone format
         * @return string the weekday name
         */
        public function getWeekDayName($day, $width = 'wide', $standAlone = false)
        {
            if ($standAlone)
            {
                return isset($this->_data['weekDayNamesSA'][$width][$day]) ? $this->_data['weekDayNamesSA'][$width][$day] : $this->_data['weekDayNames'][$width][$day];
            }
            else
            {
                return isset($this->_data['weekDayNames'][$width][$day]) ? $this->_data['weekDayNames'][$width][$day] : $this->_data['weekDayNamesSA'][$width][$day];
            }
        }

        /**
         * Returns the week day names in the specified width.
         * @param string $width weekday name width.  It can be 'wide', 'abbreviated' or 'narrow'.
         * @param boolean $standAlone whether the week day name should be returned in stand-alone format
         * @return array the weekday names indexed by weekday values (0-6, 0 means Sunday, 1 Monday, etc.)
         */
        public function getWeekDayNames($width = 'wide', $standAlone = false)
        {
            if ($standAlone)
            {
                return isset($this->_data['weekDayNamesSA'][$width]) ? $this->_data['weekDayNamesSA'][$width] : $this->_data['weekDayNames'][$width];
            }
            else
            {
                return isset($this->_data['weekDayNames'][$width]) ? $this->_data['weekDayNames'][$width] : $this->_data['weekDayNamesSA'][$width];
            }
        }

        /**
         * @param integer $era era (0,1)
         * @param string $width era name width.  It can be 'wide', 'abbreviated' or 'narrow'.
         * @return string the era name
         */
        public function getEraName($era, $width = 'wide')
        {
            return $this->_data['eraNames'][$width][$era];
        }

        /**
         * @return string the AM name
         */
        public function getAMName()
        {
            return $this->_data['amName'];
        }

        /**
         * @return string the PM name
         */
        public function getPMName()
        {
            return $this->_data['pmName'];
        }

        /**
         * @param string $width date format width. It can be 'full', 'long', 'medium' or 'short'.
         * @return string date format
         */
        public function getDateFormat($width = 'medium')
        {
            return $this->_data['dateFormats'][$width];
        }

        /**
         * @param string $width time format width. It can be 'full', 'long', 'medium' or 'short'.
         * @return string date format
         */
        public function getTimeFormat($width = 'medium')
        {
            return $this->_data['timeFormats'][$width];
        }

        /**
         * @return string datetime format, i.e., the order of date and time.
         */
        public function getDateTimeFormat()
        {
            if (in_array($this->_id, array('zn_ch')))
            {
                return '{1} {0}';
            }
            return $this->_data['dateTimeFormat'];
        }

        /**
         * @return string the character orientation, which is either 'ltr' (left-to-right) or 'rtl' (right-to-left)
         * @since 1.1.2
         */
        public function getOrientation()
        {
            return isset($this->_data['orientation']) ? $this->_data['orientation'] : 'ltr';
        }

        /**
         * @return array plural forms expressions
         */
        public function getPluralRules()
        {
            return isset($this->_data['pluralRules']) ? $this->_data['pluralRules'] : array();
        }

        /**
         * Gets a localized name from i18n data file (one of framework/i18n/data/ files).
         *
         * @param string $id array key from an array named by $category.
         * @param string $category data category. One of 'languages', 'scripts' or 'territories'.
         * @return string the localized name for the id specified. Null if data does not exist.
         * @since 1.1.9
         */
        public function getLocaleDisplayName($id  = null, $category = 'languages')
        {
            $id = $this->getCanonicalID($id);
            if (isset($this->_data[$category][$id]))
            {
                return $this->_data[$category][$id];
            }
            elseif (($category == 'languages') && ($id = $this->getLanguageID($id)) && (isset($this->_data[$category][$id])))
            {
                return $this->_data[$category][$id];
            }
            elseif (($category == 'scripts') && ($id = $this->getScriptID($id)) && (isset($this->_data[$category][$id])))
            {
                return $this->_data[$category][$id];
            }
            elseif (($category == 'territories') && ($id = $this->getTerritoryID($id)) && (isset($this->_data[$category][$id])))
            {
                return $this->_data[$category][$id];
            }
            else
            {
                return null;
            }
        }
    }
?>