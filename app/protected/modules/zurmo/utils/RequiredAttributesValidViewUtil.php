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
     * Helper class for working with views and making sure a view has the required attributes placed before allowing
     * a user to work with a view in the user interface.  When a custom attribute is created, certain views, based
     * on designer rules, are marked as missing required attributes.  Once these required attributes are placed, the
     * view is marked as containing the required attributes.  The default controller actions such as edit and create
     * resolve this information and display an error page if a view is missing required attributes.
     */
    class RequiredAttributesValidViewUtil
    {
        public static function setAsMissingRequiredAttributes($moduleClassName, $viewClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($viewClassName)');
            $key = $viewClassName . '_layoutMissingRequiredAttributes';
            ZurmoConfigurationUtil::setByModuleName($moduleClassName, $key, true);
        }

        public static function setAsContainingRequiredAttributes($moduleClassName, $viewClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($viewClassName)');
            $key = $viewClassName . '_layoutMissingRequiredAttributes';
            ZurmoConfigurationUtil::setByModuleName($moduleClassName, $key, false);

        }

        public static function isViewMissingRequiredAttributes($moduleClassName, $viewClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($viewClassName)');
            $key   = $viewClassName . '_layoutMissingRequiredAttributes';
            $value = ZurmoConfigurationUtil::getByModuleName($moduleClassName, $key);
            if($value)
            {
                return true;
            }
            return false;
        }

        public static function resolveValidView($moduleClassName, $viewClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($viewClassName)');
            if(!static::isViewMissingRequiredAttributes($moduleClassName, $viewClassName))
            {
                return;
            }
            $designerRules          = DesignerRulesFactory::createDesignerRulesByView($viewClassName);
            $viewDisplayName        = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
            $viewDisplayName       .= ' ' .  $designerRules->resolveDisplayNameByView($viewClassName);
            return                    Yii::t('Default', 'There are required fields missing from the following' .
                                                        ' layout: {view}.  Please contact your administrator.',
                                                        array('{view}' => $viewDisplayName));
        }

        public static function resolveToSetAsMissingRequiredAttributesByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                try
                {
                    if($module::getPrimaryModelName() == $modelClassName)
                    {
                        $viewClassNames          = $module::getViewClassNames();
                        foreach ($viewClassNames as $viewClassName)
                        {
                            $classToEvaluate     = new ReflectionClass($viewClassName);
                            if (is_subclass_of($viewClassName, 'MetadataView') && !$classToEvaluate->isAbstract() &&
                                $viewClassName::getDesignerRulesType() != null)
                            {

                                $designerRules = DesignerRulesFactory::createDesignerRulesByView($viewClassName);
                                if($designerRules->allowEditInLayoutTool() &&
                                   $designerRules->requireAllRequiredFieldsInLayout())
                                {
                                    self::setAsMissingRequiredAttributes(get_class($module), $viewClassName);
                                }
                            }
                        }
                    }
                }
                catch (NotSupportedException $e)
                {
                }
            }
        }
    }
?>