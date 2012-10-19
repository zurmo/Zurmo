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

    /**
     * Base class for defining rules for search form attributes. Some search form attributes require dynamically
     * determining operatorTypes and values based on either the value specified in the user interface in the search form
     * or some other means. An example is the @see OwnedItemsOnlySearchFormAttributeMappingRules which will dynamically
     * use the current user's userModel id to define the value to filter on.
     */
    abstract class SearchFormAttributeMappingRules
    {
        /**
         * Override if any massaging is needed on the value.
         * @param mixed $value
         */
        public static function resolveValueDataIntoUsableValue($value)
        {
            return $value;
        }

        /**
         * Implement if the attributeAndRelations array needs to be dynamically resolved to a new structure at run-time.
         * This could be required if the structure changes depending on the value for example.
         * @param string $attributeName
         * @param array $attributeAndRelations
         * @param mixed $value
         */
        public static function resolveAttributesAndRelations($attributeName, & $attributeAndRelations, $value)
        {
            throw new NotImplementedException();
        }

        /**
         * Override if any rules need to be ignored for attributes using this rule.
         * @param mixed $value
         */
        public static function getIgnoredSavableMetadataRules()
        {
            return array();
        }
    }
?>