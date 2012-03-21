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

    class LeadConvertView extends GridView
    {
        protected $cssClasses =  array('DetailsView');

        public function __construct(
                $controllerId,
                $moduleId,
                $modelId,
                $title,
                $selectAccountform,
                $account,
                $convertToAccountSetting,
                $userCanCreateAccount
            )
        {
            assert('$convertToAccountSetting != LeadsModule::CONVERT_NO_ACCOUNT');
            assert('is_bool($userCanCreateAccount)');

            //if has errors, then show by default
            if ($selectAccountform->hasErrors())
            {
                Yii::app()->clientScript->registerScript('leadConvert', "
                    $(document).ready(function()
                        {
                            $('#AccountConvertToView').hide();
                            $('#LeadConvertAccountSkipView').hide();
                            $('#account-skip-title').hide();
                            $('#account-create-title').hide();
                        }
                    );
                ");
            }
            else
            {
                if ($userCanCreateAccount)
                {
                    Yii::app()->clientScript->registerScript('leadConvert', "
                        $(document).ready(function()
                            {
                                $('#AccountSelectView').hide();
                                $('#LeadConvertAccountSkipView').hide();
                                $('#account-skip-title').hide();
                                $('#account-select-title').hide();
                            }
                        );
                    ");
                }
                else
                {
                    Yii::app()->clientScript->registerScript('leadConvert', "
                        $(document).ready(function()
                            {
                                $('#account-create-title').hide();
                                $('#AccountConvertToView').hide();
                                $('#LeadConvertAccountSkipView').hide();
                                $('#account-skip-title').hide();
                            }
                        );
                    ");
                }
            }
            if ($convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $gridSize = 4;
            }
            else
            {
                $gridSize = 3;
            }
            $title = Yii::t('Default', 'LeadsModuleSingularLabel Conversion',
                                                LabelUtil::getTranslationParamsForAllModules()) . ': ' . $title;
            parent::__construct($gridSize, 1);
            $this->setView(new LeadConvertActionsView($controllerId, $moduleId, $modelId, $convertToAccountSetting,
                                                      $userCanCreateAccount, $title), 0, 0);
            $this->setView(new AccountSelectView($controllerId, $moduleId, $modelId, $selectAccountform), 1, 0);
            $this->setView(new AccountConvertToView($controllerId, $moduleId, $account, $modelId), 2, 0);

            if ($convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $this->setView(new LeadConvertAccountSkipView($controllerId, $moduleId, $modelId), 3, 0);
            }
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>