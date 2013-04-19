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
                    'comments'  => array(RedBeanModel::HAS_MANY, 'Comment', RedBeanModel::OWNED,
                                         RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'note'      => array(RedBeanModel::HAS_ONE,  'Note'),
                    'files'     => array(RedBeanModel::HAS_MANY, 'FileModel', RedBeanModel::OWNED,
                                         RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'toUser'    => array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED,
                                         RedBeanModel::LINK_TYPE_SPECIFIC, 'toUser'),
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

        public function canUserDelete(User $user)
        {
            assert('$user->id > 0');
            if ($this->toUser->isSame($user) || $this->owner->isSame($user) ||
               Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME)->contains($user))
            {
                return true;
            }
            return false;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'comments'       => Zurmo::t('SocialItemsModule', 'Comments',  array(), null, $language),
                    'description'    => Zurmo::t('ZurmoModule', 'Description',  array(), null, $language),
                    'files'          => Zurmo::t('ZurmoModule',  'Files',  array(), null, $language),
                    'latestDateTime' => Zurmo::t('ActivitiesModule',  'Latest Date Time',  array(), null, $language),
                    'note'           => Zurmo::t('NotesModule', 'Note',  array(), null, $language),
                    'toUser'         => Zurmo::t('SocialItemsModule', 'To User',  array(), null, $language),
                )
            );
        }
    }
?>