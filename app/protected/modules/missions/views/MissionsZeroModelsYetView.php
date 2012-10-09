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
     * Class for showing a message and create link when there are no missions visible to the logged in user when
     * going to the missions list view.
     */
    class MissionsZeroModelsYetView extends ZeroModelsYetView
    {
        protected function getCreateLinkDisplayLabel()
        {
            return Yii::t('Default',
                          'Create Mission');
        }

        protected function getMessageContent()
        {
            return Yii::t('Default', '<h2>"Make your life a mission - not an intermission."' .
                                     '</h2><i>- Arnold Glasgow</i></i><div class="large-icon"></div><p>' .
                                     'Create a Mission in the CRM for something that needs to get done. ' .
                                     'Could be getting lunch for the team at the local taco stand or completing ' .
                                     'a powerpoint presentation for tomorrow\'s meeting. Users can select from ' .
                                     'available Missions and receive rewards for completion.</p>');
        }
    }
?>
