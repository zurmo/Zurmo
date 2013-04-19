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

    class Note extends MashableActivity
    {
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('description', $name);
        }

        public function __toString()
        {
            try
            {
                $description  = trim($this->description);
                if ($description == '')
                {
                    $description = Zurmo::t('NotesModule', '(Unnamed)');
                }
                return $description;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'NotesModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'description',
                    'occurredOnDateTime',
                ),
                'relations' => array(
                    'files'       => array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED,
                                           RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'socialItems' => array(RedBeanModel::HAS_MANY,  'SocialItem', RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('description',        'required'),
                    array('description',        'type',   'type' => 'string'),
                    array('occurredOnDateTime', 'type', 'type' => 'datetime'),
                    array('occurredOnDateTime', 'dateTimeDefault', 'value' => DateTimeCalculatorUtil::NOW),
                    ),
                'elements' => array(
                    'description'        => 'TextArea',
                    'files'              => 'Files',
                    'occurredOnDateTime' => 'DateTime'
                ),
                'defaultSortAttribute' => 'occurredOnDateTime',
            );
            return $metadata;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'occurredOnDateTime'       => Zurmo::t('NotesModule', 'Occurred On', array(), null, $language),
                )
            );
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (array_key_exists('occurredOnDateTime', $this->originalAttributeValues) &&
                    $this->occurredOnDateTime != null)
                {
                    $this->unrestrictedSet('latestDateTime', $this->occurredOnDateTime);
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function getMashableActivityRulesType()
        {
            return 'Note';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'NoteGamification';
        }
    }
?>
