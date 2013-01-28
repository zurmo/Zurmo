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
     *
     * Enter description here ...
     * @author Jason
     *
     */
    class SaveAndPostNoteToProfileButtonActionElement extends SubmitButtonActionElement
    {
        public function getActionType()
        {
            return 'Edit';
        }

        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            if (!isset($params['htmlOptions']))
            {
                $params['htmlOptions'] = array();
            }
            $params['htmlOptions'] = array_merge(array('id'    => 'save',
                                                       'name'  => 'save',
                                                       'class' => 'attachLoading'), $params['htmlOptions']);
            parent::__construct($controllerId, $moduleId, $modelId, $params);
        }

        public function render()
        {
            if (!RightsUtil::canUserAccessModule('SocialItemsModule', Yii::app()->user->userModel))
            {
                return parent::render();
            }
            $postToProfileContent  = ZurmoHtml::tag('span', array(), Zurmo::t('NotesModule', 'Post to Profile'));
            $postToProfileContent .= static::renderHelpSpan();
            $postToProfileContent .= ZurmoHtml::checkBox('postToProfile', false);
            $content               = parent::render();
            $content              .= ZurmoHtml::tag('div', array('class' => 'post-to-profile clearfix'), $postToProfileContent);
            return $content;
        }

        protected static function renderHelpSpan()
        {
            $title       = Zurmo::t('NotesModule', 'Post this note to your profile to share with your colleagues');
            $content    = '<span id="post-to-profile-note-tooltip" class="tooltip" title="' . $title . '">?</span>';
            $qtip = new ZurmoTip();
            $qtip->addQTip("#post-to-profile-note-tooltip");
            return $content;
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('NotesModule', 'Save');
        }

        protected function getDefaultRoute()
        {
        }
    }
?>