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
            $content  = '<div id="LoginLogo" class="zurmo-logo"></div>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'This is <b>version {zurmoVersion}</b>, of <b>Zurmo</b>.',
                        array('{zurmoVersion}' => $zurmoVersion));
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<b>Zurmo</b> is a <b>Customer Relationship Management</b> system by <b>Zurmo Inc.</b>');
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'Visit the <b>Zurmo Open Source Project</b> at {url}.',
                           array('{url}' => '<a href="http://www.zurmo.org">http://www.zurmo.org</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'Visit <b>Zurmo Inc.</b> at {url}.',
                        array('{url}' => '<a href="http://www.zurmo.com">http://www.zurmo.com</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<b>Zurmo</b> is licensed under the GPLv3.  You can read the <a href="http://www.zurmo.org/license">license here</a>');
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<b>Zurmo</b> uses the following great Open Source tools and frameworks:');
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '{url} (version {version} is installed)',
                           array('{url}'     => '<a href="http://www.yiiframework.com">Yii Framework</a>',
                                 '{version}' => $yiiVersion));
            $content .= '<br/>';
            $content .= Yii::t('Default', '{url} (version {version} is installed)',
                           array('{url}'     => '<a href="http://www.redbeanphp.com">RedBeanPHP ORM</a>',
                                 '{version}' => $redBeanVersion));
            $content .= '<br/>';
            $content .= Yii::t('Default', '{url} (installed with Yii)',
                           array('{url}'     => '<a href="http://www.jquery.com">jQuery JavaScript Framework</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            return $content;
        }
    }
?>
