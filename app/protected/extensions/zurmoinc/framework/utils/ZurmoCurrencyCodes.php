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
     * Available Currency Codes
     * This class contains an array of available currency codes and search methods to perform on these codes.
     */
    class ZurmoCurrencyCodes
    {
        /**
         *
         * Currency codes based on ISO 4217 list @see http://www.xe.com/iso4217.php
         */
        public static function getCodes()
        {
            $codes = array(
                'AED' => 'United Arab Emirates Dirham',
                'AFN' => 'Afghanistan Afghani',
                'ALL' => 'Albania Lek',
                'AMD' => 'Armenia Dram',
                'ANG' => 'Netherlands Antilles Guilder',
                'AOA' => 'Angola Kwanza',
                'ARS' => 'Argentina Peso',
                'AUD' => 'Australia Dollar',
                'AWG' => 'Aruba Guilder',
                'AZN' => 'Azerbaijan New Manat',
                'BAM' => 'Bosnia and Herzegovina Convertible Marka',
                'BBD' => 'Barbados Dollar',
                'BDT' => 'Bangladesh Taka',
                'BGN' => 'Bulgaria Lev',
                'BHD' => 'Bahrain Dinar',
                'BIF' => 'Burundi Franc',
                'BMD' => 'Bermuda Dollar',
                'BND' => 'Brunei Darussalam Dollar',
                'BOB' => 'Bolivia Boliviano',
                'BRL' => 'Brazil Real',
                'BSD' => 'Bahamas Dollar',
                'BTN' => 'Bhutan Ngultrum',
                'BWP' => 'Botswana Pula',
                'BYR' => 'Belarus Ruble',
                'BZD' => 'Belize Dollar',
                'CAD' => 'Canada Dollar',
                'CDF' => 'Congo/Kinshasa Franc',
                'CHF' => 'Switzerland Franc',
                'CLP' => 'Chile Peso',
                'CNY' => 'China Yuan Renminbi',
                'COP' => 'Colombia Peso',
                'CRC' => 'Costa Rica Colon',
                'CUC' => 'Cuba Convertible Peso',
                'CUP' => 'Cuba Peso',
                'CVE' => 'Cape Verde Escudo',
                'CZK' => 'Czech Republic Koruna',
                'DJF' => 'Djibouti Franc',
                'DKK' => 'Denmark Krone',
                'DOP' => 'Dominican Republic Peso',
                'DZD' => 'Algeria Dinar',
                'EGP' => 'Egypt Pound',
                'ERN' => 'Eritrea Nakfa',
                'ETB' => 'Ethiopia Birr',
                'EUR' => 'Euro Member Countries',
                'FJD' => 'Fiji Dollar',
                'FKP' => 'Falkland Islands (Malvinas) Pound',
                'GBP' => 'United Kingdom Pound',
                'GEL' => 'Georgia Lari',
                'GGP' => 'Guernsey Pound',
                'GHS' => 'Ghana Cedi',
                'GIP' => 'Gibraltar Pound',
                'GMD' => 'Gambia Dalasi',
                'GNF' => 'Guinea Franc',
                'GTQ' => 'Guatemala Quetzal',
                'GYD' => 'Guyana Dollar',
                'HKD' => 'Hong Kong Dollar',
                'HNL' => 'Honduras Lempira',
                'HRK' => 'Croatia Kuna',
                'HTG' => 'Haiti Gourde',
                'HUF' => 'Hungary Forint',
                'IDR' => 'Indonesia Rupiah',
                'ILS' => 'Israel Shekel',
                'IMP' => 'Isle of Man Pound',
                'INR' => 'India Rupee',
                'IQD' => 'Iraq Dinar',
                'IRR' => 'Iran Rial',
                'ISK' => 'Iceland Krona',
                'JEP' => 'Jersey Pound',
                'JMD' => 'Jamaica Dollar',
                'JOD' => 'Jordan Dinar',
                'JPY' => 'Japan Yen',
                'KES' => 'Kenya Shilling',
                'KGS' => 'Kyrgyzstan Som',
                'KHR' => 'Cambodia Riel',
                'KMF' => 'Comoros Franc',
                'KPW' => 'Korea (North) Won',
                'KRW' => 'Korea (South) Won',
                'KWD' => 'Kuwait Dinar',
                'KYD' => 'Cayman Islands Dollar',
                'KZT' => 'Kazakhstan Tenge',
                'LAK' => 'Laos Kip',
                'LBP' => 'Lebanon Pound',
                'LKR' => 'Sri Lanka Rupee',
                'LRD' => 'Liberia Dollar',
                'LSL' => 'Lesotho Loti',
                'LTL' => 'Lithuania Litas',
                'LVL' => 'Latvia Lat',
                'LYD' => 'Libya Dinar',
                'MAD' => 'Morocco Dirham',
                'MDL' => 'Moldova Leu',
                'MGA' => 'Madagascar Ariary',
                'MKD' => 'Macedonia Denar',
                'MMK' => 'Myanmar (Burma) Kyat',
                'MNT' => 'Mongolia Tughrik',
                'MOP' => 'Macau Pataca',
                'MRO' => 'Mauritania Ouguiya',
                'MUR' => 'Mauritius Rupee',
                'MVR' => 'Maldives (Maldive Islands) Rufiyaa',
                'MWK' => 'Malawi Kwacha',
                'MXN' => 'Mexico Peso',
                'MYR' => 'Malaysia Ringgit',
                'MZN' => 'Mozambique Metical',
                'NAD' => 'Namibia Dollar',
                'NGN' => 'Nigeria Naira',
                'NIO' => 'Nicaragua Cordoba',
                'NOK' => 'Norway Krone',
                'NPR' => 'Nepal Rupee',
                'NZD' => 'New Zealand Dollar',
                'OMR' => 'Oman Rial',
                'PAB' => 'Panama Balboa',
                'PEN' => 'Peru Nuevo Sol',
                'PGK' => 'Papua New Guinea Kina',
                'PHP' => 'Philippines Peso',
                'PKR' => 'Pakistan Rupee',
                'PLN' => 'Poland Zloty',
                'PYG' => 'Paraguay Guarani',
                'QAR' => 'Qatar Riyal',
                'RON' => 'Romania New Leu',
                'RSD' => 'Serbia Dinar',
                'RUB' => 'Russia Ruble',
                'RWF' => 'Rwanda Franc',
                'SAR' => 'Saudi Arabia Riyal',
                'SBD' => 'Solomon Islands Dollar',
                'SCR' => 'Seychelles Rupee',
                'SDG' => 'Sudan Pound',
                'SEK' => 'Sweden Krona',
                'SGD' => 'Singapore Dollar',
                'SHP' => 'Saint Helena Pound',
                'SLL' => 'Sierra Leone Leone',
                'SOS' => 'Somalia Shilling',
                'SRD' => 'Suriname Dollar',
                'STD' => 'S�o Principe and Tome Dobra',
                'SVC' => 'El Salvador Colon',
                'SYP' => 'Syria Pound',
                'SZL' => 'Swaziland Lilangeni',
                'THB' => 'Thailand Baht',
                'TJS' => 'Tajikistan Somoni',
                'TMT' => 'Turkmenistan Manat',
                'TND' => 'Tunisia Dinar',
                'TOP' => 'Tonga Pa\'anga',
                'TRY' => 'Turkey Lira',
                'TTD' => 'Trinidad and Tobago Dollar',
                'TVD' => 'Tuvalu Dollar',
                'TWD' => 'Taiwan New Dollar',
                'TZS' => 'Tanzania Shilling',
                'UAH' => 'Ukraine Hryvna',
                'UGX' => 'Uganda Shilling',
                'USD' => 'United States Dollar',
                'UYU' => 'Uruguay Peso',
                'UZS' => 'Uzbekistan Som',
                'VEF' => 'Venezuela Bolivar Fuerte',
                'VND' => 'Viet Nam Dong',
                'VUV' => 'Vanuatu Vatu',
                'WST' => 'Samoa Tala',
                'XCD' => 'East Caribbean Dollar',
                'YER' => 'Yemen Rial',
                'ZAR' => 'South Africa Rand',
                'ZMK' => 'Zambia Kwacha',
                'ZWD' => 'Zimbabwe Dollar',
            );
            return $codes;
        }

        /**
         * Given a string that is either a partial code or currency name, return the array of codes/names that match.
         * @param string $partialCode
         */
        public static function getByPartialCodeOrName($partialCodeOrName)
        {
            assert('is_string($partialCodeOrName)');
            $matches = array();
            $codesAndNames = self::getCodes();
            foreach ($codesAndNames as $code => $name)
            {
                if (stripos($code, $partialCodeOrName) !== false || stripos($name, $partialCodeOrName) !== false)
                {
                    $matches[$code] = $name;
                }
            }
            return $matches;
        }

        /**
         * Given a currency code, return true if valid otherwise return false.
         * @param string $code
         * @return true if valid code
         */
        public static function isValidCode($code)
        {
            return array_key_exists(strtoupper($code), self::getCodes());
        }
    }
?>