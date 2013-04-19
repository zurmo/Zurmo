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
     * Parent class for all LinkActionElements that may apply to all or selected records.
     */
    abstract class MassActionLinkActionElement extends DropdownSupportedLinkActionElement
    {
        const SELECTED_MENU_TYPE                = 1;

        const ALL_MENU_TYPE                     = 0;

        const DROPDOWN_ID            = 'ListViewExportActionMenu';

        protected $gridId;

        protected $selectedMenuItemName;

        protected $allMenuItemName;

        abstract protected function getActionId();

        abstract protected function getSelectedMenuNameSuffix();

        abstract protected function getAllMenuNameSuffix();

        abstract protected function getScriptNameSuffixForSelectedMenu();

        abstract protected function getScriptNameSuffixForAllMenu();

        public static function getDropDownId()
        {
            return static::DROPDOWN_ID;
        }

        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            parent::__construct($controllerId, $moduleId, $moduleId, $params);
            $this->gridId = $this->getListViewGridId();
            $this->selectedMenuItemName = $this->gridId . $this->getSelectedMenuNameSuffix();
            $this->allMenuItemName = $this->gridId . $this->getAllMenuNameSuffix();
            $this->registerUnifiedEventHandler();
        }

        public function render()
        {
            $this->registerMenuScripts();
            return $this->renderMenuWidget($this->renderMenuItem());
        }

        public function renderMenuItem()
        {
            return array('label' => $this->getMenuHeader(), 'url' => null,
                'items' => $this->getMenuItems());
        }

        public function getActionNameForCurrentElement()
        {
            return $this->getActionId();
        }

        public function getActionType()
        {
            throw new NotSupportedException();
        }

        public function registerUnifiedEventHandler()
        {
            if (Yii::app()->clientScript->isScriptRegistered('massActionLinkActionElementEventHandler'))
            {
                return;
            }
            else
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript('massActionLinkActionElementEventHandler', "
                        function massActionLinkActionElementEventHandler(elementType, gridId, baseUrl, actionId, pageVarName)
                        {
                            selectAll = '';
                            if (elementType == " . static::SELECTED_MENU_TYPE . ")
                            {
                                if ($('#' + gridId + '-selectedIds').val() == '')
                                {
                                    alert('You must select at least one record');
                                    $(this).val('');
                                    return false;
                                }
                            }
                            else
                            {
                                selectAll = 1;
                            }
                            var options =
                            {
                                url     : $.fn.yiiGridView.getUrl(gridId),
                                baseUrl : baseUrl
                            }
                            if (options.url.split( '?' ).length == 2)
                            {
                                options.url = options.baseUrl + '/' + actionId + '?' + options.url.split( '?' )[1];
                            }
                            else
                            {
                                options.url = options.baseUrl + '/' + actionId;
                            }
                            if (elementType == " . static::SELECTED_MENU_TYPE . ")
                            {
                                addListViewSelectedIdsToUrl(gridId, options);
                            }
                            var data = '' + actionId + '=' + '&selectAll=' + selectAll + '&ajax=&' + pageVarName + '=1';
                            url = $.param.querystring(options.url, data);
                            url += '" . $this->resolveAdditionalQueryStringData() ."';
                            window.location.href = url;
                            return false;
                        }
                ");
                // End Not Coding Standard
            }
        }

        public function getOptGroup()
        {
            return $this->getMenuHeader();
        }

        public function getOptions()
        {
            return $this->getMenuItems();
        }

        public function getElementValue()
        {
            return null; // because Selected and All Results would have their own and we can't determine that here.
        }

        public function registerDropDownScripts($dropDownId = null, $scriptName = null)
        {
            $dropDownId = ($dropDownId)? $dropDownId : static::getDropDownId();
            $scriptName = ($scriptName)? $scriptName : $dropDownId;
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript($scriptName, "
                        $('#" . $dropDownId . "').unbind('change.action').bind('change.action', function()
                        {
                            // TODO: @Shoaibi/@Jason: High: Heavy dependence on DOM?
                            selectedOption      = $(this).find(':selected');
                            selectedOptionId    = selectedOption.attr('id');
                            if (selectedOptionId)
                            {
                                selectedOptionValue = selectedOption.val();
                                optionType          = selectedOptionId.slice(-3);
                                actionName          = selectedOptionValue.slice(0, selectedOptionValue.indexOf('_'));
                                if (optionType == 'All')
                                {
                                    menuType = " . static::ALL_MENU_TYPE . ";
                                }
                                else
                                {
                                    menuType = " . static::SELECTED_MENU_TYPE . ";
                                }
                                $('#" . $dropDownId . "').val('');
                                massActionLinkActionElementEventHandler(" .
                                        "menuType, ".
                                        " '" . $this->gridId. "',".
                                        " '" . Yii::app()->createUrl($this->moduleId . '/' . $this->getControllerId()) . "',".
                                        " actionName,".
                                        " '" . $this->getPageVarName() ."'".
                                        ");
                            }
                        }
                        );
                    ");
                // End Not Coding Standard
            }
        }

        protected function resolveAdditionalQueryStringData()
        {
            return null;
        }

        protected function registerMenuScripts()
        {
            $this->registerScriptForAllMenu();
            $this->registerScriptForSelectedMenu();
        }

        protected function registerScriptForSelectedMenu()
        {
            $this->registerScriptForMenuType(static::SELECTED_MENU_TYPE);
        }

        protected function registerScriptForAllMenu()
        {
            $this->registerScriptForMenuType(static::ALL_MENU_TYPE);
        }

        protected function registerScriptForMenuType($menuType)
        {
            if ($menuType === static::SELECTED_MENU_TYPE)
            {
                $scriptNameSuffix       = $this->getScriptNameSuffixForSelectedMenu();
                $menuItemName           = $this->selectedMenuItemName;
            }
            else
            {
                $scriptNameSuffix       = $this->getScriptNameSuffixForAllMenu();
                $menuItemName           = $this->allMenuItemName;
            }
            Yii::app()->clientScript->registerScript($this->gridId . $scriptNameSuffix,
                            "$('#" . $menuItemName . "').unbind('click.action').bind('click.action', function()
                                {
                                    " . $this->getEventHandlerScriptContentForMenuType($menuType) ."
                                }
                            );");
        }

        protected function getEventHandlerScriptContentForMenuType($menuType)
        {
            // Begin Not Coding Standard
            return "massActionLinkActionElementEventHandler(" .
                            $menuType . ",".
                            " '" . $this->gridId. "',".
                            " '" . Yii::app()->createUrl($this->moduleId . '/' . $this->getControllerId()) . "',".
                            " '" . $this->getActionId(). "',".
                            " '" . $this->getPageVarName() ."'".
                            ")";
            // End Not Coding Standard
        }

        protected function getMenuItems()
        {
            return array(
                array('label'   => Zurmo::t('Core', 'Selected'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->selectedMenuItemName)),
                array('label'   => Zurmo::t('Core', 'All Results'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->allMenuItemName)));
        }

        protected function getMenuHeader()
        {
            return $this->getLabel();
        }

        protected function getMenuId()
        {
            return get_class($this);
        }

        protected function renderMenuWidget($items)
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                                    'htmlOptions'   => array('id' => $this->getMenuId()),
                                    'items'         => array($items),
                                    ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        protected function getListViewGridId()
        {
            // TODO: @Shoaibi/@Jason: Low: Create a common parent for Element and ActionElement, put this there.
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'listViewGridId');
        }

        protected function getPageVarName()
        {
            // TODO: @Shoaibi/@Jason: Low: Create a common parent for Element and ActionElement, put this there.
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'pageVarName');
        }

        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->getControllerId() . '/' . $this->getActionId() . '/';
        }

        protected function getDefaultLabel()
        {
            throw new NotSupportedException;
        }

        protected function getControllerId()
        {
            $controllerId = ArrayUtil::getArrayValue($this->params, 'controllerId');
            return ($controllerId)? $controllerId : $this->controllerId;
        }
    }
?>