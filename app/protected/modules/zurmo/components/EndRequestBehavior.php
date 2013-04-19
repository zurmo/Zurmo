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
            $owner->attachEventHandler('onEndRequest', array($this, 'handleEndLogRouteEvents'));
            $owner->attachEventHandler('onEndRequest', array($this, 'handleResolveRedBeanQueriesToFile'));
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

        public function handleEndLogRouteEvents($event)
        {
            $allEventHandlers = Yii::app()->getEventHandlers('onEndRequest');

            if (count($allEventHandlers))
            {
                foreach ($allEventHandlers as $eventHandler)
                {
                    if ($eventHandler[0] instanceof CLogRouter && $eventHandler[1] == 'processLogs')
                    {
                        Yii::app()->log->processLogs($event);
                    }
                }
            }
        }

        public function handleResolveRedBeanQueriesToFile($event)
        {
            if (defined('REDBEAN_DEBUG_TO_FILE') && REDBEAN_DEBUG_TO_FILE)
            {
                if (isset(Yii::app()->queryFileLogger))
                {
                    Yii::app()->queryFileLogger->processLogs();
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
            if (Yii::app()->user->userModel != null && Yii::app()->gameHelper instanceof GameHelper)
            {
                Yii::app()->gameHelper->processDeferredPoints();
                Yii::app()->gameHelper->resolveNewBadges();
                Yii::app()->gameHelper->resolveLevelChange();
            }
        }
    }
?>
