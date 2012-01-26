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

    /**
     * View that renders breadcrumb content
     */
    class DesignerBreadCrumbView extends View
    {
        protected $controllerId;
        protected $moduleId;
        protected $breadcrumbLinks;

        public function __construct($controllerId, $moduleId, $breadcrumbLinks)
        {
            $this->controllerId    = $controllerId;
            $this->moduleId        = $moduleId;
            $this->breadcrumbLinks = $breadcrumbLinks;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $homeUrl = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/index');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Breadcrumbs");
            $cClipWidget->widget('zii.widgets.CBreadcrumbs', array(
                'homeLink'  => CHtml::link(Yii::t('Default', 'Designer Home'), $homeUrl),
                'links'     => $this->breadcrumbLinks,
                'separator' => ' &#187; ',
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['Breadcrumbs'];
            $content .= '<div style="margin-bottom:5px"></div>';
            return $content;
        }
    }
?>