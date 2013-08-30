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

    /**
     * A data provider that manages the leaderboard
     */
    class LeaderboardDataProvider extends RedBeanModelDataProvider
    {
        protected $type;

        public function setType($type)
        {
            if (!in_array($type, array(GamePointUtil::LEADERBOARD_TYPE_WEEKLY,
                                       GamePointUtil::LEADERBOARD_TYPE_MONTHLY,
                                       GamePointUtil::LEADERBOARD_TYPE_OVERALL)))
            {
                throw new NotSupportedException();
            }
            $this->type = $type;
        }

        /**
         * See the yii documentation.
         */
        protected function fetchData()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $pagination->setItemCount($this->getTotalItemCount());
                $offset = $pagination->getOffset();
                $limit  = $pagination->getLimit();
            }
            else
            {
                $offset = 0;
                $limit  = null;
            }
            return $this->getUserLeaderboardData($this->type, $offset, $limit);
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         */
        public function calculateTotalItemCount()
        {
            return GamePointUtil::getUserLeaderboardCount($this->type);
        }

        /**
         * See the yii documentation.
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $row)
            {
                $keys[] = $row['userId'];
            }
            return $keys;
        }

        /**
         * @param string $type
         * @param null|int $offset
         * @param null|int $count
         * @return array
         */
        public static function getUserLeaderboardData($type, $offset = null, $count = null)
        {
            assert('is_string($type)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            $leaderboardData = GamePointUtil::getUserLeaderboardData($type, $offset + 1, $offset, $count);
            $resolvedLeaderboardData = array();
            foreach ($leaderboardData as $userId => $data)
            {
                $data['userId']            = $userId;
                $resolvedLeaderboardData[] = $data;
            }
            return $resolvedLeaderboardData;
        }
    }
?>
