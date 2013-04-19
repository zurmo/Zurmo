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

    class NamedSecurableItem extends SecurableItem
    {
        /**
         * Given a name, check the cache if the model is cached and return. Otherwise check the database for the record,
         * cache and return this model.
         * @param string $name
         */
        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            try
            {
                return GeneralCache::getEntry('NamedSecurableItem' . $name);
            }
            catch (NotFoundException $e)
            {
                $bean = R::findOne('namedsecurableitem', "name = :name ", array(':name' => $name));
                assert('$bean === false || $bean instanceof RedBean_OODBBean');
                if ($bean === false)
                {
                    $model = new NamedSecurableItem();
                    $model->unrestrictedSet('name', $name);
                }
                else
                {
                    $model = self::makeModel($bean);
                }
            }
            GeneralCache::cacheEntry('NamedSecurableItem' . $name, $model);
            return $model;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'rules' => array(
                    array('name', 'required'),
                    array('name', 'unique'),
                    array('name', 'type',   'type' => 'string'),
                    array('name', 'length', 'min'  => 3, 'max' => 64),
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Any changes to the model must be re-cached.
         * @see RedBeanModel::save()
         */
        public function save($runValidation = true, array $attributeNames = null)
        {
            $saved = parent::save($runValidation, $attributeNames);
            if ($saved)
            {
                GeneralCache::cacheEntry('NamedSecurableItem' . $this->name, $this);
            }
            return $saved;
        }

        /**
         * Override to add caching capabilities of this information.
         * @see SecurableItem::getActualPermissions()
         */
        public function getActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            if ($this->name != null)
            {
                try
                {
                    return PermissionsCache::getNamedSecurableItemActualPermissions($this->name, $permitable);
                }
                catch (NotFoundException $e)
                {
                    $actualPermissions = parent::getActualPermissions($permitable);
                }
                PermissionsCache::cacheNamedSecurableItemActualPermissions($this->name, $permitable, $actualPermissions);
                return $actualPermissions;
            }
            return parent::getActualPermissions($permitable);
        }

        /**
         * Override for the 'name' attribute since 'name' can be retrieved regardless of permissions of the user asking
         * for it.
         * @see SecurableItem::__get()
         */
        public function __get($attributeName)
        {
            if ($attributeName == 'name')
            {
                return $this->unrestrictedGet('name');
            }
            return parent::__get($attributeName);
        }
    }
?>