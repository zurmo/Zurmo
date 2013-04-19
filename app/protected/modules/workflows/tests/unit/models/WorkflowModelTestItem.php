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

    class WorkflowModelTestItem extends OwnedSecurableItem
    {
        public static function getByLastName($lastName)
        {
            return self::getByNameOrEquivalent('lastName', $lastName);
        }

        public function __toString()
        {
            try
            {
                $fullName = $this->getFullName();
                if ($fullName == '')
                {
                    return Zurmo::t('WorkflowsModule', '(Unnamed)');
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
                    'likeContactState' => 'A name for a state'
                )
            );
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'firstName',
                    'lastName',
                    'boolean',
                    'boolean2',
                    'date',
                    'date2',
                    'date3',
                    'date4',
                    'dateTime',
                    'dateTime2',
                    'dateTime3',
                    'dateTime4',
                    'float',
                    'integer',
                    'cannotTrigger',
                    'phone',
                    'string',
                    'textArea',
                    'url',
            ),
                'relations' => array(
                    'currencyValue'       => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED),
                    'dropDown'            => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'dropDown'),
                    'dropDown2'            => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'dropDown2'),
                    'radioDropDown'       => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'radioDropDown'),
                    'multiDropDown'       => array(RedBeanModel::HAS_ONE,   'OwnedMultipleValuesCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'multiDropDown'),
                    'tagCloud'            => array(RedBeanModel::HAS_ONE,   'OwnedMultipleValuesCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'tagCloud'),
                    'hasOne'              => array(RedBeanModel::HAS_ONE,   'WorkflowModelTestItem2', RedBeanModel::NOT_OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'hasOne'),
                    'hasOneAgain'         => array(RedBeanModel::HAS_ONE,   'WorkflowModelTestItem2', RedBeanModel::NOT_OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'hasOneAgain'),
                    'hasMany'             => array(RedBeanModel::HAS_MANY,  'WorkflowModelTestItem3'),
                    'hasOneAlso'          => array(RedBeanModel::HAS_ONE,   'WorkflowModelTestItem4', RedBeanModel::NOT_OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'hasOneAlso'),
                    'primaryEmail'        => array(RedBeanModel::HAS_ONE,   'Email', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'primaryEmail'),
                    'primaryAddress'      => array(RedBeanModel::HAS_ONE,   'Address', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'primaryAddress'),
                    'secondaryEmail'      => array(RedBeanModel::HAS_ONE,   'Email', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'secondaryEmail'),
                    'cannotTrigger2'      => array(RedBeanModel::MANY_MANY, 'WorkflowModelTestItem5'),
                    'usedAsAttribute' => array(RedBeanModel::HAS_ONE, 'WorkflowModelTestItem6', RedBeanModel::NOT_OWNED),
                    'likeContactState'    => array(RedBeanModel::HAS_ONE, 'ContactState', RedBeanModel::NOT_OWNED),
                    'user'                => array(RedBeanModel::HAS_ONE, 'User', RedBeanModel::NOT_OWNED),
                    'user2'               => array(RedBeanModel::HAS_ONE, 'User', RedBeanModel::NOT_OWNED),
                ),
                'derivedRelationsViaCastedUpModel' => array(
                    'model5ViaItem' => array(RedBeanModel::MANY_MANY, 'WorkflowModelTestItem5', 'workflowItems')
                ),
                'rules' => array(
                    array('firstName', 'type',   'type' => 'string'),
                    array('firstName', 'length', 'min'  => 1, 'max' => 32),
                    array('lastName',  'required'),
                    array('lastName',  'type',   'type' => 'string'),
                    array('lastName',  'length', 'min'  => 2, 'max' => 32),
                    array('boolean',   'boolean'),
                    array('boolean2',  'boolean'),
                    array('date',      'type', 'type' => 'date'),
                    array('date2',     'type', 'type' => 'date'),
                    array('date3',     'type', 'type' => 'date'),
                    array('date4',     'type', 'type' => 'date'),
                    array('dateTime',  'type', 'type' => 'datetime'),
                    array('dateTime2', 'type', 'type' => 'datetime'),
                    array('dateTime3', 'type', 'type' => 'datetime'),
                    array('dateTime4', 'type', 'type' => 'datetime'),
                    array('float',     'type',    'type' => 'float'),
                    array('float',     'length',  'min'  => 2, 'max' => 64),
                    array('integer',   'type',    'type' => 'integer'),
                    array('integer',   'length',  'min'  => 2, 'max' => 64),
                    array('cannotTrigger',    'type',  'type' => 'string'),
                    array('cannotTrigger',    'length',  'min'  => 3, 'max' => 64),
                    array('phone',     'type',    'type' => 'string'),
                    array('phone',     'length',  'min'  => 1, 'max' => 14),
                    array('string',    'required'),
                    array('string',    'type',  'type' => 'string'),
                    array('string',    'length',  'min'  => 3, 'max' => 64),
                    array('textArea',  'type',    'type' => 'string'),
                    array('url',       'url'),
                ),
                'elements' => array(
                    'currencyValue'       => 'CurrencyValue',
                    'date'                => 'Date',
                    'date2'               => 'Date',
                    'date3'               => 'Date',
                    'date4'               => 'Date',
                    'dateTime'            => 'DateTime',
                    'dateTime2'           => 'DateTime',
                    'dateTime3'           => 'DateTime',
                    'dateTime4'           => 'DateTime',
                    'dropDown'            => 'DropDown',
                    'dropDown2'           => 'DropDown',
                    'hasOne'              => 'ImportModelTestItem2',
                    'hasOneAlso'          => 'ImportModelTestItem4',
                    'likeContactState'    => 'ContactState',
                    'phone'               => 'Phone',
                    'primaryEmail'        => 'EmailAddressInformation',
                    'secondaryEmail'      => 'EmailAddressInformation',
                    'primaryAddress'      => 'Address',
                    'textArea'            => 'TextArea',
                    'radioDropDown'       => 'RadioDropDown',
                    'multiDropDown'       => 'MultiSelectDropDown',
                    'tagCloud'            => 'TagCloud',
                    'user'                => 'User',
                    'user2'               => 'User',
                ),
                'customFields' => array(
                    'dropDown'        => 'WorkflowTestDropDown',
                    'radioDropDown'   => 'WorkflowTestRadioDropDown',
                    'multiDropDown'   => 'WorkflowTestMultiDropDown',
                    'tagCloud'        => 'WorkflowTestTagCloud',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'WorkflowsTestModule';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }
    }
?>
