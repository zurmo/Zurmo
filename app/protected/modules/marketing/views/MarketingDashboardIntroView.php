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
     * View when a user first comes to the marketing dashboard. Provides an overview of how marketing works
     */
    class MarketingDashboardIntroView extends View
    {
        const PANEL_ID            = 'marketing-intro-content';

        const LINK_ID             = 'hide-marketing-intro';

        const HIDDEN_COOKIE_VALUE = 'hidden';

        /**
         * @var string
         */
        protected $cookieValue;

        /**
         * @return string
         */
        public static function resolveCookieId()
        {
            return self::PANEL_ID . '-panel';
        }

        public function __construct($cookieValue)
        {
            assert('$cookieValue == null || is_string($cookieValue)');
            $this->cookieValue = $cookieValue;
        }

        /**
         * @return bool|string
         */
        protected function renderContent()
        {
            $this->registerScripts();
            if ($this->cookieValue == self::HIDDEN_COOKIE_VALUE)
            {
                $style = "style=display:none;"; // Not Coding Standard
            }
            else
            {
                $style = null;
            }
            $content  = '<div id="' . self::PANEL_ID . '" ' . $style . '>';
            $content .= '<h1>' . Zurmo::t('MarketingModule', 'How does Email Marketing work in Zurmo?', LabelUtil::getTranslationParamsForAllModules()). '</h1>';

            $content .= '<div id="marketing-intro-steps" class="clearfix">';
            $content .= '<div class="third"><h3>' . Zurmo::t('Core', 'Step') . '<strong>1<span>➜</span></strong></h3>';
            $content .= '<p><strong>' . Zurmo::t('MarketingModule', 'Group') . '</strong>';
            $content .= Zurmo::t('MarketingModule', 'Group together the email recipients into a list, use different lists for different purposes');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third"><h3>' . Zurmo::t('Core', 'Step') . '<strong>2<span>➜</span></strong></h3>';
            $content .= '<p><strong>' . Zurmo::t('MarketingModule', 'Create') . '</strong>';
            $content .= Zurmo::t('MarketingModule', 'Create the template for the email you are going to send, import and use either full, ' .
                        'rich HTML templates or plain text');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third"><h3>' . Zurmo::t('Core', 'Step') . '<strong>3</strong></h3>';
            $content .= '<p><strong>' . Zurmo::t('MarketingModule', 'Launch') . '</strong>';
            $content .= Zurmo::t('MarketingModule', 'Create a campaign where you can schedule your email to go out, pick the List(s) of recipients, ' .
                        'add and schedule autoresponders and track your overall campaign performance');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '</div>';
            $content .= $this->renderHideLinkContent();
            $content .= '</div>';
            return $content;
        }

        /**
         * @return string
         */
        protected function renderHideLinkContent()
        {
            $label    = '<span></span>' . Zurmo::t('MarketingModule', 'Dismiss');
            $content  = '<div class="' . self::LINK_ID . '">'.ZurmoHtml::link($label, '#');
            $content .= '</div>';
            return $content;
        }

        protected function registerScripts()
        {
            $script = "$('." . self::LINK_ID . "').click(function()
            {
                        $('#" . self::PANEL_ID . "').slideToggle();
                        document.cookie = '" . self::resolveCookieId() . "=" . static::HIDDEN_COOKIE_VALUE . "';
                        $('#" . self::PANEL_ID . "-checkbox-id').attr('checked', false).parent().removeClass('c_on');
                        return false;
            })";
            Yii::app()->clientScript->registerScript(self::LINK_ID, $script);
        }
    }
?>
