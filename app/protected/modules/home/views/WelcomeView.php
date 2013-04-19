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
     * View to display to users upon login.  Shows information such as tips, helpful links and ideas of what to do.
     */
    class WelcomeView extends View
    {
        protected $tipContent;

        protected $splashImageName;

        protected $hasDashboardAccess;

        protected static function renderHelpfulLinksContent()
        {
            $content  = '<div class="help-section">';
            $content .= '<h3>' . Zurmo::t('HomeModule', 'Helpful Links') . '</h3>';
            $content .= '<ul>';
            $content .= '<li>' . ZurmoHtml::link(Zurmo::t('HomeModule', 'Join the forum'), 'http://www.zurmo.org/forums') . '</li>';
            $content .= '<li>' . ZurmoHtml::link(Zurmo::t('HomeModule', 'Read the wiki'),  'http://zurmo.org/wiki') . '</li>';
            $content .= '<li>' . ZurmoHtml::link(Zurmo::t('HomeModule', 'View a tutorial'), 'http://www.zurmo.org/tutorials') . '</li>';
            $content .= '<li>' . ZurmoHtml::link(Zurmo::t('HomeModule', 'Watch a video'), 'http://zurmo.org/screencasts') . '</li>';
            $content .= '</ul>';
            $content .= '</div>';
            return $content;
        }

        protected static function renderSocialLinksContent()
        {
            return AboutView::renderSocialLinksContent();
        }

        public function __construct($tipContent, $hasDashboardAccess)
        {
            assert('is_string($tipContent)');
            assert('is_bool($hasDashboardAccess)');
            $this->tipContent                = $tipContent;
            $this->hasDashboardAccess        = $hasDashboardAccess;
        }

        protected function renderContent()
        {
            $rand     = mt_rand(1, 6);
            $theme    = 'themes/' . Yii::app()->theme->name;
            $imgUrl   = Yii::app()->baseUrl . '/' . $theme . '/images/welcome-gallery-' . $rand . '.png';
            $content  = '<div class="clearfix">';
            $content .= '<h1>' . Zurmo::t('HomeModule', 'Welcome to Zurmo'). '</h1>';
            $content .= static::renderSocialLinksContent();
            $content .= '<div id="welcome-content">';
            $content .= '<div id="instructions"><div id="welcome-gallery"><img src="' . $imgUrl . '" title="" /><span></span></div>';
            $content .= '<p>';
            $content .= Zurmo::t('HomeModule', 'Using a CRM shouldn\'t be a chore. With Zurmo, you can earn points, ' .
                               'collect badges, and compete against co-workers while getting your job done.');
            $content .= '</p>';
            $content .= $this->renderDashboardLinkContent();
            $content .= '</div>';
            $content .= static::renderHelpfulLinksContent();
            $content .= $this->renderTipsContent();
            $content .= $this->renderHideLinkContent();
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }

        protected function renderTipsContent()
        {
            if ($this->tipContent != null)
            {
                $content  = '<div class="help-section daily-tip">';
                $content .= '<h3>' . Zurmo::t('HomeModule', 'Tip of the Day') . '</h3>';
                $content .= '<ul>';
                $content .= '<li>' . $this->tipContent . '</li>';
                $content .= '</ul>';
                $content .= self::renderNextTipAjaxLink('tip-of-day-next-page-link', Zurmo::t('HomeModule', 'Next Tip'));
                $content .= '</div>';
                return $content;
            }
        }

        protected static function renderNextTipAjaxLink($id, $label)
        {
            assert('is_string($id)');
            assert('is_string($label)');
            $url       = Yii::app()->createUrl('home/default/getTip');
            // Begin Not Coding Standard
            return       ZurmoHtml::ajaxLink($label, $url,
                         array('type' => 'GET',
                               'dataType' => 'json',
                               'success' => "js:function(data){
                                    $('.daily-tip').find('li').html(data);
                              }"),
                         array('id' => $id, 'href' => '#'));
            // End Not Coding Standard
        }

        protected function renderDashboardLinkContent()
        {
            if ($this->hasDashboardAccess)
            {
                $label    = ZurmoHtml::wrapLabel(Zurmo::t('HomeModule', 'Go to the dashboard'));
                $content  = ZurmoHtml::link($label, Yii::app()->createUrl('home/default'), array('class' => 'dashboard-link z-button'));
                return $content;
            }
        }

        protected function renderHideLinkContent()
        {
            if ($this->hasDashboardAccess)
            {
                $label    = '<span></span>' . Zurmo::t('HomeModule', 'Don\'t show me this screen again');
                $content  = '<div class="hide-welcome">'.ZurmoHtml::link($label, Yii::app()->createUrl('home/default/hideWelcome'));
                $content .= ' <i>(' . Zurmo::t('HomeModule', 'Don\'t worry you can turn it on again') . ')</i></div>';
                return $content;
            }
        }
    }
?>
