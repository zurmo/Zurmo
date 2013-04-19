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
     * A job for removing old job logs.
     */
    class JobLogCleanupJob extends BaseJob
    {
        protected static $pageSize = 1000;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('JobsManagerModule', 'Cleanup old job logs Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'JobLogCleanup';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('JobsManagerModule', 'Once a week, early in the morning.');
        }

        /**
         * Return all job logs where the modifiedDateTime was more than 1 week ago.
         * Then delete these logs.
         *
         * @see BaseJob::run()
         */
        public function run()
        {
            $oneWeekAgoTimeStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 60 * 60 *24 * 7);
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'endDateTime',
                    'operatorType'         => 'lessThan',
                    'value'                => $oneWeekAgoTimeStamp,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('JobLog');
            $where = RedBeanModelDataProvider::makeWhere('JobLog', $searchAttributeData, $joinTablesAdapter);
            $jobLogModels = JobLog::getSubset($joinTablesAdapter, null, self::$pageSize, $where, null);
            foreach ($jobLogModels as $jobLog)
            {
                $jobLog->delete();
            }
            return true;
        }
    }
?>