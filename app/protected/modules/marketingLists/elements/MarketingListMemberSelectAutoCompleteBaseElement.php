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

    abstract class MarketingListMemberSelectAutoCompleteBaseElement extends AutoCompleteTextElement
    {
        const DISABLE_TEXT_BOX_WHEN_AJAX_IN_PROGRESS        = true;

        const DISABLE_RADIO_BUTTON_WHEN_AJAX_IN_PROGRESS    = true;

        const NOTIFICATION_BAR_ID                           = 'FlashMessageBar';

        public $editableTemplate = '<td colspan="{colspan}">{content}</td>';

        abstract protected function getSelectType();

        protected function getSubscribeUrl()
        {
            return Yii::app()->createUrl('/' . Yii::app()->getController()->getModule()->getId() . '/' .
                    'defaultPortlet/subscribeContacts/');
        }

        protected function getWidgetValue()
        {
            return null;
        }

        protected function getHtmlOptions()
        {
            return CMap::mergeArray(parent::getHtmlOptions(), array('onfocus' => '$(this).val("");'));
        }

        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            $this->registerScripts();
            return parent::renderControlEditable();
        }

        protected function getOptions()
        {
            return array(
                'autoFill'  => false,
                'select'    => $this->getWidgetSelectActionJS(),
                'search'    => 'js:function(event, ui) { $(this).makeOrRemoveTogglableSpinner(true,  $(this).parent()) }',
                'open'      => 'js:function(event, ui) { $(this).makeOrRemoveTogglableSpinner(false, $(this).parent()) }',
                'close'     => 'js:function(event, ui) { $(this).makeOrRemoveTogglableSpinner(false, $(this).parent()) }',
                'response'  => 'js:function(event, ui)
                    {
                        if (ui.content.length < 1)
                        {
                            $(this).makeOrRemoveTogglableSpinner(false, $(this).parent());
                        }
                    }'
            );
        }

        protected function getWidgetSelectActionJS()
        {
            // Begin Not Coding Standard
            return 'js: function(event, ui)
                    {
                        subscribeContactsToMarketingList' . $this->getSelectType() . ' ($(this), event, ui, 1, 0, 0)
                    }';
            // End Not Coding Standard
        }

        protected function registerScripts()
        {
            $this->registerSubscribeContactsAjaxScript();
            $scriptName = $this->getListViewGridId() . '-updateFlashBar';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript($scriptName, '
                    function updateFlashBar(data, flashBarId)
                    {
                        $("#" + flashBarId).jnotifyAddMessage(
                        {
                            text: data.message,
                            permanent: true,
                            showIcon: true,
                            type: data.type,
                            removeExisting: true
                        });
                    }
                ');
            }
        }

        protected function registerSubscribeContactsAjaxScript()
        {
            // Begin Not Coding Standard
            $script = 'function subscribeContactsToMarketingList' . $this->getSelectType() . ' (object, event, ui, page, subscribedCount, skippedCount)
            {
                var searchBox           = object;
                var listGridViewId      = "' . $this->getListViewGridId() .'";
                var notificationBarId   = "' . static::NOTIFICATION_BAR_ID . '";
                var radioButtonClass    = "' . $this->getRadioButtonClass() . '";
                var url                 = "' . $this->getSubscribeUrl() . '";
                var modelId             = "' . $this->getModelId() . '";
                var selectType          = "' . $this->getSelectType() . '";
                var disableTextBox      = "' . static::DISABLE_TEXT_BOX_WHEN_AJAX_IN_PROGRESS . '";
                var disableRadioButton  = "' . static::DISABLE_RADIO_BUTTON_WHEN_AJAX_IN_PROGRESS . '";
                var event               = event;
                var ui                  = ui;
                var page                = page;
                var subscribedCount     = subscribedCount;
                var skippedCount        = skippedCount;
                $.ajax(
                    {
                        url:        url,
                        dataType:   "json",
                        data:
                        {
                            marketingListId: modelId,
                            id: ui.item.id,
                            type: selectType,
                            page: page,
                            subscribedCount: subscribedCount,
                            skippedCount: skippedCount
                        },
                        beforeSend: function(request, settings)
                                    {
                                        $(searchBox).makeSmallLoadingSpinner(listGridViewId);
                                        $("#" + listGridViewId).addClass("loading");
                                        if (disableTextBox == true)
                                        {
                                            $(searchBox).attr("disabled", "disabled");
                                        }
                                        if (disableRadioButton == true)
                                        {
                                            $("." + radioButtonClass).attr("disabled", "disabled");
                                        }
                                    },
                        success:    function(data, status, request)
                                    {
                                        $("#" + listGridViewId).find(".pager").find(".refresh").find("a").click();
                                        updateFlashBar(data, notificationBarId);
                                        //todo: this is only a check if reporting.
                                        if (data.nextPage)
                                        {
                                            subscribeContactsToMarketingList' . $this->getSelectType() . '
                                                (object, event, ui, data.nextPage, data.subscribedCount, data.skippedCount);
                                        }
                                    },
                        error:      function(request, status, error)
                                    {
                                        var data = {' . // Not Coding Standard
                                                    '   "message" : "' .
                                                            Zurmo::t('MarketingListsModule',
                                                                'There was an error processing your request'). '",
                                                        "type"    : "error"
                                                    };
                                        updateFlashBar(data, notificationBarId);
                                    },
                        complete:   function(request, status)
                                    {
                                        $(searchBox).removeAttr("disabled");
                                        $(searchBox).val("");
                                        $("." + radioButtonClass).removeAttr("disabled");
                                        $("#" + listGridViewId).removeClass("loading");
                                        event.preventDefault();
                                        return false;
                                    }
                    }
                );
            }';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('SubscribeContactsToMarketingListAjaxScript' . $this->getSelectType(), $script);
        }

        protected function getModelId()
        {
            $marketingListId = ArrayUtil::getArrayValue($this->params, 'marketingListId');
            if (!isset($marketingListId))
            {
                if (!isset($this->model))
                {
                    throw new NotSupportedException();
                }
                else
                {
                    $marketingListId = $this->model->id;
                }
            }
            return $marketingListId;
        }

        protected function getRadioButtonClass()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'radioButtonClass');
        }
    }
?>