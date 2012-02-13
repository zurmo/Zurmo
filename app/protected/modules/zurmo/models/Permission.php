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

    class Permission extends OwnedModel
    {
        const ALLOW              = 0x1;
        const DENY               = 0x2;
        const ALLOW_DENY         = 0x3;

        const NONE               = 0x00;
        const READ               = 0x01;
        const WRITE              = 0x02;
        const DELETE             = 0x04;
        const CHANGE_PERMISSIONS = 0x08;
        const CHANGE_OWNER       = 0x10;
        const ALL                = 0x1F;

        // These are for convenience for the most common
        // combinations, not an excuse to not know how to
        // use & | and ~ to do bitwise operations to
        // do all the possible necessary combinations.
        const READ_WRITE                                 = 0x03;
        const READ_DELETE                                = 0x05;
        const READ_WRITE_DELETE                          = 0x07;
        const READ_WRITE_CHANGE_PERMISSIONS              = 0xB;
        const READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER = 0x1B;

        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            if ($bean === null)
            {
                $this->permissions = Permission::NONE;
            }
        }

        public static function removeForPermitable(Permitable $permitable)
        {
            PermissionsCache::forgetAll();
            R::exec("delete from permission where permitable_id = :id;",
                    array('id' => $permitable->getClassId('Permitable')));
        }

        public static function removeAll()
        {
            PermissionsCache::forgetAll();
            R::exec("delete from permission;");
        }

        public static function permissionsToString($permissions)
        {
            assert('is_int($permissions) || is_numeric($permissions) && is_string($permissions)');
            assert("(intval($permissions) & ~Permission::ALL) == 0");
            $s  = $permissions & self::READ               ? 'R' : '-';
            $s .= $permissions & self::WRITE              ? 'W' : '-';
            $s .= $permissions & self::DELETE             ? 'D' : '-';
            $s .= $permissions & self::CHANGE_PERMISSIONS ? 'P' : '-';
            $s .= $permissions & self::CHANGE_OWNER       ? 'O' : '-';
            return $s;
        }

        public function __toString()
        {
            try
            {
                $this->castDownPermitable();
                $s = strval($this->permitable);
            }
            catch (NotFoundException $e)
            {
                $s = Yii::t('Default', '(Unknown)');
            }
            $s .= ':';
            $s .= $this->type == self::ALLOW ? Yii::t('Default', 'Allow') :
                                               Yii::t('Default', 'Deny');
            $s .= ':' . self::permissionsToString($this->permissions);
            return $s;
        }

        public function getEffectivePermissions(Permitable $permitable)
        {
            try
            {
                $this->castDownPermitable();
                if ($this->permitable->contains($permitable))
                {
                assert('is_int($this->permissions) || is_string($this->permissions)');
                assert("(intval({$this->permissions}) & ~Permission::ALL) == 0");
                    return $this->permissions;
                }
            }
            catch (NotFoundException $e)
            {
                // Not finding anything will deny permissions.
                // It may be better to send the complain up. TBD.
                assert('On no!');
            }
            return Permission::NONE;
        }

        public function getExplicitPermissions(Permitable $permitable)
        {
            try
            {
                $this->castDownPermitable();
                if ($this->permitable->isSame($permitable))
                {
                    assert('is_int($this->permissions) || is_string($this->permissions)');
                    assert("(intval({$this->permissions}) & ~Permission::ALL) == 0");
                    return $this->permissions;
                }
            }
            catch (NotFoundException $e)
            {
                // Not finding anything will deny permissions.
                // It may be better to send the complain up. TBD.
                assert('On no!');
            }
            return Permission::NONE;
        }

        public function getInheritedPermissions(Permitable $permitable)
        {
            try
            {
                $this->castDownPermitable();
                if ($this->permitable->contains($permitable) &&
                    !$this->permitable->isSame($permitable))
                {
                    assert('is_int($this->permissions) || is_string($this->permissions)');
                    assert("(intval({$this->permissions}) & ~Permission::ALL) == 0");
                    return $this->permissions;
                }
            }
            catch (NotFoundException $e)
            {
                // Not finding anything will deny permissions.
                // It may be better to send the complain up. TBD.
                assert('On no!');
            }
            return Permission::NONE;
        }

        // See comments on RedBeanModel::castDown() and
        // RedBeanModel::testDownCast() to see why
        // this (apparent/actual dodginess) is needed.
        public function castDownPermitable()
        {
            if (get_class($this->permitable) == 'Permitable')
            {
                //Set the permitable to null first otherwise it will not take the new casted down permitable and
                //remains uncasted down.
                $permitable = $this->permitable->castDown(array('Group', 'User'));
                $this->permitable = null;
                $this->permitable = $permitable;
            }
        }

        // This is an issue that should be addressed.
        // The stringification of everything coming out of the database.
        public function __get($attributeName)
        {
            if ($attributeName == 'permissions')
            {
                $permissions = $this::unrestrictedGet("permissions");
                assert('$permissions === null ||
                        (is_int($permissions)  ||
                         is_numeric($permissions) && is_string($permissions)) &&
                         (intval($permissions) & ~Permission::ALL) == 0');
                return intval($permissions);
            }
            return parent::__get($attributeName);
        }

        // How to get rid of this if not YII_DEBUG or assertions are off?
        public function __set($attributeName, $value)
        {
            assert('$attributeName != "permissions" ||
                    $attributeName == "permissions" && is_int($value) && ($value & ~Permission::ALL) == 0');
            parent::__set($attributeName, $value);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'permissions',
                ),
                'relations' => array(
                    'permitable'     => array(RedBeanModel::HAS_ONE,             'Permitable'),
                    'securableItem'  => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'SecurableItem'),
                ),
                'rules' => array(
                    array('type',        'required'),
                    array('type',        'type', 'type' => 'integer'),
                    array('type',        'numerical', 'min' => 1, 'max' => 2),
                    array('permissions', 'required'),
                    array('permissions', 'type', 'type' => 'integer'),
                    array('permissions', 'numerical', 'min' => 0, 'max' => 31),
                    array('permitable',  'required'),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>
