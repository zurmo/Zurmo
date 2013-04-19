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
     * UpdateZurmoCommand update Zurmo version.
     */
    class ManageMetadataCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc manageMetadata <username> <action>

    DESCRIPTION
      This command manage metadata.

    PARAMETERS
     * username: username to log in as and run the import processes. Typically 'super'.
                  This user must be a super administrator.
     * action: define upgrade phase(possible options: "saveAllMetadata" or "getAllMetadata")
EOD;
        }

        /**
         * Execute the action.
         * @param array command line parameters specific for this command
         */
        public function run($args)
        {
            set_time_limit(600);
            if (!isset($args[0]))
            {
                $this->usageError('A username must be specified.');
            }
            try
            {
                Yii::app()->user->userModel = User::getByUsername($args[0]);
            }
            catch (NotFoundException $e)
            {
                $this->usageError('The specified username does not exist.');
            }
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (!$group->users->contains(Yii::app()->user->userModel))
            {
                $this->usageError('The specified user is not a super administrator.');
            }

            if (!isset($args[1]))
            {
                $this->usageError('You must specify an action.');
            }
            else
            {
                $action = $args[1];
            }

            try
            {
                if ($action == 'saveAllMetadata')
                {
                    $this->saveAllMetadata();
                }
                elseif ($action == 'getAllMetadata')
                {
                    $this->getAllMetadata();
                }
                else
                {
                    $this->usageError('Invalid step/action. Valid values are "saveAllMetadata" and "getAllMetadata".');
                }
            }
            catch (Exception $e)
            {
               echo Zurmo::t('Commands', 'An error occur during metadata manage: {message}',
                             array('{message}' => $e->getMessage()));
            }
        }

        protected function saveAllMetadata()
        {
            // Save all module metadata
            foreach (Module::getModuleObjects() as $module)
            {
                $metadata = $module->getDefaultMetadata();
                $module->setMetadata($metadata);
            }

            // Save all model metadata
            $allModels   = array();
            foreach (Module::getModuleObjects() as $module)
            {
                $moduleAndDependenciesRootModelNames = $module->getModelClassNames();
                $allModels = array_merge($allModels, array_diff($moduleAndDependenciesRootModelNames, $allModels));
            }
            foreach ($allModels as $className)
            {
                $classToEvaluate     = new ReflectionClass($className);
                if (is_subclass_of($className, 'RedBeanModel') &&
                    !$classToEvaluate->isAbstract() &&
                    $className::canSaveMetadata())
                {
                    $metadata = $className::getDefaultMetadata();
                    $className::setMetadata($metadata);
                }
            }

            // Save all View metadata
            $configurableMetadataViews = array();
            foreach (Module::getModuleObjects() as $module)
            {
                $viewClassNames  = $module::getViewClassNames();
                if (count($viewClassNames))
                {
                    foreach ($viewClassNames as $className)
                    {
                        $classToEvaluate     = new ReflectionClass($className);
                        if (is_subclass_of($className, 'MetadataView') &&
                            !$classToEvaluate->isAbstract() &&
                            $className::getDesignerRulesType() != null)
                        {
                            $configurableMetadataViews[] = $className;
                            $metadata = $className::getDefaultMetadata();
                            $className::setMetadata($metadata);
                        }
                    }
                }
            }
        }

        protected function getAllMetadata()
        {
            throw new NotImplementedException();
        }
    }
?>