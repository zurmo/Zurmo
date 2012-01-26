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
     * Model for storing currency attribute values on other models.
     */
    class CurrencyValue extends OwnedModel
    {
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            if ($bean ===  null && $setDefaults)
            {
                $currentUser = Yii::app()->user->userModel;
                if (!$currentUser instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
                $this->currency = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            }
        }

        public function __toString()
        {
            if (trim($this->value) == '')
            {
                return Yii::t('Default', '(None)');
            }
            return $this->value;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'value',
                    'rateToBase',
                ),
                'relations' => array(
                    'currency' => array(RedBeanModel::HAS_ONE, 'Currency'),
                ),
                'rules' => array(
                    array('value',       'required'),
                    array('value',       'type',    'type' => 'float'),
                    array('value',       'default', 'value' => 0),
                    array('rateToBase',  'required'),
                    array('rateToBase',  'type', 'type' => 'float'),
                    array('currency',    'required'),
                ),
                'defaultSortAttribute' => 'value'
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function isCurrencyInUseById($currencyId)
        {
            assert('is_int($currencyId)');
            $columnName = RedBeanModel::getForeignKeyName('CurrencyValue', 'currency');
            $quote      = DatabaseCompatibilityUtil::getQuote();
            $where      = "{$quote}{$columnName}{$quote} = '{$currencyId}'";
            $count      = CurrencyValue::getCount(null, $where);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * Get the rateToBase from the currency model.
         * @return true to signal success and that validate can proceed.
         */
        public function beforeValidate()
        {
            if (!parent::beforeValidate())
            {
                return false;
            }
            if ($this->currency->rateToBase !== null &&
                    ($this->rateToBase === null                     ||
                     array_key_exists('value', $this->originalAttributeValues) ||
                     array_key_exists('currency', $this->originalAttributeValues)))
            {
                $this->rateToBase = $this->currency->rateToBase;
                assert('$this->rateToBase !== null');
            }
            return true;
        }
    }
?>
