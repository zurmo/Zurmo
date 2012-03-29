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
     * Base class for displaying meetings on a calendar
     */
    abstract class UpcomingMeetingsCalendarView extends CalendarView
    {
        protected function makeDayEvents()
        {
            return $this->getDataProvider()->getData();
        }

        protected function makeDataProvider($timeString = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            return new MeetingsCalendarDataProvider('Meeting', $this->makeSearchAttributeData($timeString));
        }

        protected function makeSearchAttributeData($timeString = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'greaterThan',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay(
                                              DateTimeUtil::getFirstDayOfAMonthDate($timeString))
                ),
                2 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'lessThan',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeEndOfDay(
                                              DateTimeUtil::getLastDayOfAMonthDate($timeString))
                )
                );
            $searchAttributeData['structure'] = '(1 and 2)';
            return $searchAttributeData;
        }

        public static function getModuleClassName()
        {
            return 'MeetingsModule';
        }

        protected function getOnChangeMonthScript()
        {
            return "js:function(year, month, inst) {
                //Call to render new events
                $.ajax({
                    url      : $.param.querystring('" . $this->getPortletChangeMonthUrl() . "', '&month=' + month + '&year=' + year),
                    async    : false,
                    type     : 'GET',
                    dataType : 'html',
                    success  : function(data)
                    {
                        eval(data);
                        //Since the home page for some reason cannot render this properly in beforeShow, we are using a trick.
                        setTimeout('addSpansToDatesOnCalendar(\"' + inst.id + '\")', 100);
                    },
                    error : function()
                    {
                        //todo: error call
                    }
                });
            }";
        }

        protected function getPortletChangeMonthUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/viewAction',
                                                        array_merge($_GET, array(
                                                            'action'         => 'renderMonthEvents',
                                                            'portletId'      => $this->params['portletId'],
                                                            'uniqueLayoutId' => $this->uniqueLayoutId)));
        }

        /**
         * Called by ajax action when the calendar month is changed.  Needed to render additional events.
         */
        public function renderMonthEvents()
        {
            $month     = str_pad($_GET['month'], 2, '0', STR_PAD_LEFT);
            $year      = $_GET['year'];
            $dayEvents = $this->makeDataProvider($year . '-' . $month . '-01')->getData();
            foreach($dayEvents as $event)
            {
                echo "calendarEvents[new Date('" . $event['date'] . "')] = new CalendarEvent('" . $event['label'] . "', '" . $event['className'] . "'); \n";
            }
        }
    }
?>