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
     * Helper functionality for aggregating the messages
     * from the framework and all installed modules.
     */
    class MessageUtil
    {
        /**
         * Returns the messages made by aggregating in order
         * the messages found int the framework, then in each
         * of the dependent modules up to then including
         * the module for the currently controller.
         */
        public static function getMessages($language, $moduleName = null, $category = null)
        {
            assert('is_string($language)');
            assert('strlen($language) == 2 || strlen($language) == 5');
            if ($moduleName === null)
            {
                $directories = self::getMessageDirectoriesForAllModules();
            }
            elseif ($moduleName != 'framework')
            {
                $directories = self::getMessageDirectoriesForNamedModuleAndDependencies($moduleName);
            }
            else
            {
                $directories = array();
            }
            array_unshift($directories, 'extensions/zurmoinc/framework');
            $messages = array();
            foreach ($directories as $directory)
            {
                $messagesDirectoryName = COMMON_ROOT . "/protected/$directory/messages/$language";
                if (is_dir($messagesDirectoryName))
                {
                    $fileNames = scandir($messagesDirectoryName);
                    foreach ($fileNames as $fileName)
                    {
                        if (pathinfo($fileName, PATHINFO_EXTENSION) == 'php')
                        {
                            if ($category === null || pathinfo($fileName, PATHINFO_FILENAME) == $category)
                            {
                                $fileName = "$messagesDirectoryName/$fileName";
                                if (file_exists($fileName))
                                {
                                    $messages = array_merge($messages, require($fileName));
                                }
                            }
                        }
                    }
                }
            }
            return $messages;
        }

        private static function getMessageDirectoriesForCurrentModuleAndDependencies()
        {
            if (Yii::app()->controller instanceof CController)
            {
                $currentModule = Yii::app()->controller->getModule();
                return self::getMessageDirectoriesForModuleAndDependencies($currentModule);
            }
            return array();
        }

        private static function getMessageDirectoriesForNamedModuleAndDependencies($moduleName)
        {
            $module = Yii::app()->findModule($moduleName);
            assert('$module instanceof Module');
            return self::getMessageDirectoriesForModuleAndDependencies($module);
        }

        private static function getMessageDirectoriesForModuleAndDependencies(Module $module)
        {
            $dependencies = Module::getDependenciesForModule($module);
            $directories = array();
            foreach ($dependencies as $dependency)
            {
                $directories[] = "modules/$dependency";
            }
            return $directories;
        }

        /**
         * Ignores nested modules. Assumes nested modules have their messages in their parent module.
         * @return all available module message directories
         */
        private static function getMessageDirectoriesForAllModules()
        {
             $modules = Module::getModuleObjects();
             foreach ($modules as $module)
             {
                if (get_class($module->getParentModule()) == 'MessageUtil')
                {
                    $directories[] = 'modules/' . $module->getDirectoryName();
                }
             }
             return $directories;
        }
    }

    function getLanguagesMessages($basePath, $moduleName, $language)
    {
        $messagesDirectoryName = "$basePath/protected/modules/$moduleName/messages/$language";
        $messageFileName = "$messagesDirectoryName/Default.php";
        if (file_exists($messageFileName))
        {
            return require($messageFileName);
        }
        else
        {
            return array();
        }
    }

    function checkLanguagesMessageFilesContainAllTheSameValidMessages($messagesDirectoryName, $moduleName)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $problems = array();
        $languages = getLanguages($messagesDirectoryName);
        if (count($languages) > 0)
        {
            $languagesToMessageFiles = array();
            foreach ($languages as $language)
            {
                $languagesToMessageFiles[$language] = array();
                $languageMessagesDirectoryName = "$messagesDirectoryName/$language";
                $languagesToMessageFiles[$language]['messageDirectory'] = $languageMessagesDirectoryName;
                $entries = scandir($languageMessagesDirectoryName);
                foreach ($entries as $entry)
                {
                    $fullEntryName = "$languageMessagesDirectoryName/$entry";
                    if (is_file($fullEntryName) &&
                        pathinfo($entry, PATHINFO_EXTENSION) == 'php')
                    {
                        $languagesToMessageFiles[$language][] = $entry;
                    }
                }
            }
            for ($i = 0; $i < count($languages); $i++)
            {
                $problems = array_merge($problems,
                                        checkLanguagesMessageFilesFormat($languagesToMessageFiles[$languages[$i]],
                                                                         $messagesDirectoryName));
            }
            // Compares the first language to the others.
            for ($i = 1; $i < count($languages); $i++)
            {
                $problems = array_merge($problems,
                                        diffLanguagesMessageFiles($languagesToMessageFiles[$languages[0]],
                                                                  $languagesToMessageFiles[$languages[$i]],
                                                                  $messagesDirectoryName,
                                                                  $moduleName));
            }
        }
        else
        {
            $problems[] = 'No languages were found in the following directory:' . $messagesDirectoryName . "\n";
        }
        return $problems;
    }

    function checkFirstLanguagesMessageFilesMessagesReallyExistInSource($messagesDirectoryName, $moduleName)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $problems = array();
        $fileNamesToCategoriesToMessages = findFileNameToCategoryToMessage($messagesDirectoryName . '/..');
        $categoriesToMessagesToFileNames = convertFileNameToCategoryToMessageToCategoryToMessageToFileName($fileNamesToCategoriesToMessages);
        unset($categoriesToMessagesToFileNames['yii']);
        $languages = getLanguages($messagesDirectoryName);
        if (count($languages) > 0)
        {
            $firstLanguage = $languages[0];
            foreach ($categoriesToMessagesToFileNames as $category => $messagesToFileNames)
            {
                $messageFileName = "$messagesDirectoryName/$firstLanguage/$category.php";
                if (is_file($messageFileName))
                {
                    $messagesInMessageFile = require($messageFileName);
                    if (!is_array($messagesInMessageFile))
                    {
                        $problems[] = "$shortFileName is not a valid message file.\n";
                        continue;
                    }
                    $messagesInMessageFile = array_keys($messagesInMessageFile);
                }
                else
                {
                    continue;
                }
                $messagesInSourceFiles = array_keys($messagesToFileNames);
                $messagesNotInSourceFiles = array_diff($messagesInMessageFile, $messagesInSourceFiles);
                foreach ($messagesNotInSourceFiles as $message)
                {
                    if (strpos($message, 'ModulePluralLabel') === false &&
                       strpos($message, 'ModuleSingularLabel') === false &&
                       strpos($message, 'ModulePluralLowerCaseLabel') === false &&
                       strpos($message, 'ModuleSingularLowerCaseLabel') === false )
                    {
                        //There is no current way to resolve this type of issue. It is possible a label has been pushed
                        //back into framework for example, even though there is no mention of the label in framework.  This
                        //is so it is not duplicated in various modules. This is turned off for now from the message checker.
                        //$problems[] = "(This might be ok) '$message' in $firstLanguage/$category.php not in any source file in $moduleName.";
                    }
                }
            }
        }
        else
        {
            $problems[] = 'No languages were found in the following directory:' . $messagesDirectoryName . "\n";
        }
        return $problems;
    }

    function checkFirstLanguagesMessageFilesContainAllExistingMessages($messagesDirectoryName, $moduleName, $basePath)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $problems = array();
        $fileNamesToCategoriesToMessages = findFileNameToCategoryToMessage($messagesDirectoryName . '/..');
        $categoriesToMessagesToFileNames = convertFileNameToCategoryToMessageToCategoryToMessageToFileName($fileNamesToCategoriesToMessages);
        unset($categoriesToMessagesToFileNames['yii']);
        $languages = getLanguages($messagesDirectoryName);
        if (count($languages) > 0)
        {
            $firstLanguage = $languages[0];
            foreach ($categoriesToMessagesToFileNames as $category => $messagesToFileNames)
            {
                $existingMessages   = array_keys(MessageUtil::getMessages($firstLanguage, $moduleName, $category));
                $duplicateCount = count($existingMessages) != count(array_unique($existingMessages));
                if ($duplicateCount != 0)
                {
                    $problems[] = "$moduleName and its dependencies contain $duplicateCount duplicate entries.";
                }
                $yiiMessageFileName = "$messagesDirectoryName/$firstLanguage/$category.php";
                $yiiMessages        = require("$basePath/../../yii/framework/messages/$firstLanguage/yii.php");
                foreach ($messagesToFileNames as $message => $fileNames)
                {
                    $fileNames = join(', ', $fileNames);
                    if (!in_array($message, $existingMessages) &&
                        !in_array($message, $yiiMessages))
                    {
                        $problems[] = "'$message' in $fileNames not in $firstLanguage/$category.php in $moduleName or its dependencies.";
                    }
                    elseif (in_array($message, $existingMessages) &&
                        in_array($message, $yiiMessages))
                    {
                        $problems[] = "'$message' in $fileNames in $firstLanguage/$category.php in $moduleName or its dependencies is a duplicate of definition already in Yii.";
                    }
                }
            }
        }
        else
        {
            $problems[] = 'No languages were found in the following directory:' . $messagesDirectoryName . "\n";
        }
        return $problems;
    }

    function checkForYiiTCallsThatAreNotHowThisScriptExpectsThemToBe($messagesDirectoryName, $moduleName)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $problems = array();
        $fileNamesToUnexpectedlyFormattedYiiTs = findFileNameToUnexpectedlyFormattedYiiT($messagesDirectoryName . '/..');
        foreach ($fileNamesToUnexpectedlyFormattedYiiTs as $fileName => $unexpectedlyFormattedYiiTs)
        {
            foreach ($unexpectedlyFormattedYiiTs as $code)
            {
                $problems[] = "$code in $fileName in $moduleName.";
            }
        }
        return $problems;
    }

    function findFileNameToCategoryToMessage($path)
    {
        assert('is_string($path)');
        assert('is_dir   ($path)');
        $fileNamesToCategoriesToMessages = array();
        $f = opendir($path);
        assert('$f !== false');
        while ($entry = readdir($f))
        {
            if (!in_array($entry, array('.', '..', 'messages')))
            {
                $fullEntryName = "$path/$entry";
                if (is_dir($fullEntryName))
                {
                    $fileNamesToCategoriesToMessages = array_merge($fileNamesToCategoriesToMessages,
                                                                   findFileNameToCategoryToMessage($fullEntryName));
                }
                elseif (is_file($fullEntryName) &&
                    pathinfo($entry, PATHINFO_EXTENSION) == 'php')
                {
                    //Avoid any models in the framework/models folder and test models
                    if ( strpos($path, '/framework') === false &&
                         strpos($path, '/tests') === false &&
                         strpos($path, '/models') !== false &&
                         strpos($fullEntryName, '.php') !== false)
                    {
                        $modelClassName = basename(substr($fullEntryName, 0, -4));
                        $modelReflectionClass = new ReflectionClass($modelClassName);
                        if ($modelReflectionClass->isSubclassOf('RedBeanModel') &&
                            !$modelReflectionClass->isAbstract())
                        {
                           $model              = new $modelClassName(false);
                           $modelAttributes    = $model->attributeNames();
                           $untranslatedLabels = $model->getUntranslatedAttributeLabels();
                           foreach ($modelAttributes as $attributeName)
                           {
                                $attributeLabel = $model->getAttributeLabel($attributeName);
                                if (isset($untranslatedLabels[$attributeName]))
                                {
                                    $translatedLabel = Yii::t('Default', $untranslatedLabels[$attributeName],
                                                                         LabelUtil::getTranslationParamsForAllModules());
                                    if ($untranslatedLabels[$attributeName] == $attributeLabel ||
                                       $translatedLabel != $attributeLabel)
                                    {
                                        $fileNamesToCategoriesToMessages[$entry]['Default'][] = $attributeLabel;
                                    }
                                    else
                                    {
                                        $fileNamesToCategoriesToMessages[$entry]['Default'][] =
                                        $untranslatedLabels[$attributeName];
                                    }
                                }
                                else
                                {
                                    $fileNamesToCategoriesToMessages[$entry]['Default'][] = $attributeLabel;
                                }
                               //Find attributes that are a CustomField relation. This means there is drop down values
                               //that will need to be translated.
                               if ($model->isRelation($attributeName) &&
                                   ($model->getRelationModelClassName($attributeName) == 'OwnedCustomField' ||
                                   $model->getRelationModelClassName($attributeName) == 'CustomField' ||
                                   $model->getRelationModelClassName($attributeName) == 'MultipleValuesCustomField' ||
                                   $model->getRelationModelClassName($attributeName) == 'OwnedMultipleValuesCustomField'))
                                {
                                    $customFieldData = CustomFieldDataModelUtil::
                                                       getDataByModelClassNameAndAttributeName($modelClassName, $attributeName);
                                    $customFieldDataNames = unserialize($customFieldData->serializedData);
                                    foreach ($customFieldDataNames as $dataName)
                                    {
                                        $fileNamesToCategoriesToMessages[$entry]['Default'][] = $dataName;
                                    }
                                }
                           }
                        }
                    }
                    //Check for any menu labels, rights, policies, or audit event names in the modules.
                    if ( strpos($fullEntryName, 'Module.php') !== false)
                    {
                        $moduleClassName = basename(substr($fullEntryName, 0, -4));
                        $moduleReflectionClass = new ReflectionClass($moduleClassName);
                        if ($moduleReflectionClass->isSubclassOf('SecurableModule') &&
                            !$moduleReflectionClass->isAbstract())
                        {
                            $labelsData = getSecurableModuleRightsPoliciesAndAuditEventLabels($moduleClassName);
                            if (!empty($labelsData))
                            {
                                if (isset($fileNamesToCategoriesToMessages[$entry]['Default']))
                                {
                                    $fileNamesToCategoriesToMessages[$entry]['Default'] =
                                    array_merge($fileNamesToCategoriesToMessages[$entry]['Default'], $labelsData);
                                }
                                else
                                {
                                    $fileNamesToCategoriesToMessages[$entry]['Default'] = $labelsData;
                                }
                            }
                        }
                        //attempt to detect any 'state' adapters and look for state labels.
                        $stateAdapterClassName = $moduleClassName::getStateMetadataAdapterClassName();
                        if ($stateAdapterClassName != null && $stateAdapterClassName)
                        {
                            $stateModelClassName = $stateAdapterClassName::getStateModelClassName();
                            $states              = $stateModelClassName::getAll();
                            foreach ($states as $state)
                            {
                                $fileNamesToCategoriesToMessages[$entry]['Default'][] = $state->name;
                            }
                        }
                        //check for menu labels
                        if (!$moduleReflectionClass->isAbstract())
                        {
                            if (isset($fileNamesToCategoriesToMessages[$entry]['Default']))
                            {
                                $fileNamesToCategoriesToMessages[$entry]['Default'] =
                                array_merge($fileNamesToCategoriesToMessages[$entry]['Default'],
                                            getModuleMenuLabelNamesByModuleName($moduleClassName));
                            }
                            else
                            {
                                $fileNamesToCategoriesToMessages[$entry]['Default'] =
                                    getModuleMenuLabelNamesByModuleName($moduleClassName);
                            }
                        }
                    }
                    //Check for any panel titles that need translation
                    if ( strpos($fullEntryName, 'View.php') !== false)
                    {
                        $viewClassName = basename(substr($fullEntryName, 0, -4));
                        $moduleReflectionClass = new ReflectionClass($viewClassName);
                        if ($moduleReflectionClass->isSubclassOf('MetadataView') &&
                            !$moduleReflectionClass->isAbstract())
                        {
                            $metadata = $viewClassName::getDefaultMetadata();
                            if (isset($metadata['global']) && isset($metadata['global']['panels']))
                            {
                                foreach ($metadata['global']['panels'] as $panel)
                                {
                                    if (isset($panel['title']))
                                    {
                                        $fileNamesToCategoriesToMessages[$entry]['Default'][] = $panel['title'];
                                    }
                                }
                            }
                        }
                    }
                    //Avoid picking up any models or anything in the test folders
                    if ( strpos($path, '/tests') === false)
                    {
                        $content = file_get_contents($fullEntryName);
                        $content = str_replace('\\\'', '\'', $content);
                        if (preg_match_all(GOOD_YII_T, $content, $matches))
                        {
                            foreach ($matches[1] as $index => $category)
                            {
                                if (!isset($fileNamesToCategoriesToMessages[$entry][$category]))
                                {
                                    $fileNamesToCategoriesToMessages[$entry][$category] = array();
                                }
                                //Remove extra lines caused by ' . ' which is used for line breaks in php. Minimum 3 spaces
                                //will avoid catching 2 spaces between words which can be legitimate.
                                $massagedString = preg_replace('/[\p{Z}\s]{3,}/u', ' ', $matches[2][$index]); // Not Coding Standard
                                $massagedString = str_replace("' . '", '', $massagedString);
                                $fileNamesToCategoriesToMessages[$entry][$category][] = $massagedString;
                                if ($matches[2][$index] != $massagedString && strpos($matches[2][$index], "' .") === false)
                                {
                                    echo 'The following message should be using proper line breaks: ' . $matches[2][$index] . "\n";
                                }
                            }
                        }
                    }
                }
            }
        }
        return $fileNamesToCategoriesToMessages;
    }

    function getSecurableModuleRightsPoliciesAndAuditEventLabels($moduleClassName)
    {
        assert('is_string($moduleClassName)');
        $rightsNames     = $moduleClassName::getUntranslatedRightsLabels();
        $policiesNames   = $moduleClassName::getUntranslatedPolicyLabels();
        $auditEventNames = $moduleClassName::getAuditEventNames();
        $labelsData      = array_merge($rightsNames, $policiesNames);
        return             array_merge($labelsData, $auditEventNames);
    }

    function getModuleMenuLabelNamesByModuleName($moduleClassName)
    {
        $labels   = array();
        $metadata = $moduleClassName::getMetadata();
        if (isset($metadata['global']['tabMenuItems']))
        {
            foreach ($metadata['global']['tabMenuItems'] as $menuItem)
            {
                if (isset($menuItem['items']))
                {
                    foreach ($menuItem['items'] as $subMenuItem)
                    {
                        if (!in_array($subMenuItem['label'], $labels))
                        {
                            $labels[] = $subMenuItem['label'];
                        }
                    }
                }
                if (!in_array($menuItem['label'], $labels))
                {
                    $labels[] = $menuItem['label'];
                }
            }
        }
        if (isset($metadata['global']['shortcutsMenuItems']))
        {
            foreach ($metadata['global']['shortcutsMenuItems'] as $menuItem)
            {
                if (isset($menuItem['items']))
                {
                    foreach ($menuItem['items'] as $subMenuItem)
                    {
                        if (!in_array($subMenuItem['label'], $labels))
                        {
                            $labels[] = $subMenuItem['label'];
                        }
                    }
                }
                if (!in_array($menuItem['label'], $labels))
                {
                    $labels[] = $menuItem['label'];
                }
            }
        }
        if (isset($metadata['global']['headerMenuItems']))
        {
            foreach ($metadata['global']['headerMenuItems'] as $menuItem)
            {
                if (!in_array($menuItem['label'], $labels))
                {
                    $labels[] = $menuItem['label'];
                }
            }
        }
        if (isset($metadata['global']['configureMenuItems']))
        {
            foreach ($metadata['global']['configureMenuItems'] as $menuItem)
            {
                if (!in_array($menuItem['titleLabel'], $labels))
                {
                    $labels[] = $menuItem['titleLabel'];
                }
                if (!in_array($menuItem['descriptionLabel'], $labels))
                {
                    $labels[] = $menuItem['descriptionLabel'];
                }
            }
        }
        return $labels;
    }

    function findFileNameToUnexpectedlyFormattedYiiT($path)
    {
        assert('is_string($path)');
        assert('is_dir   ($path)');
        $fileNamesToUnexpectedlyFormattedYiiTs = array();
        $f = opendir($path);
        assert('$f !== false');
        while ($entry = readdir($f))
        {
            if (!in_array($entry, array('.', '..', 'messages')))
            {
                $fullEntryName = "$path/$entry";
                if (is_dir($fullEntryName))
                {
                    $fileNamesToUnexpectedlyFormattedYiiTs = array_merge($fileNamesToUnexpectedlyFormattedYiiTs,
                                                                         findFileNameToUnexpectedlyFormattedYiiT($fullEntryName));
                }
                elseif (is_file($fullEntryName) &&
                    pathinfo($entry, PATHINFO_EXTENSION) == 'php')
                {
                    $content = file_get_contents($fullEntryName);
                    if (preg_match_all(ALL_YII_TS, $content, $matches))
                    {
                        foreach ($matches[0] as $code)
                        {
                            $code = str_replace('\\\'', '\'', $code);
                            if (!preg_match(GOOD_YII_T, $code))
                            {
                                if (!preg_match(GOOD_YII_T_WITH_LINEBREAK, $code))
                                {
                                    if (!isset($fileNamesToUnexpectedlyFormattedYiiTs[$entry]))
                                    {
                                        $fileNamesToUnexpectedlyFormattedYiiTs[$entry] = array();
                                    }
                                    $fileNamesToUnexpectedlyFormattedYiiTs[$entry][] = $code;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $fileNamesToUnexpectedlyFormattedYiiTs;
    }

    function convertFileNameToCategoryToMessageToCategoryToMessageToFileName(array $fileNamesToCategoriesToMessages)
    {
        $categoriesToMessagesToFileNames = array();
        foreach ($fileNamesToCategoriesToMessages as $fileName => $filesCategoriesToMessages)
        {
            foreach ($filesCategoriesToMessages as $category => $messages)
            {
                foreach ($messages as $message)
                {
                    if (!in_array        ($category, $categoriesToMessagesToFileNames) ||
                        !array_key_exists($message,  $categoriesToMessagesToFileNames[$category]))
                    {
                        if (!isset($categoriesToMessagesToFileNames[$category][$message]))
                        {
                            $categoriesToMessagesToFileNames[$category][$message] = array();
                        }
                        $categoriesToMessagesToFileNames[$category][$message][] = $fileName;
                    }
                }
            }
        }
        foreach ($categoriesToMessagesToFileNames as $category => $unused)
        {
            uksort($categoriesToMessagesToFileNames[$category], 'lowercaseCompare');
        }
        return $categoriesToMessagesToFileNames;
    }

    function checkLanguagesMessageFilesFormat(array $fileNames, $messagesDirectoryName)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $problems = array();
        $directoryName = $fileNames['messageDirectory'];
        unset($fileNames['messageDirectory']);
        foreach ($fileNames as $fileName)
        {
            $fullFileName = "$directoryName/$fileName";
            $shortFileName = substr($fullFileName, strlen($messagesDirectoryName) + 1);
            $messages = require($fullFileName);
            if (!is_array($messages))
            {
                $problems = "$shortFileName is not a valid message file.\n";
                continue;
            }
            $messages = array_keys($messages);
            $messagesSorted = $messages;
            usort($messagesSorted, 'lowercaseCompare');
            if ($messages !== $messagesSorted)
            {
                $problems[] = "Messages not in alphabetical order in $shortFileName. " . compareArrays($messages, $messagesSorted);
            }
        }
        return $problems;
    }

    function diffLanguagesMessageFiles(array $fileNames1, array $fileNames2, $messagesDirectoryName, $moduleName)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $problems = array();
        $directoryName1 = $fileNames1['messageDirectory'];
        $directoryName2 = $fileNames2['messageDirectory'];
        $shortDirectoryName1 = substr($directoryName1, strlen($messagesDirectoryName) + 1);
        $shortDirectoryName2 = substr($directoryName2, strlen($messagesDirectoryName) + 1);
        unset($fileNames1['messageDirectory']);
        unset($fileNames2['messageDirectory']);
        $commonFileNames = array_intersect($fileNames1, $fileNames2);
        $only1FileNames  = array_diff     ($fileNames1, $fileNames2);
        $only2FileNames  = array_diff     ($fileNames2, $fileNames1);
        foreach ($commonFileNames as $fileName)
        {
            $fullFileName1 = "$directoryName1/$fileName";
            $fullFileName2 = "$directoryName2/$fileName";
            $shortFileName1 = substr($fullFileName1, strlen($messagesDirectoryName) + 1);
            $shortFileName2 = substr($fullFileName2, strlen($messagesDirectoryName) + 1);
            $messages1 = require($fullFileName1);
            if (is_array($messages1))
            {
                $messages1 = array_keys($messages1);
                $messages2 = require($fullFileName2);
                if (is_array($messages2))
                {
                    $messages2 = array_keys($messages2);
                    if ($messages1 != $messages2)
                    {
                        $problems[] = "$shortFileName1 and $shortFileName2 do not contain the same messages in $moduleName. " . compareArrays($messages1, $messages2);
                    }
                }
            }
        }
        foreach ($only2FileNames as $fileName)
        {
            $problems[] = "$fileName found in $shortDirectoryName1.";
        }
        foreach ($only1FileNames as $fileName)
        {
            $problems[] = "$fileName not in $shortDirectoryName2.";
        }
        return $problems;
    }

    function getLanguages($messagesDirectoryName)
    {
        assert('is_string($messagesDirectoryName)');
        assert('is_dir   ($messagesDirectoryName)');
        $entries = scandir($messagesDirectoryName);
        $languages = array();
        foreach ($entries as $entry)
        {
            $fullEntryName = "$messagesDirectoryName/$entry";
            if (is_dir($fullEntryName)   &&
                strlen($entry) == 2 &&
                $entry != '..')
            {
                $languages[] = $entry;
            }
        }
        return $languages;
    }

    function lowercaseCompare($a, $b)
    {
        assert('is_string($a)');
        assert('is_string($b)');
        $al = strtolower($a);
        $bl = strtolower($b);
        if ($al == $bl)
        {
            return 0;
        }
        elseif ($al > $bl)
        {
            return 1;
        }
        else
        {
            return -1;
        }
    }

    function compareArrays(array $array1, array $array2)
    {
        for ($i = 0; $i < count($array1) && count($array2); $i++)
        {
            if ($array1[$i] !== $array2[$i])
            {
                $entryIndex = $i + 1;
                return "Near entries '{$array1[$i]}' & '{$array2[$i]}'.";
            }
        }
        return 'OK';
    }
?>
