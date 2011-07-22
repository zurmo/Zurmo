<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class ContactsModule extends SecurableModule
    {
        const RIGHT_CREATE_CONTACTS = 'Create Contacts';
        const RIGHT_ACCESS_CONTACTS = 'Access Contacts Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('Contact', 'ContactsFilteredList');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'tabMenuItems' => array(
                    array(
                        'label' => 'ContactsModulePluralLabel',
                        'url'   => array('/contacts/default'),
                        'right' => self::RIGHT_ACCESS_CONTACTS,
                        'items' => array(
                            array(
                                'label' => 'Create ContactsModuleSingularLabel',
                                'url'   => array('/contacts/default/create'),
                                'right' => self::RIGHT_CREATE_CONTACTS
                            ),
                            array(
                                'label' => 'ContactsModulePluralLabel',
                                'url'   => array('/contacts/default'),
                                'right' => self::RIGHT_ACCESS_CONTACTS
                            ),
                        ),
                    ),
                ),
                'designerMenuItems' => array(
                    'showFieldsLink' => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink' => true,
                ),
                'startingState' => 1
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Contact';
        }

        /**
         * Used on first load to install ContactState data
         * and the startingState for the Contacts module.
         * @return true/false if data was in fact loaded.
         */
        public static function loadStartingData()
        {
            if (count(ContactState::GetAll()) != 0)
            {
                return false;
            }
            $data = array(
                'New',
                'In Progress',
                'Recycled',
                'Dead',
                'Qualified',
                'Customer'
            );
            $order = 0;
            $startingStateId = null;
            foreach ($data as $stateName)
            {
                $state        = new ContactState();
                $state->name  = $stateName;
                $state->order = $order;
                $saved        = $state->save();
                assert('$saved');
                if ($stateName == 'Qualified')
                {
                    $startingStateId = $state->id;
                }
                $order++;
            }
            if ($startingStateId == null)
            {
                throw new NotSupportedException();
            }
            $metadata = ContactsModule::getMetadata();
            $metadata['global']['startingStateId'] = $startingStateId;
            ContactsModule::setMetadata($metadata);
            assert('count(ContactState::GetAll()) == 6');
            return true;
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_CONTACTS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_CONTACTS;
        }

        /**
         * Override since the ContactsModule controls module permissions for both leads and contacts.
         */
        public static function getSecurableModuleDisplayName()
        {
            $label = static::getModuleLabelByTypeAndLanguage('Plural') . '&#160;&#38;&#160;' .
                     LeadsModule::getModuleLabelByTypeAndLanguage('Plural');
            return $label;
        }
    }
?>
