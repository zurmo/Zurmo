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
     * Build a data set showing meetings on a calendar for a specific date.
     */
    class MeetingsCalendarDataProvider extends CalendarDataProvider
    {
        /**
         * Runs a query to get all the dates for meetings based on SearchAttributeData.  Then the data is processed
         * and @returns a data array of dates and quantity.  Quantity stands for how many meetings in a given date.
         * (non-PHPdoc)
         * @see CalendarDataProvider::getData()
         */
        public function getData()
        {
            $sql        = $this->makeSqlQuery();
            $rows       = R::getAll($sql);
            $data       = array();
            foreach ($rows as $row)
            {
                $localTimeZoneAdjustedDate = DateTimeUtil::
                                             convertDbFormattedDateTimeToLocaleFormattedDisplay($row['startdatetime'],
                                             DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                                             null);
                if(isset($data[$localTimeZoneAdjustedDate]))
                {
                    $data[$localTimeZoneAdjustedDate]['quantity'] = $data[$localTimeZoneAdjustedDate]['quantity'] + 1;
                }
                else
                {
                    $data[$localTimeZoneAdjustedDate] = array('date' => $localTimeZoneAdjustedDate, 'quantity' => 1);
                }
            }
            foreach ($data as $key => $item)
            {
                if($item['quantity'] == 1)
                {
                    $label = Yii::t('Default', '{quantity} Meeting', array('{quantity}' => $item['quantity']));
                }
                else
                {
                    $label = Yii::t('Default', '{quantity} Meetings', array('{quantity}' => $item['quantity']));
                }
                $data[$key]['label']     = $label;
                $data[$key]['className'] = 'calendar-events-' . $item['quantity'];
            }
            return $data;
        }

        /**
         * Gets the date of all meetings as defined by the searchAttributeData
         */
        public function makeSqlQuery()
        {
            assert('get_class($this->model) == "Meeting"');
            $modelClassName            = 'Meeting';
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $where                     = ModelDataProviderUtil::makeWhere($modelClassName, $this->searchAttributeData,
                                                                          $joinTablesAdapter);
            $selectDistinct            = false;
            $modelClassName::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                                                          $joinTablesAdapter,
                                                                          $where,
                                                                          $selectDistinct);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $selectQueryAdapter->addClause('meeting', 'startdatetime');
            $sql                       = SQLQueryUtil::makeQuery('meeting', $selectQueryAdapter,
                                                                 $joinTablesAdapter, null, null, $where);
            return $sql;
        }
    }
?>