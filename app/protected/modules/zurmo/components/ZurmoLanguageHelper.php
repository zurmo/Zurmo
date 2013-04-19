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
     * Application loaded component at run time.  @see BeginBehavior - calls load() method.
     */
    class ZurmoLanguageHelper extends CApplicationComponent
    {
        /**
         * The base language as defined by the config file. This language cannot be deactivated.
         * @var string
         */
        protected $baseLanguage;

        /**
         * Sets active language.
         */
        public function setActive($language)
        {
            assert('is_string($language)');
            Yii::app()->user->setState('language', $language);
            $this->flushModuleLabelTranslationParameters();
        }

        /**
         * Loads language for current user.  This is called by BeginBehavior. This will also copy the base language
         * into a parameter $baseLanguage in this class.
         */
        public function load()
        {
            $this->baseLanguage = Yii::app()->language;
            if (Yii::app()->user->userModel == null)
            {
                $language = $this->getForCurrentUser();
            }
            else
            {
                if (null == $language = Yii::app()->user->getState('language'))
                {
                    $language = $this->getForCurrentUser();
                    Yii::app()->user->setState('language', $language);
                    $this->flushModuleLabelTranslationParameters();
                }
            }
            Yii::app()->language = $language;
        }

        public function getBaseLanguage()
        {
            return $this->baseLanguage;
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
         * Get supported languages and data of language. Uses language id as
         * key.
         * @return array of language keys/ data.
         */
        public function getSupportedLanguagesData()
        {
            $data = array();
            foreach (ZurmoTranslationServerUtil::getAvailableLanguages() as $language)
            {
                $data[$language['code']] = $language;
                $data[$language['code']]['label'] = sprintf(
                    '%s (%s)',
                    $language['name'],
                    $language['nativeName']
                );
            }
            return $data;
        }

        /**
         * Module translation parameters are used by Zurmo::t as the third parameter to define the module labels.  These
         * parameter values resolve any custom module label names that have been specified in the module metadata.
         * @return array of key/value module label pairings.
         * Caches results to improve performance.
         */
        public function getAllModuleLabelsAsTranslationParameters()
        {
            try
            {
                $moduleLabelTranslationParameters = GeneralCache::
                                                    getEntry('moduleLabelTranslationParameters' . Yii::app()->language);
                return $moduleLabelTranslationParameters;
            }
            catch (NotFoundException $e)
            {
                $modules = Module::getModuleObjects();
                $params  = array();
                foreach ($modules as $module)
                {
                    $params[get_class($module) . 'SingularLabel']
                        = $module::getModuleLabelByTypeAndLanguage('Singular', Yii::app()->language);
                    $params[get_class($module) . 'SingularLowerCaseLabel']
                        = $module::getModuleLabelByTypeAndLanguage('SingularLowerCase', Yii::app()->language);
                    $params[get_class($module) . 'PluralLabel']
                        = $module::getModuleLabelByTypeAndLanguage('Plural', Yii::app()->language);
                    $params[get_class($module) . 'PluralLowerCaseLabel']
                        = $module::getModuleLabelByTypeAndLanguage('PluralLowerCase', Yii::app()->language);
                }
                GeneralCache::cacheEntry('moduleLabelTranslationParameters' . Yii::app()->language, $params);
                return $params;
            }
        }

        /**
         * Used by tests to reset value between tests.
         */
        public function flushModuleLabelTranslationParameters()
        {
            foreach (Yii::app()->params['supportedLanguages'] as $language => $notUsed)
            {
                GeneralCache::forgetEntry('moduleLabelTranslationParameters' . $language);
            }
        }

        /**
         * Returns an array of active language models.
         */
        public function getActiveLanguagesData()
        {
            $beans = ActiveLanguage::getAll();

            $beans[] = ActiveLanguage::getSourceLanguageModel();

            foreach ($beans as $bean)
            {
                $activeLanguages[$bean->code] = array(
                    'canDeactivate'         => $this->canDeactivateLanguage($bean->code),
                    'activationDatetime'    => $bean->activationDatetime,
                    'lastUpdateDatetime'    => $bean->lastUpdateDatetime,
                    'nativeName'            => $bean->nativeName,
                    'name'                  => $bean->name,
                    'label'                 => $this->formatLanguageLabel($bean)
                );
            }

            // Sort languages alphabetically by the language code
            ksort($activeLanguages);

            return $activeLanguages;
        }

        /**
         * A language that is the base language or currently selected as a user's default language, cannot be removed.
         * @return true if the specified language can be removed.
         */
        public function canDeactivateLanguage($language)
        {
            assert('is_string($language)');
            if ($language == $this->baseLanguage || $this->isLanguageADefaultLanguageForAnyUsers($language))
            {
                return false;
            }
            return true;
        }

        /**
         * Activates a language
         */
        public function activateLanguage($languageCode)
        {
            $activeLanguages = $this->getActiveLanguagesData();
            // Check if the language is already active
            if (array_key_exists($languageCode, $activeLanguages))
            {
                return true;
            }

            $supportedLanguages = $this->getSupportedLanguagesData();
            // Check if the language is supported
            if (!array_key_exists($languageCode, $supportedLanguages))
            {
                throw new NotFoundException(Zurmo::t('ZurmoModule', 'Language not supported.'));
            }

            $translationUrl = ZurmoTranslationServerUtil::getPoFileUrl($languageCode);

            // Check if the po file exists
            $headers = get_headers($translationUrl);
            list($version, $status_code, $msg) = explode(' ', $headers[0], 3);
            if ($status_code != 200)
            {
                throw new NotFoundException(Zurmo::t('ZurmoModule', 'Translation not available.'));
            }

            if (ZurmoMessageSourceUtil::importPoFile($languageCode, $translationUrl))
            {
                $language = new ActiveLanguage;
                $language->code = $supportedLanguages[$languageCode]['code'];
                $language->name = $supportedLanguages[$languageCode]['name'];
                $language->nativeName = $supportedLanguages[$languageCode]['nativeName'];
                $language->activationDatetime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $language->lastUpdateDatetime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if ($language->save())
                {
                    return true;
                }
            }

            throw new FailedServiceException(Zurmo::t('ZurmoModule', 'Unexpected error. Please try again later.'));
        }

        /**
         * Updates a language
         */
        public function updateLanguage($languageCode)
        {
            try
            {
                $language = ActiveLanguage::getByCode($languageCode);
            }
            catch (NotFoundException $e)
            {
                throw new NotFoundException(Zurmo::t('ZurmoModule', 'Language not active.'));
            }

            $translationUrl = ZurmoTranslationServerUtil::getPoFileUrl($language->code);

            // Check if the po file exists
            $headers = get_headers($translationUrl);
            list($version, $status_code, $msg) = explode(' ', $headers[0], 3);
            if ($status_code != 200)
            {
                throw new NotFoundException(Zurmo::t('ZurmoModule', 'Translation not available.'));
            }

            if (ZurmoMessageSourceUtil::importPoFile($language->code, $translationUrl))
            {
                $language->lastUpdateDatetime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if ($language->save())
                {
                    return true;
                }
            }

            throw new FailedServiceException(Zurmo::t('ZurmoModule', 'Unexpected error. Please try again later.'));
        }

        public function deactivateLanguage($languageCode)
        {
            try
            {
                $language = ActiveLanguage::getByCode($languageCode);
            }
            catch (NotFoundException $e)
            {
                throw new NotFoundException(Zurmo::t('ZurmoModule', 'Language not active.'));
            }

            if ($language->delete())
            {
                return true;
            }

            throw new FailedServiceException(Zurmo::t('ZurmoModule', 'Unexpected error. Please try again later.'));
        }

        public function formatLanguageLabel($language)
        {
            if ($language instanceof ActiveLanguage)
            {
                $language = array(
                    'name'       => $language->name,
                    'nativeName' => $language->nativeName
                );
            }

            if (!empty($language['nativeName']))
            {
                return sprintf('%s (%s)', $language['name'], $language['nativeName']);
            }
            else
            {
                return $language['name'];
            }
        }

        /**
         * Given a language, is it in use as a default language by any of the users.
         * @param string $language
         * @return true if in use, otherwise returns false.
         */
        protected function isLanguageADefaultLanguageForAnyUsers($language)
        {
            assert('is_string($language)');
            $tableName = User::getTableName('User');
            $beans = R::find($tableName, "language = '$language'");
            if (count($beans) > 0)
            {
                return true;
            }
            return false;
        }
    }
?>