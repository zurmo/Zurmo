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

    class MashableInboxMassActionElement extends LinkActionElement
    {
        private $massOptions;

        public function getActionType()
        {
            return 'MassEdit';
        }

        public function render()
        {
            $this->massOptions = $this->getDefaultMassActions();
            if ($this->getModelClassName() !== null)
            {
                $this->addModelMassOptions();
            }
            $menuItems   = $this->getMenuItems();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'MashableInboxMassActionMenu'),
                'items'       => array($menuItems),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('MashableInbox', 'Options');
        }

        protected function getListViewGridId()
        {
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }

        protected function getModelClassName()
        {
            return $this->params['modelClassName'];
        }

        protected function getFormName()
        {
            if (!isset($this->params['formName']))
            {
                throw new NotSupportedException();
            }
            return $this->params['formName'];
        }

        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/list/';
        }

        private function getDefaultMassActions()
        {
            $defaultMassOptions  = array(
                                    'markRead'   => array('label' => Zurmo::t('MashableInboxModule', 'Mark selected as read'),
                                                         'isActionForAll' => false),
                                    'markUnread' => array('label' => Zurmo::t('MashableInboxModule', 'Mark selected as unread'),
                                                         'isActionForAll' => false),
                    );
            return $defaultMassOptions;
        }

        private function addModelMassOptions()
        {
            $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($this->getModelClassName());
            $this->massOptions  = array_merge($this->massOptions, $mashableUtilRules->getMassOptions());
        }

        private function getMenuItems()
        {
            $items  = array();
            $script = '';
            foreach ($this->massOptions as $massOption => $massOptionParams)
            {
                $selectedName = $this->getListViewGridId() . '-' . $massOption;
                $items[]      = array('label' => $massOptionParams['label'],
                                      'url'   => '#',
                                      'itemOptions' => array( 'id'   => $selectedName));
                $script .= $this->getScriptForOptionAction($selectedName, $massOption, $massOptionParams['isActionForAll']);
            }
            Yii::app()->clientScript->registerScript(
                                            $this->getListViewGridId() . 'ScriptForMashableInboxMassAction',
                                            $script);
            $menuItems      = array('label' => $this->getLabel(), 'url' => null,
                                    'items' => $items);
            return $menuItems;
        }

        private function getScriptForOptionAction($selectedName, $massOption, $isActionForAll)
        {
            $gridId                 = $this->getListViewGridId();
            $formName               = $this->getFormName();
            $formClassName          = $this->modelId;
            $onCompleteScript       = $this->getOnCompleteScript();
            $isActionForEachScript  = null;
            $ajaxSubmitScript       = "$.fn.yiiGridView.update('{$gridId}',
                                        {
                                            data: $('#{$formName}').serialize(),
                                            complete: {$onCompleteScript}
                                        });";
            if (!$isActionForAll)
            {
                $isActionForEachScript = $this->getScriptForAlertNoRecordSelected();
            }
            $script      = "
                $('#{$selectedName}').unbind('click.action');
                $('#{$selectedName}').bind('click.action', function()
                    {
                        {$isActionForEachScript}
                        $('#{$formClassName}_massAction').val('{$massOption}');
                        $('#{$formClassName}_selectedIds').val($('#{$gridId}-selectedIds').val());
                        {$ajaxSubmitScript};
                    }
                );
            ";
            return $script;
        }

        private function getOnCompleteScript()
        {
            $gridId = $this->getListViewGridId();
            $script = "
                    function()
                    {
                        $('#{$gridId}-selectedIds').val('');
                        " . $this->getScriptForUpdateUnreadCount() ."
                    }
                ";
            return $script;
        }

        private function getScriptForUpdateUnreadCount()
        {
            // Begin Not Coding Standard
            $script = ZurmoHtml::ajax(array(
                                        "url"       => Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/getUnreadCount'),
                                        "success"   => "function(data)
                                                        {
                                                            data  = JSON.parse(data);
                                                            total = 0;
                                                            for (var key in data) {
                                                                $('a.icon-' + key).find('span.unread-count').html(data[key]);
                                                                total += data[key];
                                                            }
                                                            $('a.icon-combined').find('span.unread-count').html(total);
                                                            $('span.unread-inbox-count').html(total);
                                                        }",
                ));
            // End Not Coding Standard
            return $script;
        }

        private function getScriptForAlertNoRecordSelected()
        {
            $gridId = $this->getListViewGridId();
            // Begin Not Coding Standard
            $script = "
                        if ($('#{$gridId}-selectedIds').val() == '')
                        {
                            alert('" . Zurmo::t('MashableInboxModule', 'You must select at least one record') . "');
                            $(this).val('');
                            return false;
                        }";
            // End Not Coding Standard
            return $script;
        }
    }
?>