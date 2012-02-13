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

    class Person extends OwnedSecurableItem
    {
        public function __toString()
        {
            try
            {
                $fullName = $this->getFullName();
                if ($fullName == '')
                {
                    return Yii::t('Default', '(Unnamed)');
                }
                return $fullName;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public function getFullName()
        {
            $fullName = array();
            if ($this->firstName != '')
            {
                $fullName[] = $this->firstName;
            }
            if ($this->lastName != '')
            {
                $fullName[] = $this->lastName;
            }
            return join(' ' , $fullName);
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'fullName' => 'Name',
                    'title'    => 'Salutation',
                )
            );
        }

        protected static function getPluralLabel()
        {
            return 'People';
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'firstName',
                    'lastName',
                    'jobTitle',
                    'department',
                    'officePhone',
                    'mobilePhone',
                    'officeFax',
                ),
                'relations' => array(
                    'title'          => array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED),
                    'primaryAddress' => array(RedBeanModel::HAS_ONE, 'Address',          RedBeanModel::OWNED),
                    'primaryEmail'   => array(RedBeanModel::HAS_ONE, 'Email',            RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('firstName',      'type',   'type' => 'string'),
                    array('firstName',      'length', 'min'  => 1, 'max' => 32),
                    array('lastName',       'required'),
                    array('lastName',       'type',   'type' => 'string'),
                    array('lastName',       'length', 'min'  => 2, 'max' => 32),
                    array('jobTitle',       'type',   'type' => 'string'),
                    array('jobTitle',       'length', 'min'  => 3, 'max' => 64),
                    array('department',     'type',   'type' => 'string'),
                    array('department',     'length', 'min'  => 3, 'max' => 64),
                    array('officePhone',    'type',   'type' => 'string'),
                    array('officePhone',    'length', 'min'  => 1, 'max' => 16),
                    array('officeFax',      'type',   'type' => 'string'),
                    array('officeFax',      'length', 'min'  => 1, 'max' => 16),
                    array('mobilePhone',    'type',   'type' => 'string'),
                    array('mobilePhone',    'length', 'min'  => 1, 'max' => 16),
                ),
                'elements' => array(
                    'officePhone'    => 'Phone',
                    'officeFax'      => 'Phone',
                    'mobilePhone'    => 'Phone',
                    'primaryEmail'   => 'EmailAddressInformation',
                    'primaryAddress' => 'Address',
                ),
                'customFields' => array(
                    'title' => 'Titles',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return false;
        }
    }
?>
