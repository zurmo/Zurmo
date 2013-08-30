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
     * Helper class for working with sliding panels in the detail views.
     */
    abstract class SlidingPanelsUtil
    {
        const SHOULD_SLIDE_TO_FIRST_PANEL_KEY_NAME = 'slideToFirstPanel';

        /**
         * Implement in child class
         * @throws NotImplementedException
         */
        public static function getSlideToSecondPanelLabel()
        {
            throw new NotImplementedException();
        }

        /**
         * Implement in child class
         * @throws NotImplementedException
         */
        public static function getSlideToFirstPanelLabel()
        {
            throw new NotImplementedException();
        }

        /**
         * @param $portletId - unique id
         * @return string
         */
        public static function renderToggleLinkContent($portletId)
        {
            if (static::resolveShouldSlideToSecondPanel($portletId))
            {
                $slideToSecond = true;
            }
            else
            {
                $slideToSecond = false;
            }
            static::registerSlidingPanelsScript($portletId);
            if ($slideToSecond)
            {
                $label      = static::getSlideToSecondPanelLabel();
                $extraClass = ' slide-to-second-panel';
            }
            else
            {
                $label      = static::getSlideToFirstPanelLabel();
                $extraClass = null;
            }
            $content  = ZurmoHtml::tag('span', array(), $label);
            $content  = ZurmoHtml::link($content, '#', array('id'    => 'sliding-panel-toggle',
                                                             'class' => 'vertical-forward-pager' . $extraClass));
            return $content;
        }

        public static function setShouldSlideToSecondPanelForCurrentUser($portletId, $shouldSlideToSecondPanel)
        {
            assert('is_bool($shouldSlideToSecondPanel)');
            LatestActivitiesPortletPersistentConfigUtil::
                setForCurrentUserByPortletIdAndKey($portletId, static::SHOULD_SLIDE_TO_FIRST_PANEL_KEY_NAME, !$shouldSlideToSecondPanel);
        }

        /**
         * @param $portletId - unique id
         * @return bool
         */
        public static function resolveShouldSlideToSecondPanel($portletId)
        {
            return !(LatestActivitiesPortletPersistentConfigUtil::
                getForCurrentUserByPortletIdAndKey($portletId, static::SHOULD_SLIDE_TO_FIRST_PANEL_KEY_NAME, true));
        }

        protected static function registerSlidingPanelsScript($portletId)
        {
            $script = "
                $('#sliding-panel-toggle').click(function()
                {
                    $('.sliding-panel').slideToggle();
                    $('.sliding-panel').toggleClass('showing-panel');
                    if ($(this).hasClass('slide-to-second-panel'))
                    {
                        $(this).removeClass('slide-to-second-panel');
                        $(this).addClass('slide-to-first-panel');
                        $(this).find('span').text('" . static::getSlideToFirstPanelLabel() . "');
                        " . static::getAjaxSubmitScript($portletId, false) . "
                    }
                    else
                    {
                        $(this).removeClass('slide-to-first-panel');
                        $(this).addClass('slide-to-second-panel');
                        $(this).find('span').text('" . static::getSlideToSecondPanelLabel() . "');
                        " . static::getAjaxSubmitScript($portletId, true) . "
                    }
                    return false;
                });
            ";
            Yii::app()->getClientScript()->registerScript('slidingPanelsScript', $script);
        }

        /**
         * @param $portletId - unique id
         * @param bool $shouldSlideToSecondPanel
         * @return string
         */
        protected static function getAjaxSubmitScript($portletId, $shouldSlideToSecondPanel)
        {
            assert('is_bool($shouldSlideToSecondPanel)');
            $urlScript = 'js:$.param.querystring("' . static::getAjaxUpdateSlidingPanelShowingByDefaultUrl() . '", "' .
                         'portletId=' . $portletId . '&shouldSlideToSecondPanel=' . $shouldSlideToSecondPanel . '")'; // Not Coding Standard
            return ZurmoHtml::ajax(array('type' => 'GET', 'url' =>  $urlScript));
        }

        protected  static function getAjaxUpdateSlidingPanelShowingByDefaultUrl()
        {
            return Yii::app()->createUrl('/zurmo/default/ajaxUpdateSlidingPanelShowingByDefault');
        }
    }
?>