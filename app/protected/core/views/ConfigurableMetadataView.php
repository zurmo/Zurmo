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
     * The base View for any view that requires
     * meta data in order to render itself and is
     * configurable.
     */
    abstract class ConfigurableMetadataView extends MetadataView
    {
        /**
         * TODO
         */
        public function getTitle()
        {
            $title = $this->resolveViewAndMetadataValueByName('title');
            $this->resolveEvaluateSubString($title, null);
            return $title;
        }

        public function resolveViewAndMetadataValueByName($name)
        {
            assert('is_string($name)');
            if (!empty($this->viewData[$name]))
            {
                return $this->viewData[$name];
            }
            else
            {
                $metadata = self::getMetadata();
                return $metadata['perUser'][$name];
            }
        }

        /**
         * Returns metadata for use in automatically generating the view.  Will attempt to retrieve from cache if
         * available, otherwill retrieve from database and cache.
         * @see getDefaultMetadata()
         * @param $user The current user.
         * @returns An array of metadata.
         */
        public static function getMetadata(User $user = null)
        {
            $className = get_called_class();
            if ($user == null)
            {
                try
                {
                    return GeneralCache::getEntry($className . 'Metadata');
                }
                catch (NotFoundException $e)
                {
                }
            }
            $metadata = MetadataUtil::getMetadata($className, $user);
            if (YII_DEBUG)
            {
                $className::assertMetadataIsValid($metadata);
            }
            if ($user == null)
            {
                GeneralCache::cacheEntry($className . 'Metadata', $metadata);
            }
            return $metadata;
        }

        /**
         * Sets new metadata.
         * @param $metadata An array of metadata.
         * @param $user The current user.
         */
        public static function setMetadata(array $metadata, User $user = null)
        {
            $className = get_called_class();
            if (YII_DEBUG)
            {
                $className::assertMetadataIsValid($metadata);
            }
            MetadataUtil::setMetadata($className, $metadata, $user);
            if ($user == null)
            {
                GeneralCache::cacheEntry($className . 'Metadata', $metadata);
            }
        }

        protected static function assertMetadataIsValid(array $metadata)
        {
        }
    }
?>
