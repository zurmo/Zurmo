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
     * Helper class to interrogate relationships between customFieldData models and other models
     * in the application.
     */
    class CustomFieldDataModelUtil
    {
        /**
         * Use this function to ascertain if a particular customFieldData model is used by more than one attribute
         * in the application.  An example is a pick list that is shared by two separate attributes in two different
         * models.
         * @return array of model plural label / attribute label pairings.
         *
         * Important Limitations to understand!!
         * Only supports up to one attribute per model that uses the same customFieldData.
         * Only searches primary models of the modules in the system.
         */
        public static function getModelPluralNameAndAttributeLabelsByName($name)
        {
            $data = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                try
                {
                    $modelClassName = $module->getPrimaryModelName();
                    $metadata = $modelClassName::getMetadata();

                    if (isset($metadata[$modelClassName]['customFields']) &&
                        in_array($name, $metadata[$modelClassName]['customFields']))
                    {
                        $model = new $modelClassName();
                        $attributeName = array_search($name, $metadata[$modelClassName]['customFields']);
                        $data[$module::getModuleLabelByTypeAndLanguage('Plural')] =
                            $model->getAttributeLabel($attributeName);
                    }
                }
                catch (NotSupportedException $e)
                {
                }
            }
            return $data;
        }

        /**
         * Given a model class name and an attribute name, get the CustomFieldData object associated with this
         * attribute.  Requires the attribute to be a customField type attribute otherwise it will throw an error.
         * @param string $modelClassName
         * @param string $attributeName
         */
        public static function getDataByModelClassNameAndAttributeName($modelClassName, $attributeName)
        {
            $metadata = $modelClassName::getMetadata();
            foreach ($metadata as $unused => $classMetadata)
            {
                if (isset($classMetadata['customFields']))
                {
                    foreach ($classMetadata['customFields'] as $customFieldName => $customFieldDataName)
                    {
                        if ($attributeName == $customFieldName)
                        {
                            return CustomFieldData::getByName($customFieldDataName);
                        }
                    }
                }
            }
            throw new NotSupportedException();
        }
    }
?>