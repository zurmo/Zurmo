<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Rules for working with operators that can be used for triggers in workflows or filters in reporting.
     */
    class OperatorRules
    {
        const TYPE_EQUALS                         = 'equals';

        const TYPE_DOES_NOT_EQUAL                 = 'doesNotEqual';

        const TYPE_STARTS_WITH                    = 'startsWith';

        const TYPE_ENDS_WITH                      = 'endsWith';

        const TYPE_CONTAINS                       = 'contains';

        const TYPE_GREATER_THAN_OR_EQUAL_TO       = 'greaterThanOrEqualTo';

        const TYPE_LESS_THAN_OR_EQUAL_TO          = 'lessThanOrEqualTo';

        const TYPE_GREATER_THAN                   = 'greaterThan';

        const TYPE_LESS_THAN                      = 'lessThan';

        const TYPE_ONE_OF                         = 'oneOf';

        const TYPE_BETWEEN                        = 'between';

        const TYPE_IS_NULL                        = 'isNull';

        const TYPE_IS_NOT_NULL                    = 'isNotNull';

        const TYPE_BECOMES                        = 'becomes';

        const TYPE_WAS                            = 'was';

        const TYPE_BECOMES_ONE_OF                 = 'becomesOneOf';

        const TYPE_WAS_ONE_OF                     = 'wasOneOf';

        const TYPE_CHANGES                        = 'changes';

        const TYPE_DOES_NOT_CHANGE                = 'doesNotChange';

        const TYPE_IS_EMPTY                       = 'isEmpty';

        const TYPE_IS_NOT_EMPTY                   = 'isNotEmpty';

        public static function getTranslatedTypeLabel($type)
        {
            assert('is_string($type)');
            $labels             = self::translatedTypeLabels();
            if (isset($labels[$type]))
            {
                return $labels[$type];
            }
            throw new NotSupportedException();
        }

        public static function translatedTypeLabels()
        {
            return array(OperatorRules::TYPE_EQUALS                      => Zurmo::t('Core', 'Equals'),
                         OperatorRules::TYPE_DOES_NOT_EQUAL              => Zurmo::t('Core', 'Does Not Equal'),
                         OperatorRules::TYPE_STARTS_WITH                 => Zurmo::t('Core', 'Starts With'),
                         OperatorRules::TYPE_ENDS_WITH                   => Zurmo::t('Core', 'Ends With'),
                         OperatorRules::TYPE_CONTAINS                    => Zurmo::t('Core', 'Contains'),
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO    => Zurmo::t('Core', 'Greater Than Or Equal To'),
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO       => Zurmo::t('Core', 'Less Than Or Equal To'),
                         OperatorRules::TYPE_GREATER_THAN                => Zurmo::t('Core', 'Greater Than'),
                         OperatorRules::TYPE_LESS_THAN                   => Zurmo::t('Core', 'Less Than'),
                         OperatorRules::TYPE_ONE_OF                      => Zurmo::t('Core', 'One Of'),
                         OperatorRules::TYPE_BETWEEN                     => Zurmo::t('Core', 'Between'),
                         OperatorRules::TYPE_IS_NULL                     => Zurmo::t('Core', 'Is Null'), // Not Coding Standard
                         OperatorRules::TYPE_IS_NOT_NULL                 => Zurmo::t('Core', 'Is Not Null'), // Not Coding Standard
                         OperatorRules::TYPE_BECOMES                     => Zurmo::t('Core', 'Becomes'),
                         OperatorRules::TYPE_WAS                         => Zurmo::t('Core', 'Was'),
                         OperatorRules::TYPE_BECOMES_ONE_OF              => Zurmo::t('Core', 'Becomes One Of'),
                         OperatorRules::TYPE_WAS_ONE_OF                  => Zurmo::t('Core', 'Was One Of'),
                         OperatorRules::TYPE_CHANGES                     => Zurmo::t('Core', 'Changes'),
                         OperatorRules::TYPE_DOES_NOT_CHANGE             => Zurmo::t('Core', 'Does Not Change'),
                         OperatorRules::TYPE_IS_EMPTY                    => Zurmo::t('Core', 'Is Empty'),
                         OperatorRules::TYPE_IS_NOT_EMPTY                => Zurmo::t('Core', 'Is Not Empty'),
            );
        }

        public static function availableTypes()
        {
            return array(OperatorRules::TYPE_EQUALS,
                         OperatorRules::TYPE_DOES_NOT_EQUAL,
                         OperatorRules::TYPE_STARTS_WITH,
                         OperatorRules::TYPE_ENDS_WITH,
                         OperatorRules::TYPE_CONTAINS,
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_GREATER_THAN,
                         OperatorRules::TYPE_LESS_THAN,
                         OperatorRules::TYPE_ONE_OF,
                         OperatorRules::TYPE_BETWEEN,
                         OperatorRules::TYPE_IS_NULL,
                         OperatorRules::TYPE_IS_NOT_NULL,
                         OperatorRules::TYPE_BECOMES,
                         OperatorRules::TYPE_WAS,
                         OperatorRules::TYPE_BECOMES_ONE_OF,
                         OperatorRules::TYPE_WAS_ONE_OF,
                         OperatorRules::TYPE_CHANGES,
                         OperatorRules::TYPE_DOES_NOT_CHANGE,
                         OperatorRules::TYPE_IS_EMPTY,
                         OperatorRules::TYPE_IS_NOT_EMPTY,
            );
        }

        public static function getOperatorsWhereValueIsRequired()
        {
            return array(   OperatorRules::TYPE_EQUALS,
                            OperatorRules::TYPE_DOES_NOT_EQUAL,
                            OperatorRules::TYPE_STARTS_WITH,
                            OperatorRules::TYPE_ENDS_WITH,
                            OperatorRules::TYPE_CONTAINS,
                            OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                            OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                            OperatorRules::TYPE_GREATER_THAN,
                            OperatorRules::TYPE_LESS_THAN,
                            OperatorRules::TYPE_ONE_OF,
                            OperatorRules::TYPE_BETWEEN,
                            OperatorRules::TYPE_BECOMES,
                            OperatorRules::TYPE_WAS,
                            OperatorRules::TYPE_BECOMES_ONE_OF,
                            OperatorRules::TYPE_WAS_ONE_OF,
            );
        }

        public static function getOperatorsWhereSecondValueIsRequired()
        {
            return array(OperatorRules::TYPE_BETWEEN);
        }
    }
?>