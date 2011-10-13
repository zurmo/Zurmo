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
     * The base View for the lead's convert view a ctions
     */
    class LeadConvertActionsView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $convertToAccountSetting;

        public function __construct($controllerId, $moduleId, $modelId, $convertToAccountSetting, $userCanCreateAccount)
        {
            assert('is_int($convertToAccountSetting)');
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_bool($userCanCreateAccount)');
            $this->controllerId            = $controllerId;
            $this->moduleId                = $moduleId;
            $this->modelId                 = $modelId;
            $this->convertToAccountSetting = $convertToAccountSetting;
            $this->userCanCreateAccount    = $userCanCreateAccount;
        }

        /**
         * Renders content for the view.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            Yii::app()->clientScript->registerScript('leadConvertActions', "
                $('.account-select-link').click( function()
                    {
                        $('#AccountConvertToView').hide();
                        $('#LeadConvertAccountSkipView').hide();
                        $('#AccountSelectView').show();
                        $('#account-create-title').hide();
                        $('#account-skip-title').hide();
                        $('#account-select-title').show();
                        return false;
                    }
                );
                $('.account-create-link').click( function()
                    {
                        $('#AccountConvertToView').show();
                        $('#LeadConvertAccountSkipView').hide();
                        $('#AccountSelectView').hide();
                        $('#account-create-title').show();
                        $('#account-skip-title').hide();
                        $('#account-select-title').hide();
                        return false;
                    }
                );
                $('.account-skip-link').click( function()
                    {
                        $('#AccountConvertToView').hide();
                        $('#LeadConvertAccountSkipView').show();
                        $('#AccountSelectView').hide();
                        $('#account-create-title').hide();
                        $('#account-skip-title').show();
                        $('#account-select-title').hide();
                        return false;
                    }
                );
            ");

            $cancelLink = new CancelConvertLinkActionElement($this->controllerId, $this->moduleId, $this->modelId);
            $createLink = CHtml::link(Yii::t('Default', 'Create AccountsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules()), '#', array('class' => 'account-create-link'));
            $selectLink = CHtml::link(Yii::t('Default', 'Select AccountsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules()), '#', array('class' => 'account-select-link'));
            $skipLink   = CHtml::link(Yii::t('Default', 'Skip AccountsModuleSingularLabel',
                            LabelUtil::getTranslationParamsForAllModules()), '#', array('class' => 'account-skip-link'));
            $content    = '<div class="view-toolbar">';
            $content   .= $cancelLink->render() . '&#160;';
            $content .= '</div>';
            $content .= '<div id="account-select-title" style="margin-bottom:5px;">';
            if($this->userCanCreateAccount)
            {
                $content .= $createLink .  '&#160;' . Yii::t('Default', 'or') . '&#160;';
            }
            $content .= '<b>' . Yii::t('Default', 'Select AccountsModuleSingularLabel',
                                    LabelUtil::getTranslationParamsForAllModules()) . '</b>&#160;';
            if ($this->convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $content .= Yii::t('Default', 'or') . '&#160;' . $skipLink;
            }
            $content .= '</div>';
            $content .= '<div id="account-create-title" style="margin-bottom:5px;">';
            $content .= '<b>' . Yii::t('Default', 'Create AccountsModuleSingularLabel',
                                    LabelUtil::getTranslationParamsForAllModules()) . '</b>&#160;';
            $content .= Yii::t('Default', 'or') . '&#160;' . $selectLink . '&#160;';
            if ($this->convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $content .= Yii::t('Default', 'or') . '&#160;' . $skipLink;
            }
            $content .= '</div>';
            if ($this->convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED)
            {
                $content .= '<div id="account-skip-title" style="margin-bottom:5px;">';
                if($this->userCanCreateAccount)
                {
                    $content .= $createLink . '&#160;' . Yii::t('Default', 'or') . '&#160;';
                }
                $content .= $selectLink . '&#160;' . Yii::t('Default', 'or') . '&#160;';
                $content .= '<b>' . Yii::t('Default', 'Skip AccountsModuleSingularLabel',
                                        LabelUtil::getTranslationParamsForAllModules()) . '</b>&#160;';
                $content .= '</div>';
            }
            return $content;
        }
    }
?>