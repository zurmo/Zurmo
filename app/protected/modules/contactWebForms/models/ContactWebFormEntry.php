<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ContactWebFormEntry extends Item
    {
        const STATUS_SUCCESS          = 1;

        const STATUS_ERROR            = 2;

        const STATUS_SUCCESS_MESSAGE  = 'Success';

        const STATUS_ERROR_MESSAGE    = 'Error';

        const HASH_INDEX_HIDDEN_FIELD = 'hashIndex';

        public static function getByName($name)
        {
            return ZurmoModelSearch::getModelsByFullName('ContactWebFormEntry', $name);
        }

        protected static function translatedAttributeLabels($language)
        {
            return parent::translatedAttributeLabels($language);
        }

        public static function getModuleClassName()
        {
            return 'ContactWebFormsModule';
        }

        /**
         * Override since Person has its own override.
         * @see RedBeanModel::getLabel
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            if (null != $moduleClassName = static::getModuleClassName())
            {
                return $moduleClassName::getModuleLabelByTypeAndLanguage('Singular', $language);
            }
            return get_called_class();
        }

        /**
         * Override since Person has its own override.
         * @see RedBeanModel::getPluralLabel
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            if (null != $moduleClassName = static::getModuleClassName())
            {
                return $moduleClassName::getModuleLabelByTypeAndLanguage('Plural', $language);
            }
            return static::getLabel($language) . 's';
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
                    'serializedData',
                    'status',
                    'message',
                    'hashIndex'
                ),
                'relations' => array(
                    'contact'            => array(RedBeanModel::HAS_ONE, 'Contact'),
                    'contactWebForm'     => array(RedBeanModel::HAS_ONE, 'ContactWebForm'),
                ),
                'rules' => array(
                    array('serializedData',    'type', 'type' => 'string'),
                    array('status',            'type', 'type' => 'integer'),
                    array('message',           'type', 'type' => 'string'),
                    array('hashIndex',         'type', 'type' => 'string'),
                ),
                'elements' => array(
                ),
                'defaultSortAttribute' => 'status',
                'noAudit' => array(),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getRollUpRulesType()
        {
            return 'ContactWebFormEntry';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getByHashIndex($hashIndex)
        {
            $modelClassName = get_called_class();
            $tableName      = self::getTableName($modelClassName);
            $columnName     = self::getColumnNameByAttribute('hashIndex');
            $beans          = R::find($tableName, "$columnName = '$hashIndex'");
            assert('count($beans) <= 1');
            if (count($beans) == 0)
            {
                return null;
            }
            else
            {
                return RedBeanModel::makeModel(end($beans), $modelClassName);
            }
        }
    }
?>