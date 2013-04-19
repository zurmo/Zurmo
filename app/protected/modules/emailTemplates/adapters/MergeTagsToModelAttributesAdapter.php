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

    /*
     * This class is responsible from converting merge tags to relevant attribute values,
     *  apply any language translations and returning the final value.
     */
    class MergeTagsToModelAttributesAdapter
    {
        const PROPERTY_NOT_FOUND = "!MERGETAG-TO-ATTR-FAILED";

        public static function resolveMergeTagsArrayToAttributesFromModel(& $mergeTags, $model, & $invalidTags = array(), $language = 'en', $errorOnFirstMissing = false)
        {
            $resolvedMergeTags = array();
            foreach ($mergeTags as $mergeTag)
            {
                $attributeAccessorString    = static::resolveStringToAttributeAccessor($mergeTag);
                $timeQualifier              = static::stripTimeDelimiterAndReturnQualifier($attributeAccessorString);
                $resolvedValue              =  static::resolveMergeTagToStandardOrRelatedAttribute($attributeAccessorString, $model, $language, $timeQualifier);
                if ($resolvedValue === static::PROPERTY_NOT_FOUND)
                {
                    if ($errorOnFirstMissing)
                    {
                        return false;
                    }
                    else
                    {
                        $invalidTags[] = $mergeTag;
                    }
                }
                else
                {
                    $resolvedMergeTags[$mergeTag] = $resolvedValue;
                }
            }
            $mergeTags = $resolvedMergeTags;
            return (empty($invalidTags))? true: false;
        }

        protected static function stripTimeDelimiterAndReturnQualifier(& $mergeTag)
        {
            $timeDelimiterIndex = strpos($mergeTag, MergeTagsUtil::TIME_DELIMITER);
            if ($timeDelimiterIndex !== false)
            {
                $timeQualifier  = substr($mergeTag, 0, $timeDelimiterIndex);
                $mergeTag       = substr($mergeTag, $timeDelimiterIndex + 1);
                return $timeQualifier;
            }
            else
            {
                return null;
            }
        }

        protected static function resolveMergeTagToStandardOrRelatedAttribute($attributeAccessorString, $model, $language, $timeQualifier)
        {
            $attributeName = strtok($attributeAccessorString, '->');
            $modelAttributeAdapter = new ModelAttributesAdapter($model);
            if (!$model->isAttribute($attributeName))
            {
                return static::PROPERTY_NOT_FOUND;
            }
            elseif ($model->$attributeName instanceof CustomField)
            {
                $value = static::getAttributeValue($model->$attributeName, 'value', $timeQualifier);
                // TODO: @Shoaibi/@Jason: Low: need to apply localizations(Date/time/currency formats, ...) here besides translation
                return (isset($value)) ? Zurmo::t($model::getModuleClassName(), $value, array(), null, $language) : null;
            }
            elseif ($model->isRelation($attributeName))
            {
                $model = $model->$attributeName;
                if ($attributeName === $attributeAccessorString) // We have name of relation, don't have a property requested, like $object->owner
                {
                    $attributeAccessorString = null;
                }
                else
                {
                    $attributeAccessorString = str_replace($attributeName . '->', '', $attributeAccessorString);
                }
                if (empty($attributeAccessorString))
                {
                    // If a user specific a relation merge tag but not a property, we assume he meant "value" property.
                    if (empty($timeQualifier))
                    {
                        return strval($model);
                    }
                    else
                    {
                        return static::PROPERTY_NOT_FOUND;
                    }
                }
                return static::resolveMergeTagToStandardOrRelatedAttribute($attributeAccessorString, $model, $language, $timeQualifier);
            }
            elseif ($modelAttributeAdapter->isStandardAttribute($attributeName))
            {
                if ($attributeName === $attributeAccessorString) // we don't have any accessor operator after the attributeName e.g. its the last in list
                {
                    return static::getAttributeValue($model, $attributeName, $timeQualifier);
                }
                else
                {
                    return static::PROPERTY_NOT_FOUND;
                }
            }
            else
            {
                // Don't really need this as null would be return implicitly if we exclude this,
                // so basically this is just to avoid IDE warnings to not returning anything
                return null;
            }
        }

        protected static function getAttributeValue($model, $attributeName, $timeQualifier)
        {
            return (empty($timeQualifier)) ?
                        static::getAttributeCurrentValue($model, $attributeName) :
                        static::getAttributePreviousValue($model, $attributeName);
        }

        protected static function getAttributeCurrentValue($model, $attributeName)
        {
            return (isset($model->$attributeName))? $model->$attributeName : null;
        }

        protected static function getAttributePreviousValue($model, $attributeName)
        {
            if (property_exists($model, 'originalAttributeValues') || $model->isAttribute('originalAttributeValues'))
            {
                if (isset($model->originalAttributeValues[$attributeName]))
                {
                    return $model->originalAttributeValues[$attributeName];
                }
                else
                {
                    if (isset($model->$attributeName))
                    {
                        return $model->$attributeName;
                    }
                }
            }
            else
            {
                return static::PROPERTY_NOT_FOUND;
            }
            return null;
        }

        protected static function resolveStringToAttributeAccessor($string)
        {
            return static::resolveStringToCamelCase(str_replace(MergeTagsUtil::PROPERTY_DELIMITER, '->', strtolower($string)));
        }

        protected static function resolveStringToCamelCase($string, $capitaliseFirstChar = false )
        {
            if ($capitaliseFirstChar)
            {
                $string[0] = strtoupper($string[0]);
            }
            $covertToCamelCaseFunction = create_function('$character', 'return strtoupper($character[1]);');
            return preg_replace_callback('/' . preg_quote(MergeTagsUtil::CAPITAL_DELIMITER) . '([a-z])/',
                                                                                    $covertToCamelCaseFunction, $string);
        }
    }
?>