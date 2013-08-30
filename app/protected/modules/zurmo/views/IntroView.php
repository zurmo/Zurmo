<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Base class for all into views.
     * An intro view is a view that provides an overview on modules works
     */
    abstract class IntroView extends View
    {
        protected $moduleName;

        /**
         * The content of the overview of how module works
         */
        protected abstract function renderIntroContent();

        public function __construct($moduleName)
        {
            assert('is_string($moduleName)');
            $this->moduleName = $moduleName;
        }

        /**
         * The id of the intro view panel
         */
        public function getPanelId()
        {
            return get_class($this) . '-intro-content';
        }

        public function getModuleName()
        {
            return $this->moduleName;
        }

        protected function renderContent()
        {
            $style = null;
            if ($this->isIntroViewDismissed())
            {
                $style = "style=display:none;"; // Not Coding Standard
            }
            $content  = "<div id='{$this->getPanelId()}' class='module-intro-content' {$style}>";
            $content .= $this->renderIntroContent();
            $content .= $this->renderHideLinkContent();
            $content .= '</div>';
            return $content;
        }

        protected function renderHideLinkContent()
        {
            $label       = '<span></span>' . Zurmo::t('ZurmoModule', 'Dismiss');
            $content     = '<div class="hide-module-intro">';
            $ajaxOptions = array('type'     => 'GET',
                                'success'  => "function()
                                       {
                                           $('#{$this->getPanelId()}-checkbox-id').attr('checked', false).parent().removeClass('c_on');
                                           $('#{$this->getPanelId()}').slideToggle();
                                       }
            ");
            $content .= ZurmoHtml::ajaxLink($label,
                                            Yii::app()->createUrl('zurmo/default/toggleDismissIntroView',
                                                                  array('moduleName' => $this->moduleName,
                                                                        'panelId'         => $this->getPanelId())
                                                    ),
                                            $ajaxOptions);
            $content .= '</div>';
            return $content;
        }

        public function isIntroViewDismissed()
        {
            if (ZurmoConfigurationUtil::getForCurrentUserByModuleName($this->moduleName, $this->getPanelId()))
            {
                return true;
            }
            return false;
        }
    }
?>