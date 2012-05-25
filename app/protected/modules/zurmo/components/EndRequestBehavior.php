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
     * Called when Yii::app->end(0, false) is called. You should always call end
     * with the second parameter set to false. This behavior exists so that during
     * unit tests this behavior can be switched for a behavior that raises an
     * exception instead of exiting.
     */
    class EndRequestBehavior extends CBehavior
    {
        public function attach($owner)
        {
            if (Yii::app()->isApplicationInstalled())
            {
                $owner->attachEventHandler('onEndRequest', array($this, 'handleGamification'));
            }
            $owner->attachEventHandler('onEndRequest', array($this, 'handleSaveGlobalStateCheck'));
            $owner->attachEventHandler('onEndRequest', array($this, 'handleEndRequest'));
        }

        // Save global state into ZurmoConfig, before handleEndRequest event handler is called.
        // This is needed because handleEndRequest is attached to component before saveGlobalState handler
        // and therefore will be execute before, so we need to change order.
        public function handleSaveGlobalStateCheck($event)
        {
            $allEventHandlers = Yii::app()->getEventHandlers('onEndRequest');
            if (count($allEventHandlers))
            {
                foreach ($allEventHandlers as $eventHandler)
                {
                    if ($eventHandler[0] instanceof CApplication && $eventHandler[1] == 'saveGlobalState')
                    {
                        Yii::app()->saveGlobalState();
                    }
                }
            }
        }

        public function handleEndRequest($event)
        {
            exit;
        }

        /**
         * Process any points that need to be tabulated based on scoring that occurred during the request.
         * @param CEvent $event
         */
        public function handleGamification($event)
        {
            if (Yii::app()->user->userModel != null)
            {
                Yii::app()->gameHelper->processDeferredPoints();
                Yii::app()->gameHelper->resolveNewBadges();
                Yii::app()->gameHelper->resolveLevelChange();
            }
        }
    }
?>
