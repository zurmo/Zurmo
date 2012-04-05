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

    class AboutView extends View
    {
        protected function renderContent()
        {
            $zurmoVersion    = VERSION;
            $yiiVersion     = YiiBase::getVersion();
            if (method_exists('R', 'getVersion'))
            {
                $redBeanVersion =  R::getVersion();
            }
            else
            {
                $redBeanVersion = '&lt; 1.2.9.1';
            }
            $content  = '<div id="ZurmoLogo" class="zurmo-logo"></div>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'This is <strong>version {zurmoVersion}</strong> of <strong>Zurmo</strong>.',
                        array('{zurmoVersion}' => $zurmoVersion));
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<strong>Zurmo</strong> is a <strong>Customer Relationship Management</strong> system by <strong>Zurmo Inc.</strong>');
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'Visit the <strong>Zurmo Open Source Project</strong> at {url}.',
                           array('{url}' => '<a href="http://www.zurmo.org">http://www.zurmo.org</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'Visit <strong>Zurmo Inc.</strong> at {url}.',
                        array('{url}' => '<a href="http://www.zurmo.com">http://www.zurmo.com</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<strong>Zurmo</strong> is licensed under the GPLv3.  You can read the license <a href="http://www.zurmo.org/license">here</a>.');
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<strong>Zurmo</strong> uses the following great Open Source tools and frameworks:');
            $content .= '<ul>';
            $content .= '<li>';
            $content .= Yii::t('Default', '{url} (version {version} is installed)',
                           array('{url}'     => '<a href="http://www.yiiframework.com">Yii Framework</a>',
                                 '{version}' => $yiiVersion));
            $content .= '</li>';
			$content .= '<li>';
            $content .= Yii::t('Default', '{url} (version {version} is installed)',
                           array('{url}'     => '<a href="http://www.redbeanphp.com">RedBeanPHP ORM</a>',
                                 '{version}' => $redBeanVersion));
            $content .= '</li>';
			$content .= '<li>';
            $content .= Yii::t('Default', '{url} (installed with Yii)',
                           array('{url}'     => '<a href="http://www.jquery.com">jQuery JavaScript Framework</a>'));
            $content .= '</li>';
            $content .= '</ul></p>';
            return $content;
        }
    }
?>
