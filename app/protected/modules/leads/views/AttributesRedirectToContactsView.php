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
     * Helper tool for designer.  When a user select the fields option in the Leads module, it will provide a message
     * explaining that leads attributes are the same as contacts and therefore controlled by the contacts attribute list.
     */
    class AttributesRedirectToContactsView extends View
    {
        protected $controllerId;

        protected $moduleId;

        public function __construct($controllerId, $moduleId)
        {
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
        }

        protected function renderContent()
        {
            $content  = '<div class="horizontal-line">';
            $content .= Yii::t('Default', 'LeadsModulePluralLabel and ContactsModulePluralLabel are the same records,' .
                                          ' just in a different status. To create a LeadsModuleSingularLowerCaseLabel' .
                                          ' field, create a ContactsModuleSingularLowerCaseLabel field, and then it ' .
                                          ' will be placeable in the LeadsModulePluralLowerCaseLabel layouts.',
                                          LabelUtil::getTranslationParamsForAllModules());
            $content .= '</div>' . "\n";
            return $content;
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>