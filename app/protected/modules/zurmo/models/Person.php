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

    class Person extends OwnedSecurableItem
    {
        public function __toString()
        {
            try
            {
                $fullName = $this->getFullName();
                if ($fullName == '')
                {
                    return Zurmo::t('ZurmoModule', '(Unnamed)');
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

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'department'     => Zurmo::t('ZurmoModule', 'Department', array(), null, $language),
                    'firstName'      => Zurmo::t('ZurmoModule', 'First Name', array(), null, $language),
                    'fullName'       => Zurmo::t('ZurmoModule', 'Name', array(), null, $language),
                    'jobTitle'       => Zurmo::t('ZurmoModule', 'Job Title', array(), null, $language),
                    'lastname'       => Zurmo::t('ZurmoModule', 'Last Name', array(), null, $language),
                    'mobilePhone'    => Zurmo::t('ZurmoModule', 'Mobile Phone', array(), null, $language),
                    'officePhone'    => Zurmo::t('ZurmoModule', 'Office Phone', array(), null, $language),
                    'officeFax'      => Zurmo::t('ZurmoModule', 'Office Fax', array(), null, $language),
                    'primaryAddress' => Zurmo::t('ZurmoModule', 'Primary Address', array(), null, $language),
                    'primaryEmail'   => Zurmo::t('ZurmoModule', 'Primary Email', array(), null, $language),
                    'title'          => Zurmo::t('ZurmoModule', 'Salutation', array(), null, $language)
                )
            );
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Person', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'People', array(), null, $language);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'department',
                    'firstName',
                    'jobTitle',
                    'lastName',
                    'mobilePhone',
                    'officePhone',
                    'officeFax',
                ),
                'relations' => array(
                    'primaryAddress' => array(RedBeanModel::HAS_ONE, 'Address',          RedBeanModel::OWNED,
                                         RedBeanModel::LINK_TYPE_SPECIFIC, 'primaryAddress'),
                    'primaryEmail'   => array(RedBeanModel::HAS_ONE, 'Email',            RedBeanModel::OWNED,
                                         RedBeanModel::LINK_TYPE_SPECIFIC, 'primaryEmail'),
                    'title'          => array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED,
                                         RedBeanModel::LINK_TYPE_SPECIFIC, 'title'),
                ),
                'rules' => array(
                    array('department',     'type',   'type' => 'string'),
                    array('department',     'length', 'min'  => 3, 'max' => 64),
                    array('firstName',      'type',   'type' => 'string'),
                    array('firstName',      'length', 'min'  => 1, 'max' => 32),
                    array('jobTitle',       'type',   'type' => 'string'),
                    array('jobTitle',       'length', 'min'  => 3, 'max' => 64),
                    array('lastName',       'required'),
                    array('lastName',       'type',   'type' => 'string'),
                    array('lastName',       'length', 'min'  => 2, 'max' => 32),
                    array('mobilePhone',    'type',   'type' => 'string'),
                    array('mobilePhone',    'length', 'min'  => 1, 'max' => 24),
                    array('officePhone',    'type',   'type' => 'string'),
                    array('officePhone',    'length', 'min'  => 1, 'max' => 24),
                    array('officeFax',      'type',   'type' => 'string'),
                    array('officeFax',      'length', 'min'  => 1, 'max' => 24),
                ),
                'elements' => array(
                    'mobilePhone'    => 'Phone',
                    'officePhone'    => 'Phone',
                    'officeFax'      => 'Phone',
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

        /**
         * Overriding so when sorting by lastName it sorts bye firstName lastName
         */
        public static function getSortAttributesByAttribute($attribute)
        {
            if ($attribute == 'lastName')
            {
                return array('firstName', $attribute);
            }
            return parent::getSortAttributesByAttribute($attribute);
        }
    }
?>
