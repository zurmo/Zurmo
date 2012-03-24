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
     * Helper class to render content for a list of recently viewed items.
     */
    class AuditEventsRecentlyViewedUtil
    {
        /**
         * Get the content for displaying recently viewed information via an ajax call.
         * @see RecentlyViewedView
         * @param User $user
         */
        public static function getRecentlyViewedAjaxContentByUser(User $user, $count)
        {
            assert('is_int($count)');
            $content     = null;
            $auditEvents = self::getRecentlyViewedAuditEventsByUser($user, $count);
            if (count($auditEvents) > 0)
            {
                foreach ($auditEvents as $auditEvent)
                {
                    assert('is_string($auditEvent->modelClassName)');
                    assert('$auditEvent->serializedData != null');
                    $modelClassName   = $auditEvent->modelClassName;
                    $unserializedData = unserialize($auditEvent->serializedData);
                    if ($unserializedData)
                    {
                        $moduleClassName = $unserializedData[1];
                        $linkHtmlOptions = array('style' => 'text-decoration:underline;');
                        $content .= CHtml::link($unserializedData[0],
                                    self::getRouteByAuditEvent($auditEvent, $moduleClassName), $linkHtmlOptions);
                        $content .= '&#160;-&#160;<span style="font-size:75%">';
                        $content .= $moduleClassName::getModuleLabelByTypeAndLanguage('Singular') . '</span><br/>';
                    }
                }
            }
            else
            {
                $content .= Yii::t('Default', 'There are no recently viewed items.');
            }
            return $content;
        }

        /**
         * Get the recently viewed models as items which include a link and a moduleClassName.
         * @see RecentlyViewedView
         * @param User $user
         */
        public static function getRecentlyViewedItemsByUser(User $user, $count)
        {
            assert('is_int($count)');
            $recentlyViewedItems = array();
            $auditEvents = self::getRecentlyViewedAuditEventsByUser($user, $count);
            if (count($auditEvents) > 0)
            {
                foreach ($auditEvents as $auditEvent)
                {
                    assert('is_string($auditEvent->modelClassName)');
                    assert('$auditEvent->serializedData != null');
                    $modelClassName   = $auditEvent->modelClassName;
                    $unserializedData = unserialize($auditEvent->serializedData);
                    if ($unserializedData)
                    {
                        $recentlyViewedItem                    = array();
                        $moduleClassName                       = $unserializedData[1];
                        $recentlyViewedItem['link']            = CHtml::link($unserializedData[0],
                                    self::getRouteByAuditEvent($auditEvent, $moduleClassName));
                        $recentlyViewedItem['moduleClassName'] = $moduleClassName;
                        $recentlyViewedItems[]                 = $recentlyViewedItem;
                    }
                }
            }
            return $recentlyViewedItems;
        }

        /**
         * Given a user and a count, get a tail of recent audit events for that user limited by the count.
         * @param User $user
         */
        protected static function getRecentlyViewedAuditEventsByUser(User $user, $count)
        {
            assert('is_int($count)');
            return AuditEvent::getTailDistinctEventsByEventName('Item Viewed', $user, $count);
        }

        /**
         * Given an AuditEvent, build a route to the event's model's details action.
         * @param AuditEvent $auditEvent
         */
        protected static function getRouteByAuditEvent(AuditEvent $auditEvent, $moduleClassName)
        {
            assert('is_string($moduleClassName)');
            return Yii::app()->createUrl($moduleClassName::getDirectoryName() . '/default/details/',
                                         array('id' => $auditEvent->modelId));
        }
    }
?>