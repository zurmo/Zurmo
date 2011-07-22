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
     * Application loaded component at run time.  @see BeginBehavior - calls load() method.
     */
    class ZurmoLanguageHelper extends CApplicationComponent
    {
        private $_moduleLabelTranslationParameters;

        /**
         * Sets active language.
         */
        public function setActive($language)
        {
            assert('is_string($language)');
            Yii::app()->user->setState('language', $language);
        }

        /**
         * Loads language for current user.  This is called by BeginBehavior.
         */
        public function load()
        {
            if ( null == $language = Yii::app()->user->getState('language'))
            {
                $language = $this->getForCurrentUser();
                Yii::app()->user->setState('language', $language);
            }
            Yii::app()->language = $language;
        }

        /**
         * For the current user, get the language setting.
         * The current user is specified here: Yii::app()->user->userModel
         * @return string - language.
         */
        public function getForCurrentUser()
        {
            if (Yii::app()->user->userModel != null && Yii::app()->user->userModel->language != null)
            {
                return Yii::app()->user->userModel->language;
            }
            return Yii::app()->language;
        }

        /**
         * Get supported languages and translates names of language. Uses language id as
         * key.
         * @return array of language keys/ translated names.
         */
        public function getSupportedLanguagesData()
        {
            $data = array();
            foreach (Yii::app()->params['supportedLanguages'] as $language => $name)
            {
                $data[$language] = Yii::t('Default', $name);
            }
            return $data;
        }

        /**
         * Module translation parameters are used by Yii::t as the third parameter to define the module labels.  These
         * parameter values resolve any custom module label names that have been specified in the module metadata.
         * @return array of key/value module label pairings.
         * TODO: cache results after first retrieval on each page load. Potentially across mulitple page loads
         */
        public function getAllModuleLabelsAsTranslationParameters()
        {
            if ($this->_moduleLabelTranslationParameters != null)
            {
                return $this->_moduleLabelTranslationParameters;
            }
            $modules = Module::getModuleObjects();
            $params  = array();
            foreach ($modules as $module)
            {
                $params[get_class($module) . 'SingularLabel']
                    = $module::getModuleLabelByTypeAndLanguage('Singular');
                $params[get_class($module) . 'SingularLowerCaseLabel']
                    = $module::getModuleLabelByTypeAndLanguage('SingularLowerCase');
                $params[get_class($module) . 'PluralLabel']
                    = $module::getModuleLabelByTypeAndLanguage('Plural');
                $params[get_class($module) . 'PluralLowerCaseLabel']
                    = $module::getModuleLabelByTypeAndLanguage('PluralLowerCase');
            }
            $this->_moduleLabelTranslationParameters = $params;
            return $params;
        }

        /**
         * Used by tests to reset value between tests.
         */
        public function resetModuleLabelTranslationParameters()
        {
            $this->_moduleLabelTranslationParameters = null;
        }
    }
?>