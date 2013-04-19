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

    class Import extends Item
    {
        protected $tempTableName;

        public function __toString()
        {
            return Zurmo::t('ImportModule', '(Unnamed)');
        }

        public static function getModuleClassName()
        {
            return 'ImportModule';
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
                ),
                'noAudit' => array(
                    'serializedData',
                ),
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
