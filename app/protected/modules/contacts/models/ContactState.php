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

    /**
     * Stores state information for Contacts.
     */
    class ContactState extends RedBeanModel
    {
        public static function getByName($name)
        {
            assert('is_string($name) && $name != ""');
            return self::makeModels(R::find('contactstate', "name = :name ", array(':name' => $name)));
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'name'   => Zurmo::t('ZurmoModule', 'Name',  array(), null, $language),
                    'order'  => Zurmo::t('CoreModule', 'Order', array(), null, $language),
                )
            );
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('ContactsModule', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'order',
                    'serializedLabels',
                ),
                'relations' => array(
                ),
                'rules' => array(
                    array('name',             'required'),
                    array('name',             'type',   'type' => 'string'),
                    array('name',             'length', 'min'  => 3, 'max' => 64),
                    array('order',            'required'),
                    array('order',            'type',    'type' => 'integer'),
                    array('order',            'numerical', 'min' => 0),
                    array('serializedLabels', 'type', 'type' => 'string'),
                ),
                'defaultSortAttribute' => 'order',
            );
            return $metadata;
        }

        /**
         * $param string $language
         * @return translated name string if available.
         */
        public function resolveTranslatedNameByLanguage($language)
        {
            assert('is_string($language)');
            if ($this->serializedLabels !== null)
            {
                $unserializedLabels = unserialize($this->serializedLabels);
                if (isset($unserializedLabels[$language]))
                {
                    return $unserializedLabels[$language];
                }
            }
            return Zurmo::t('ContactsModule', $this->name, array(), null, $language);
        }
    }
?>
