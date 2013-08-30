<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class with functions
     * to assist in working with ContactWebForms module
     * information
     */
    class ContactWebFormsUtil
    {
        /**
         * Get list of all index and derived attributes
         * @return array of all attributes
         */
        public static function getAllAttributes()
        {
            $contact     = new Contact();
            $adapter     = new ContactWebFormModelAttributesAdapter($contact);
            $attributes  = $adapter->getAttributes();
            $attributes  = ArrayUtil::subValueSort($attributes, 'attributeLabel', 'asort');
            return $attributes;
        }

        /**
         * @param $attributes
         * @param array $contactWebFormAttributes
         * @return array of attributes placed on web form, default to required fields
         */
        public static function getAllPlacedAttributes($attributes, $contactWebFormAttributes = array())
        {
            $items = array();
            foreach ($attributes as $attributeName => $attributeData)
            {
                if (!$attributeData['isReadOnly'])
                {
                    if ($attributeData['isRequired'])
                    {
                        $items[$attributeName] = array('{content}'            => $attributeData['attributeLabel'],
                                                       '{checkedAndReadOnly}' => '');
                    }
                    elseif (in_array($attributeName, $contactWebFormAttributes))
                    {
                        $checkedAndReadOnly    = '<a class="remove-dynamic-row-link" id="ContactWebForm_serializedData_' .
                                                  $attributeName . '" data-value="' . $attributeName . '" href="#">—</a>';
                        $items[$attributeName] = array('{content}'            => $attributeData['attributeLabel'],
                                                       '{checkedAndReadOnly}' => $checkedAndReadOnly);
                    }
                }
            }
            return $items;
        }

        /**
         * @param $attributes
         * @param array $contactWebFormAttributes
         * @return array of attributes not placed on web form, but can be placed
         */
        public static function getAllNonPlacedAttributes($attributes, $contactWebFormAttributes = array())
        {
            $items = array();
            foreach ($attributes as $attributeName => $attributeData)
            {
                //TODO: Figure out, how to hide attributes like googleWebTrackingId
                if (!$attributeData['isReadOnly'] && $attributeName != 'googleWebTrackingId')
                {
                    if (!$attributeData['isRequired'])
                    {
                        if (!in_array($attributeName, $contactWebFormAttributes))
                        {
                            $items[$attributeName] = $attributeData['attributeLabel'];
                        }
                    }
                }
            }
            return $items;
        }

        /**
         * @param integer $id
         * @return string
         */
        public static function getEmbedScript($id)
        {
            $embedScript = '<div id="zurmoExternalWebForm">' .
                           '<script type="text/javascript" ' .
                           'src="' . Yii::app()->createAbsoluteUrl('contacts/external/sourceFiles/', array('id' => $id)) . '">' .
                           '</script></div>';
            return $embedScript;
        }
    }
?>