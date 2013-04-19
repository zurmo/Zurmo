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
     * The import process includes a view for mapping import columns to attributes or derived attribute types
     * in a model. When a column is selected to be mapped against an attribute or derived attribute type, there is the
     * potentially for mapping rules to accompany that mapping. An example of a rule would be a default value rule
     * or in the case of something like owner, is the value the owner's username or actuay user id.
     */
    abstract class MappingRuleForm extends ConfigurableMetadataModel
    {
        /**
         * @return string - If the class name is DefaultValueModelAttributeMappingRuleForm,
         * then 'DefaultValueModelAttribute' will be returned.
         */
        public static function getType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('MappingRuleForm'));
            return $type;
        }

        /**
         * A mapping rule form has one attribute. This returns the name of that attribute.
         */
        public static function getAttributeName()
        {
        }

        public function rules()
        {
            return array();
        }

        public function attributeLabels()
        {
            return array();
        }
    }
?>