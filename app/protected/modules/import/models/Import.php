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

    class Import extends Item
    {
        protected $tempTableName;

        public function __toString()
        {
            return Yii::t('Default', '(Unnamed)');
        }

        public static function getModuleClassName()
        {
            return 'ImportModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel()
        {
            return 'ImportsModuleSingularLabel';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            return 'ImportModulePluralLabel';
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
                    'serializedData',
                ),
                'rules' => array(
                    array('serializedData',  'required'),
                    array('serializedData',  'type', 'type' => 'string'),
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Returns the string name of the temp table in the database used for the import data.
         * @throws NotSupportedException
         * @return Temporary table id if the import model has a valid id.
         */
        public function getTempTableName()
        {
            if ($this->id <= 0 )
            {
                throw new NotSupportedException();
            }
            if ($this->tempTableName != null)
            {
                return $this->tempTableName;
            }
            $this->tempTableName = 'importtable' . $this->id;
            return $this->tempTableName;
        }

        public function setTempTableName($name)
        {
            assert('is_string($name)');
            $this->tempTableName = $name;
        }

        protected function beforeDelete()
        {
            if (!parent::beforeDelete())
            {
                return false;
            }
            $sql = 'Drop table if exists ' . $this->getTempTableName();
            R::exec($sql);
            return true;
        }
    }
?>
