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
     * Controller Class for managing languages.
     *
     */
    class ZurmoLanguageController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH,
                    'moduleClassName' => 'ZurmoModule',
                    'rightName' => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
               ),
            );
        }

        public function actionIndex()
        {
            $this->actionConfigurationList();
        }

        public function actionConfigurationList()
        {
            $redirectUrlParams = array('/zurmo/' . $this->getId() . '/ConfigurationList');
            $messageBoxContent = Zurmo::t('ZurmoModule', 'Don\'t see a language that you want to load? Help us make Zurmo better by contributing on a translation. Click <a href="{l10nServerDomain}" class="simple-link normal-size" target="_blank">here</a>.',
                array(
                    '{l10nServerDomain}' => ZurmoTranslationServerUtil::getServerDomain()
                )
            );
            $view = new LanguageTitleBarConfigurationListView(
                            $this->getId(),
                            $this->getModule()->getId(),
                            $messageBoxContent);
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $view));
            echo $view->render();
        }

        public function actionActivate($languageCode)
        {
            $languageData = LanguagesCollectionView::getLanguageDataByLanguageCode($languageCode);
            try
            {
                if (Yii::app()->languageHelper->activateLanguage($languageCode))
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} activated successfully',
                        array('{languageName}' => $languageData['label'])
                    );

                    $content = LanguagesCollectionView::renderFlashMessage($message);

                    RedBeansCache::forgetAll();
                    GeneralCache::forgetAll();
                }
            }
            catch (Exception $e)
            {
                $exceptionMessage = $e->getMessage();
                if (!empty($exceptionMessage))
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} activation failed. Error: {errorMessage}',
                        array(
                            '{languageName}' => $languageData['label'],
                            '{errorMessage}' => $exceptionMessage
                        )
                    );
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} activation failed. Unexpected error.',
                        array('{languageName}' => $languageData['label'])
                    );
                }

                $content = LanguagesCollectionView::renderFlashMessage(
                    $message,
                    true
                );
            }

            $view = new LanguagesCollectionView(
                $this->getId(),
                $this->getModule()->getId()
            );
            $content .= $view->renderLanguageRow($languageCode);
            echo $content;
        }

        public function actionUpdate($languageCode)
        {
            $languageData = LanguagesCollectionView::getLanguageDataByLanguageCode($languageCode);
            try
            {
                if (Yii::app()->languageHelper->updateLanguage($languageCode))
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} updated successfully',
                        array('{languageName}' => $languageData['label'])
                    );

                    $content = LanguagesCollectionView::renderFlashMessage($message);

                    RedBeansCache::forgetAll();
                    GeneralCache::forgetAll();
                }
            }
            catch (Exception $e)
            {
                $exceptionMessage = $e->getMessage();

                if (!empty($exceptionMessage))
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} update failed. Error: {errorMessage}',
                        array(
                            '{languageName}' => $languageData['label'],
                            '{errorMessage}' => $exceptionMessage
                        )
                    );
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} update failed. Unexpected error.',
                        array('{languageName}' => $languageData['label'])
                    );
                }

                $content = LanguagesCollectionView::renderFlashMessage(
                    $message,
                    true
                );
            }

            $view = new LanguagesCollectionView(
                $this->getId(),
                $this->getModule()->getId()
            );
            $content .= $view->renderLanguageRow($languageCode);
            echo $content;
        }

        public function actionDeactivate($languageCode)
        {
            $languageData = LanguagesCollectionView::getLanguageDataByLanguageCode($languageCode);

            try
            {
                if (Yii::app()->languageHelper->deactivateLanguage($languageCode))
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} deactivated successfully',
                        array('{languageName}' => $languageData['label'])
                    );

                    $content = LanguagesCollectionView::renderFlashMessage($message);

                    RedBeansCache::forgetAll();
                    GeneralCache::forgetAll();
                }
            }
            catch (Exception $e)
            {
                $exceptionMessage = $e->getMessage();
                if (!empty($exceptionMessage))
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} deactivate failed. Error: {errorMessage}',
                        array(
                            '{languageName}' => $languageData['label'],
                            '{errorMessage}' => $exceptionMessage
                        )
                    );
                }
                else
                {
                    $message = Zurmo::t('ZurmoModule', '{languageName} deactivate failed. Unexpected error.',
                        array('{languageName}' => $languageData['label'])
                    );
                }

                $content = LanguagesCollectionView::renderFlashMessage(
                    $message,
                    true
                );
            }

            $view = new LanguagesCollectionView(
                $this->getId(),
                $this->getModule()->getId()
            );
            $content .= $view->renderLanguageRow($languageCode);
            echo $content;
        }
    }
?>