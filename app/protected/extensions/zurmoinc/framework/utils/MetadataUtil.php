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

        public static function resolveEvaluateSubString(& $subString)
        {
            if (is_array($subString))
            {
                array_walk($subString, self::resolveEvaluateSubString);
                return;
            }
            if (strpos($subString, 'eval:') !== 0)
            {
                return;
            }
            $stringToEvaluate = substr($subString, 5);
            eval("\$subString = $stringToEvaluate;");
        }
    }
?>
