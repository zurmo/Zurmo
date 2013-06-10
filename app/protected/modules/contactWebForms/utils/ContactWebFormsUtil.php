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

    /**
     * Helper class with functions
     * to assist in working with ContactWebForms module
     * information
     */
    class ContactWebFormsUtil
    {
        /**
         * Get list of all index and derived attributes
         * @return array of attributes
         */
        public static function getAllAttributes()
        {
            $contact     = new Contact();
            $adapter     = new ContactWebFormModelAttributesAdapter($contact);
            $attributes  = $adapter->getAttributes();
            $attributes  = ArrayUtil::subValueSort($attributes, 'attributeLabel', 'asort');
            return $attributes;
        }

        public static function getAllPlacedAttributes($attributes, $contactWebFormAttributes = null)
        {
            $items = array();
            if ($contactWebFormAttributes !== null)
            {
                $allPlacedAttributes = array();
                foreach ($contactWebFormAttributes as $contactWebFormAttribute)
                {
                    $allPlacedAttributes[$contactWebFormAttribute] = $attributes[$contactWebFormAttribute];
                }
                $attributes = $allPlacedAttributes;
            }
            foreach ($attributes as $attributeName => $attributeData)
            {
                if (!$attributeData['isReadOnly'])
                {
                    if ($attributeData['isRequired'])
                    {
                        $items[$attributeName] = array('{content}'            => $attributeData['attributeLabel'],
                                                       '{checkedAndReadOnly}' => '');
                    }
                    elseif ($contactWebFormAttributes !== null)
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

        public static function getAllNonPlacedAttributes($attributes, $contactWebFormAttributes = null)
        {
            $items = array();
            foreach ($attributes as $attributeName => $attributeData)
            {
                //TODO: Figure out, how to hide attributes like googleWebTrackingId
                if (!$attributeData['isReadOnly'] && $attributeName != 'googleWebTrackingId')
                {
                    if (!$attributeData['isRequired'])
                    {
                        if ($contactWebFormAttributes !== null)
                        {
                            if (!in_array($attributeName, $contactWebFormAttributes))
                            {
                                $items[$attributeName] = $attributeData['attributeLabel'];
                            }
                        }
                        else
                        {
                            $items[$attributeName] = $attributeData['attributeLabel'];
                        }
                    }
                }
            }
            return $items;
        }

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