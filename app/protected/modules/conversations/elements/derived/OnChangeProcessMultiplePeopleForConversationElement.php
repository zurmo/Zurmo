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
     * Extended to support the element in the conversation detailview user interface.  On each change, an ajax
     * call is fired to update the participants and permission information on the conversation.
     *
     */
    class OnChangeProcessMultiplePeopleForConversationElement extends MultiplePeopleForConversationElement
    {
        protected function getOnAddContent()
        {
            return 'function(item){ ' . $this->renderOnAddOrDeleteAjaxScript() . '}';
        }

        protected function getOnDeleteContent()
        {
            return 'function(item){ ' . $this->renderOnAddOrDeleteAjaxScript() . '}';
        }

        protected static function getNotificationBarId()
        {
            return 'FlashMessageBar';
        }

        /**
         * On success, if the current user has removed themselves, then redirect to the listview.
         */
        protected function renderOnAddOrDeleteAjaxScript()
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $this->params['formName'] . '").serialize()',
                    'url'  =>  $this->getUpdateParticipantsUrl(),
                    'success' => "function(data, textStatus, jqXHR){
                            if (data == 'redirectToList')
                            {
                                window.location = '" . $this->getParticipatingInListUrl() . "';
                            }
                            else
                            {
                                $('#" . self::getNotificationBarId() . "').jnotifyAddMessage(
                                {
                                    text: '" . Yii::t('Default', 'Participants updated successfully') . "',
                                    permanent: false,
                                    showIcon: true,
                                    type: data.type
                                });
                            }
                        }"
                ));
            // End Not Coding Standard
        }

        protected function getUpdateParticipantsUrl()
        {
            return Yii::app()->createUrl('conversations/default/updateParticipants', array('id' => $this->model->id));
        }

        protected function getParticipatingInListUrl()
        {
            return Yii::app()->createUrl('conversations/default/list', array('type' => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT));
        }
    }
?>