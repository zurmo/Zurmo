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
     * TODO
     */
    class MetadataUtil
    {
        /**
         * Returns metadata for the given class.
         * @see getDefaultMetadata()
         * @param $user The current user.
         * @returns An array of metadata.
         */
        public static function getMetadata($className, User $user = null)
        {
            assert('is_string($className) && $className != ""');
            $metadata = $className::getDefaultMetadata();
            if ($user instanceof User)
            {
                try
                {
                    $perUserMetadata = PerUserMetadata::getByClassNameAndUser($className, $user);
                    $metadata['perUser'] = unserialize($perUserMetadata->serializedMetadata);
                }
                catch (NotFoundException $e)
                {
                }
            }
            try
            {
                $globalMetadata = GlobalMetadata::getByClassName($className);
                $unserializedMetadata = unserialize($globalMetadata->serializedMetadata);
                if (!empty($unserializedMetadata))
                {
                    $metadata['global'] = $unserializedMetadata;
                }
            }
            catch (NotFoundException $e)
            {
            }
            return $metadata;
        }

        /**
         * Sets new metadata.
         * @param $metadata An array of metadata.
         * @param $user The current user.
         */
        public static function setMetadata($className, array $metadata, User $user = null)
        {
            assert('is_string($className) && $className != ""');
            if ($user instanceof User && isset($metadata['perUser']))
            {
                try
                {
                    $perUserMetadata = PerUserMetadata::getByClassNameAndUser($className, $user);
                }
                catch (NotFoundException $e)
                {
                    $perUserMetadata = new PerUserMetadata();
                    $perUserMetadata->className = $className;
                    $perUserMetadata->user = $user;
                }
                $perUserMetadata->serializedMetadata = serialize($metadata['perUser']);
                $saved = $perUserMetadata->save();
                assert('$saved');
            }
            if (isset($metadata['global']))
            {
                try
                {
                    $globalMetadata = GlobalMetadata::getByClassName($className);
                }
                catch (NotFoundException $e)
                {
                    $globalMetadata = new GlobalMetadata();
                    $globalMetadata->className = $className;
                }
                $globalMetadata->serializedMetadata = serialize($metadata['global']);
                $saved = $globalMetadata->save();
                assert('$saved');
            }
        }

        /**
         * @param $subString String|Array to be evaluated
         * @param null $resolveVariableName string|array Name the variable to be resolved in local scope
         * @param null $params string|array
         * @param null $defaultValue string
         */
        public static function resolveEvaluateSubString(& $subString, $resolveVariableName = null, $params = null, $defaultValue = null)
        {
            if (is_array($subString))
            {
                foreach ($subString as $subStringNodeKey => $subStringNodeValue)
                {
                    self::resolveEvaluateSubString($subString[$subStringNodeKey], $resolveVariableName, $params, $defaultValue);
                }
                return;
            }
            if (strpos($subString, 'eval:') !== 0)
            {
                return;
            }
            if ($resolveVariableName !== null)
            {
                if (is_array($resolveVariableName))
                {
                    foreach ($resolveVariableName as $index => $variableName)
                    {
                        if (is_array($params) && array_key_exists($index, $params))
                        {
                            $$variableName = $params[$index];
                        }
                        elseif (!is_array($params) && $params !== null)
                        {
                            $$variableName = $params;
                        }
                        else
                        {
                            $$variableName = $defaultValue;
                        }
                    }
                }
                else
                {
                    if ($params !== null)
                    {
                        $$resolveVariableName = $params;
                    }
                    else
                    {
                        $$resolveVariableName = $defaultValue;
                    }
                }
            }
            $stringToEvaluate = substr($subString, 5);
            eval("\$subString = $stringToEvaluate;");
        }
    }
?>
