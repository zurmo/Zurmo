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
     * Helper class to provider SQL operators and validate
     * accurate usage of operator types
     */
    class SQLOperatorUtil
    {
        /**
         * Confirms usage of operator type is valid.
         * @return boolean;
         */
        public static function isValidOperatorTypeByValue($operatorType, $value)
        {
            if (is_string($value))
            {
                return in_array($operatorType, array('startsWith', 'endsWith', 'equals', 'doesNotEqual', 'contains',
                                                     'lessThan', 'greaterThan', 'greaterThanOrEqualTo',
                                                     'lessThanOrEqualTo'));
            }
            elseif (is_array($value))
            {
                return in_array($operatorType, array('oneOf'));
            }
            elseif ($value !== null)
            {
                return in_array($operatorType, array('greaterThan', 'lessThan', 'equals', 'doesNotEqual',
                                                     'greaterThanOrEqualTo', 'lessThanOrEqualTo'));
            }
            elseif ($value === null)
            {
                return in_array($operatorType, array('isNull', 'isNotNull', 'isEmpty', 'isNotEmpty'));
            }
            return false;
        }

        /**
         * Input an operator type and it returns an
         * equivalent SQL operator.
         * @return string
         */
        public static function getOperatorByType($operatorType)
        {
            assert('is_string($operatorType)');
            $validOperator = true;
            if (YII_DEBUG)
            {
                $validOperator = SQLOperatorUtil::isValidOperatorType($operatorType);
            }
            if ($validOperator)
            {
                switch ($operatorType)
                {
                    case 'startsWith' :
                        return 'like';

                    case 'endsWith' :
                        return 'like';

                    case 'contains' :
                        return 'like';

                    case 'equals' :
                        return '=';

                    case 'doesNotEqual' :
                        return '!=';

                    case 'greaterThan' :
                        return '>';

                    case 'lessThan' :
                        return '<';

                    case 'greaterThanOrEqualTo' :
                        return '>=';

                    case 'lessThanOrEqualTo' :
                        return '<=';

                    default :
                        throw new NotSupportedException('Unsupported operator type: ' . $operatorType);
                }
            }
        }

        /**
         * @return string
         */
        public static function resolveValueLeftSideLikePartByOperatorType($operatorType)
        {
            assert('is_string($operatorType)');
            $validOperator = true;
            if (YII_DEBUG)
            {
                $validOperator = SQLOperatorUtil::isValidOperatorType($operatorType);
            }
            if ($validOperator &&  in_array($operatorType, array('endsWith', 'contains')))
            {
                return '%';
            }
        }

        /**
         * @return string
         */
        public static function resolveValueRightSideLikePartByOperatorType($operatorType)
        {
            assert('is_string($operatorType)');
            $validOperator = true;
            if (YII_DEBUG)
            {
                $validOperator = SQLOperatorUtil::isValidOperatorType($operatorType);
            }
            if ($validOperator && in_array($operatorType, array('startsWith', 'contains')))
            {
                return '%';
            }
        }

        public static function resolveOperatorAndValueForOneOf($operatorType, $values, $ignoreStringToLower = false)
        {
            assert('$operatorType == "oneOf"');
            assert('is_array($values) && count($values) > 0');
            $inPart = null;
            foreach ($values as $theValue)
            {
                if ($inPart != null)
                {
                    $inPart .= ','; // Not Coding Standard
                }
                if (is_string($theValue))
                {
                    if ($ignoreStringToLower)
                    {
                        $inPart .= "'" . DatabaseCompatibilityUtil::escape($theValue) . "'";
                    }
                    else
                    {
                        $inPart .= "'" . DatabaseCompatibilityUtil::escape($theValue) . "'";
                    }
                }
                elseif (is_numeric($theValue))
                {
                    $inPart .= $theValue;
                }
                elseif (is_bool($theValue))
                {
                    if (!$theValue)
                    {
                        $theValue = 0;
                    }
                    $inPart .= $theValue;
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return 'IN(' . $inPart . ')';
        }

        public static function resolveOperatorAndValueForNullOrEmpty($operatorType)
        {
            assert('in_array($operatorType, array("isNull", "isNotNull", "isEmpty", "isNotEmpty"))');
            if ($operatorType == 'isNull')
            {
                return 'IS NULL'; // Not Coding Standard
            }
            elseif ($operatorType == 'isNotNull')
            {
                return 'IS NOT NULL'; // Not Coding Standard
            }
            elseif ($operatorType == 'isEmpty')
            {
                return "= ''";
            }
            else
            {
                return "!= ''";
            }
        }

        /**
         * @return boolean
         */
        protected static function isValidOperatorType($type)
        {
            if (in_array($type, array(
                'startsWith',
                'endsWith',
                'contains',
                'equals',
                'doesNotEqual',
                'greaterThanOrEqualTo',
                'lessThanOrEqualTo',
                'greaterThan',
                'lessThan',
                'oneOf',
                'isNull',
                'isNotNull',
                'isEmpty',
                'isNotEmpty')))
            {
                return true;
            }
            return false;
        }

        public static function doesOperatorTypeAllowNullValues($type)
        {
            assert('is_string($type)');
            if (in_array($type, array(
                'isNull',
                'isNotNull',
                'isEmpty',
                'isNotEmpty')))
            {
                return true;
            }
            return false;
        }

        /**
         * Used to validate dynamicStructure for search or filtersStructure for reporting for example. If you have
         * 1 and 2 and 3 and 4, where the numbers are later replaced by valid clauses, this method will ensure the
         * operators are correctly used.
         * @return null or error message
         */
        public static function resolveValidationForATemplateSqlStatementAndReturnErrorMessage($structure, $clauseCount)
        {
            assert('is_string($structure)');
            assert('is_int($clauseCount)');
            $formula = strtolower($structure);
            if (!self::validateParenthesis($formula))
            {
                $errorContent = Zurmo::t('Core', 'Please fix your parenthesis.');
            }
            else
            {
                $formula = str_replace("(", "", $formula);
                $formula = str_replace(")", "", $formula);
                $arguments = preg_split("/or|and/", $formula);
                foreach ($arguments as $argument)
                {
                    $argument = trim($argument);
                    if (!is_numeric($argument) ||
                        !(intval($argument) <= $clauseCount) ||
                        !(intval($argument) > 0) ||
                        !(preg_match("/\./", $argument) === 0) )
                    {
                        $errorContent = Zurmo::t('Core', 'Please use only integers less than {max}.',
                                                          array('{max}' => $clauseCount + 1));
                    }
                }
            }
            if (isset($errorContent))
            {
                return Zurmo::t('Core', 'The structure is invalid. {error}',  array('{error}' => $errorContent));
            }
        }

        /**
         * Function for validation of parenthesis in a formula
         */
        public static function  validateParenthesis($formula)
        {
            $val = 0;
            for ($i = 0; $i <= strlen($formula); $i++)
            {
                $char = substr($formula, $i, 1);
                if ($char === "(")
                {
                    $val += 1;
                }
                elseif ($char === ")")
                {
                    $val -= 1;
                }
                if ($val < 0)
                {
                    return false;
                }
            }
            if ($val !== 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }
?>