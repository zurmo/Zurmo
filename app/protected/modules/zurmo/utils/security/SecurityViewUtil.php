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
     * Helper class to dynamically generate
     * view metadata based on data array.
     * Used by Policies, Rights, and Permissions Views
     */
    abstract class SecurityViewUtil
    {
        /**
         * resolves the view metadata based on dynamic information from the
         * data array and the metadata passed in which.  The metadata passed in
         * should not have any global panel information as this part of the array is being
         * generated here.
         * @return array - final view metadata.
         */
        public static function resolveMetadataFromData($data, $metadata)
        {
            assert('!isset($metadata["global"]["panels"])');
            $formMetadata    = static::makeMetadataFromData($data);
            return array_merge_recursive($metadata, $formMetadata);
        }

        /**
         * Makes view metadata based on data array
         * @return array - view metadata
         */
        protected static function makeMetadataFromData($data)
        {
            $elements        = array();
            $calledClassName = get_called_class();
            foreach ($data as $moduleClassName => $moduleItems)
            {
                foreach ($moduleItems as $item => $itemInformation)
                {
                    $element = $calledClassName::getElementInformation(
                        $moduleClassName,
                        $item,
                        $itemInformation);
                    if ($element != null)
                    {
                        $elements[] = $element;
                    }
                }
            }
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                            ),
                        ),
                    ),
                )
            );
            foreach ($elements as $element)
            {
                $metadata['global']['panels'][0]['rows'][] = $calledClassName::getRowByElement($element);
            }
            return $metadata;
        }

        protected static function sortCategoryItems($categoryItems)
        {
            return ArrayUtil::subValueSort($categoryItems, 'titleLabel', 'asort');
        }

        protected static function getRowByElement($element, $rowTitle = null)
        {
            assert('is_array($element)');
            assert('$rowTitle == null | is_string($rowTitle)');
            $row = array(
                'cells' => array(
                    array(
                        'elements' => array(
                            $element,
                        ),
                    ),
                ),
            );
            if ($rowTitle != null)
            {
                $row['title'] = $rowTitle;
            }
            return $row;
        }

        /**
         * This is not abstract because PHP will not allow an
         * abstract protected static function.  Override this function
         * as necessary
         */
        protected static function getElementInformation($moduleClassName, $item, $itemInformation)
        {
        }
    }
?>