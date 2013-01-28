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

    class SocialItem extends OwnedSecurableItem
    {
        public static function getByDescription($description)
        {
            assert('is_string($description) && $description != ""');
            return self::getSubset(null, null, null, "description = '$description'");
        }

        public function onCreated()
        {
            parent::onCreated();
            $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public function __toString()
        {
            if (trim($this->description) == '')
            {
                return Zurmo::t('SocialItemsModule', '(Unnamed)');
            }
            return $this->description;
        }

        public static function getModuleClassName()
        {
            return 'SocialItemsModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'latestDateTime',
                ),
                'relations' => array(
                    'comments'  => array(RedBeanModel::HAS_MANY, 'Comment', RedBeanModel::OWNED, 'relatedModel'),
                    'note'      => array(RedBeanModel::HAS_ONE,  'Note'),
                    'files'     => array(RedBeanModel::HAS_MANY, 'FileModel', RedBeanModel::OWNED, 'relatedModel'),
                    'toUser'    => array(RedBeanModel::HAS_ONE,  'User'),
                ),
                'rules' => array(
                    array('description',    'type',     'type' => 'string'),
                    array('description',    'required', 'on'   => 'createPost'),
                    array('latestDateTime', 'required'),
                    array('latestDateTime', 'readOnly'),
                    array('latestDateTime', 'type', 'type' => 'datetime'),
                ),
                'elements' => array(
                    'description'        => 'TextArea',
                    'files'              => 'Files',
                    'latestDateTime'     => 'DateTime'
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'SocialItemGamification';
        }

        /**
         * Update latestDateTime based on new related comments
         * (non-PHPdoc)
         * @see Item::beforeSave()
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if ($this->comments->isModified() || $this->getIsNewModel())
                {
                    $this->unrestrictedSet('latestDateTime', DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }
    }
?>