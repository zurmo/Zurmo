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

    class MarketingIntroLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return null;
        }

        public function render()
        {
            if ($this->moduleId == 'marketing' && $this->controllerId == 'default' &&
               (Yii::app()->controller->action->id == 'dashboardDetails' ||
                Yii::app()->controller->action->id == null ||
                Yii::app()->controller->action->id == 'index'))
            {
                $items          = array($this->renderMenuItem());
                $clipName       = get_class($this);
                $cClipWidget    = new CClipWidget();
                $cClipWidget->beginClip($clipName);
                $cClipWidget->widget('application.core.widgets.MinimalDynamicLabelMbMenu', array(
                    'htmlOptions'   => array(
                        'id' => $clipName,
                        'class' => 'clickable-mbmenu'
                    ),
                    'items'         => $items,
                ));
                $cClipWidget->endClip();
                return $cClipWidget->getController()->clips[$clipName];
            }
        }

        public function renderMenuItem()
        {
            return array(
                'label' => $this->getLabel(),
                'url'   => $this->getRoute(),
                'items' => array(
                    array(
                        'label'                 => '',
                        'dynamicLabelContent'   => $this->renderHideOrShowContent(),
                    ),
                ),
            );
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('ZurmoModule', 'Screen Options');
        }

        protected function getDefaultRoute()
        {
            return null;
        }

        protected function renderHideOrShowContent()
        {
            $name        = $this->getPanelId() . '-checkbox-id';
            $ajaxOptions = array('type'     => 'GET',
                                 'url'      => Yii::app()->createUrl('zurmo/default/toggleDismissIntroView',
                                                                  array('moduleName' => $this->getModuleName(),
                                                                        'panelId'    => $this->getPanelId())
                                                    ),
                                 'success'  => "function()
                                       {
                                           var checked = $('#{$this->getPanelId()}-checkbox-id').attr('checked');
                                           $('#{$this->getPanelId()}').slideToggle();
                                           $('#{$this->getPanelId()}-checkbox-id').attr('checked', !checked);
                                           if (checked)
                                           {
                                             $('#{$this->getPanelId()}-checkbox-id').parent().removeClass('c_on');
                                           }
                                           else
                                           {
                                             $('#{$this->getPanelId()}-checkbox-id').parent().addClass('c_on');
                                           }
                                       }
            ");
            $htmlOptions = array('id'   => $this->getPanelId() . '-checkbox-id',
                                 'ajax' => $ajaxOptions);
            $checkBox    = ZurmoHtml::checkBox($name, $this->getChecked(), $htmlOptions);
            return '<div class="screen-options"><h4>Screen Options</h4>' . $checkBox . Zurmo::t('MarketingModule', 'Show intro message') . '</div>';
        }

        protected function getPanelId()
        {
            return $this->params['panelId'];
        }

        protected function getChecked()
        {
            return $this->params['checked'];
        }

        protected function getModuleName()
        {
            return $this->params['moduleName'];
        }
    }
?>