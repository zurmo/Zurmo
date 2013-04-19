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
     * Helper class to render content for a list of recently viewed items.
     */
    class AuditEventsRecentlyViewedUtil
    {
        const RECENTLY_VIEWED_COUNT = 10;

        /**
         * Get the content for displaying recently viewed information via an ajax call.
         * @see RecentlyViewedView
         * @param User $user
         */
        public static function getRecentlyViewedAjaxContentByUser(User $user, $count)
        {
            assert('is_int($count)');
            $content     = null;
            $recentlyViewedData = self::getRecentlyViewedByUser($user, $count);
            if (count($recentlyViewedData) > 0)
            {
                foreach ($recentlyViewedData as $recentlyViewed)
                {
                    $moduleClassName                       = $recentlyViewed[0];
                    $modelId                               = $recentlyViewed[1];
                    $modelName                             = $recentlyViewed[2];
                    $linkHtmlOptions = array('style' => 'text-decoration:underline;');
                    $content .= ZurmoHtml::link($modelName,
                                self::getRouteByRecentlyViewed($moduleClassName, $modelId), $linkHtmlOptions);
                    $content .= '&#160;-&#160;<span style="font-size:75%">';
                    $content .= $moduleClassName::getModuleLabelByTypeAndLanguage('Singular') . '</span><br/>';
                }
            }
            else
            {
                $content .= Zurmo::t('ZurmoModule', 'There are no recently viewed items.');
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
            $itemLinkPrefix = null;
            if (!Yii::app()->userInterface->isMobile())
            {
                $itemLinkPrefix = ZurmoHtml::tag('span', array(), '') . ZurmoHtml::tag('em', array(), '');
            }
            $recentlyViewedItems = array();
            $recentlyViewedData = self::getRecentlyViewedByUser($user, $count);
            if (count($recentlyViewedData) > 0)
            {
                foreach ($recentlyViewedData as $recentlyViewed)
                {
                    $recentlyViewedItem                    = array();
                    $moduleClassName                       = $recentlyViewed[0];
                    $modelId                               = $recentlyViewed[1];
                    $modelName                             = $recentlyViewed[2];
                    $recentlyViewedItem['link']            = ZurmoHtml::link(
                                                                $itemLinkPrefix . ZurmoHtml::tag('span', array(), $modelName),
                                                                self::getRouteByRecentlyViewed($moduleClassName, $modelId));
                    $recentlyViewedItem['moduleClassName'] = $moduleClassName;
                    $recentlyViewedItems[]                 = $recentlyViewedItem;
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

        /**
         * Given an user, get the recently viewed moduleClassName and modelId limited by count
         * @param User $user
         * @param integer $count
         * @return array($moduleClassName, $modelId)
         */
        protected static function getRecentlyViewedByUser(User $user, $count)
        {
            assert('is_int($count)');
            $recentlyViewed = unserialize(ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'recentlyViewed'));
            if (!is_array($recentlyViewed))
            {
                return array();
            }
            return array_slice($recentlyViewed, 0, $count);
        }

        /**
         * Returns the url for the details view of a modelId on the moduleClassName
         * @param string $moduleClassName
         * @param integer $modelId
         * @return string
         */
        protected static function getRouteByRecentlyViewed($moduleClassName, $modelId)
        {
            assert('is_string($moduleClassName)');
            assert('$modelId > 0');
            return Yii::app()->createUrl($moduleClassName::getDirectoryName() . '/default/details/',
                                         array('id' => $modelId));
        }

        public static function resolveNewRecentlyViewedModel($moduleName, RedBeanModel $model, $count)
        {
            assert('strlen($moduleName) > 0 && is_int($model->id)');
            $newItem        = array($moduleName, $model->id, strval($model));
            $recentlyViewed = unserialize(ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            if (!is_array($recentlyViewed))
            {
                $recentlyViewed = array();
            }
            if (in_array($newItem, $recentlyViewed))
            {
                $key = array_search($newItem, $recentlyViewed);
                unset($recentlyViewed[$key]);
                array_keys($recentlyViewed);
            }
            if (array_unshift($recentlyViewed, $newItem) > $count)
            {
                array_pop($recentlyViewed);
            }
            ZurmoConfigurationUtil::
                    setForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed', serialize($recentlyViewed));
        }

        public static function deleteModelFromRecentlyViewed($moduleName, RedBeanModel $model)
        {
            if (!isset($model) || !isset($moduleName))
            {
                return;
            }
            $newItem        = array($moduleName, $model->id, strval($model));
            $recentlyViewed = unserialize(ZurmoConfigurationUtil::
                                    getForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed'));
            if (!is_array($recentlyViewed))
            {
                return;
            }
            if (in_array($newItem, $recentlyViewed))
            {
                $key = array_search($newItem, $recentlyViewed);
                unset($recentlyViewed[$key]);
                array_keys($recentlyViewed);
            }
            ZurmoConfigurationUtil::
                    setForCurrentUserByModuleName('ZurmoModule', 'recentlyViewed', serialize($recentlyViewed));
        }
    }
?>