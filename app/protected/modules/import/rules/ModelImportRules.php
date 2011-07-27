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

    /**
     * Class to help the import module understand
     * how to parse and handle the import file it is importing based on what module(s) it is being imported into.
     */
    abstract class ModelImportRules
    {
        protected function getAttributeNames()
        {

        }

        protected function getDerivedAttributeTypes()
        {
            return array();
        }

        protected function getNonImportableAttributeNames()
        {
            return array();
        }

        protected function getNonImportableAttributeTypes()
        {
            return array();
        }

        public function getMappableAttributeNamesAndDerivedTypes()
        {
            $mappableAttributeNamesAndDerivedTypes = array();

            //what hapens if 2 attribute names are the same because cross module? like to address or something.
            /**
             * Account_name
             * Account_address_street1
             * Account_address_state
             * Account_officePhone
             */
            $modelAttributesAdapter = new ModelAttributesAdapter(new Contact());
echo "<pre>";
print_r($modelAttributesAdapter->getAttributes());
echo "</pre>";

            exit;
            return $mappableAttributeNamesAndDerivedTypes;
        }
    }
?>