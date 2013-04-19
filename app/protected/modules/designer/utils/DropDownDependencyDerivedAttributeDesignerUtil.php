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
     * Helper class to work with changes to mapped dropDown values in a dropDown dependency derived attribute.
     */
    class DropDownDependencyDerivedAttributeDesignerUtil
    {
        /**
         * Given an array of old and new values for a dropDown, make the appropriate changes to the mappings if needed.
         * @param string $modelClassName
         * @param string $attributeName
         * @param array $oldAndNewValuePairs
         */
        public static function updateValueInMappingByOldAndNewValue($modelClassName,
                                                                    $attributeName,
                                                                    $oldAndNewValuePairs)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('is_array($oldAndNewValuePairs) && count($oldAndNewValuePairs) > 0');
            $attributesMetadata = DropDownDependencyDerivedAttributeMetadata::getAllByModelClassName($modelClassName);
            if (count($attributesMetadata) > 0)
            {
                foreach ($attributesMetadata as $metadata)
                {
                    $saveMetadata   = false;
                    $parentPosition = null;
                    if ($metadata->serializedMetadata != null)
                    {
                        $unserializedMetadata = unserialize($metadata->serializedMetadata);
                        if (isset($unserializedMetadata['mappingData']))
                        {
                            foreach ($oldAndNewValuePairs as $oldValue => $newValue)
                            {
                                foreach ($unserializedMetadata['mappingData'] as $position => $data)
                                {
                                    if ($data['attributeName'] == $attributeName)
                                    {
                                        $parentPosition = $position;
                                        if (isset($data['valuesToParentValues']) &&
                                           isset($data['valuesToParentValues'][$oldValue]))
                                        {
                                            $mappedValue = $unserializedMetadata['mappingData'][$position]
                                                                       ['valuesToParentValues'][$oldValue];
                                            unset($unserializedMetadata['mappingData'][$position]
                                                                       ['valuesToParentValues'][$oldValue]);
                                            $unserializedMetadata['mappingData'][$position]
                                                                       ['valuesToParentValues'][$newValue] = $mappedValue;
                                            $saveMetadata   = true;
                                            break;
                                        }
                                    }
                                }
                                if ($parentPosition !== null)
                                {
                                    $nextPosition = $parentPosition + 1;
                                    if (isset($unserializedMetadata['mappingData'][$nextPosition]) &&
                                       isset($unserializedMetadata['mappingData'][$nextPosition]['valuesToParentValues']))
                                    {
                                        foreach ($unserializedMetadata['mappingData'][$nextPosition]
                                                                     ['valuesToParentValues'] as $value => $parentValue)
                                        {
                                            if ($parentValue == $oldValue)
                                            {
                                                $unserializedMetadata['mappingData'][$nextPosition]
                                                                     ['valuesToParentValues'][$value] = $newValue;
                                                $saveMetadata = true;
                                            }
                                        }
                                    }
                                    $parentPosition = null;
                                }
                            }
                        }
                    }
                    if ($saveMetadata)
                    {
                        $metadata->serializedMetadata = serialize($unserializedMetadata);
                        $saved = $metadata->save();
                        if (!$saved)
                        {
                            throw new NotSupportedException();
                        }
                    }
                }
            }
        }

        /**
         * Given an array of customFieldData's data, resolve if any data has been removed and is currently mapped.  If it
         * is mapped remove it.
         * @param string $modelClassName
         * @param string $attributeName
         * @param array $customFieldDataData
         */
        public static function resolveValuesInMappingWhenValueWasRemoved($modelClassName,
                                                                         $attributeName,
                                                                         $customFieldDataData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('is_array($customFieldDataData)');
            $attributesMetadata = DropDownDependencyDerivedAttributeMetadata::getAllByModelClassName($modelClassName);
            if (count($attributesMetadata) > 0)
            {
                foreach ($attributesMetadata as $metadata)
                {
                    $saveMetadata   = false;
                    $parentPosition = null;
                    if ($metadata->serializedMetadata != null)
                    {
                        $unserializedMetadata = unserialize($metadata->serializedMetadata);
                        if (isset($unserializedMetadata['mappingData']))
                        {
                            foreach ($unserializedMetadata['mappingData'] as $position => $data)
                            {
                                if ($data['attributeName'] == $attributeName)
                                {
                                    $parentPosition = $position;
                                    if (isset($data['valuesToParentValues']))
                                    {
                                        foreach ($data['valuesToParentValues'] as $value => $parentValue)
                                        {
                                            if (!in_array($value, $customFieldDataData))
                                            {
                                                unset($unserializedMetadata['mappingData'][$position]
                                                                       ['valuesToParentValues'][$value]);
                                                $saveMetadata   = true;
                                            }
                                        }
                                    }
                                }
                            }
                            if ($parentPosition !== null)
                            {
                                $nextPosition = $parentPosition + 1;
                                if (isset($unserializedMetadata['mappingData'][$nextPosition]) &&
                                   isset($unserializedMetadata['mappingData'][$nextPosition]['valuesToParentValues']))
                                {
                                    foreach ($unserializedMetadata['mappingData'][$nextPosition]
                                                                 ['valuesToParentValues'] as $value => $parentValue)
                                    {
                                        if (!in_array($parentValue, $customFieldDataData))
                                        {
                                            $unserializedMetadata['mappingData'][$nextPosition]
                                                                 ['valuesToParentValues'][$value] = null;
                                            $saveMetadata   = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($saveMetadata)
                    {
                        $metadata->serializedMetadata = serialize($unserializedMetadata);
                        $saved = $metadata->save();
                        if (!$saved)
                        {
                            throw new NotSupportedException();
                        }
                    }
                }
            }
        }
    }
?>