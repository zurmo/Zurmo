<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Class that builds demo meetings.
     */
    class MeetingsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 3;

        public static function getDependencies()
        {
            return array('opportunities');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');
            assert('$demoDataHelper->isSetRange("Opportunity")');

            $meetings = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $meeting        = new Meeting();
                $opportunity    = $demoDataHelper->getRandomByModelName('Opportunity');
                $meeting->owner = $opportunity->owner;
                $meeting->activityItems->add($opportunity);
                $meeting->activityItems->add($opportunity->contacts[0]);
                $meeting->activityItems->add($opportunity->account);
                $this->populateModel($meeting);
                $saved = $meeting->save();
                assert('$saved');
                $meetings[] = $meeting->id;
            }
            $demoDataHelper->setRangeByModelName('Meeting', $meetings[0], $meetings[count($meetings)-1]);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Meeting');
            parent::populateModel($model);

            $meetingRandomData = ZurmoRandomDataUtil::
                                 getRandomDataByModuleAndModelClassNames('MeetingsModule', 'Meeting');
            $name              = RandomDataUtil::getRandomValueFromArray($meetingRandomData['names']);
            $category          = RandomDataUtil::getRandomValueFromArray(
                                 static::getCustomFieldDataByName('MeetingCategories'));
            $location          = RandomDataUtil::getRandomValueFromArray($meetingRandomData['locations']);
            $startTimeStamp    = time() + (mt_rand(1, 200) * 60 * 60 * 24);
            $startDateTime     = DateTimeUtil::convertTimestampToDbFormatDateTime($startTimeStamp);
            $endDateTime       = DateTimeUtil::convertTimestampToDbFormatDateTime($startTimeStamp + (mt_rand(1, 24) * 15));

            $model->name             =      $name;
            $model->category->value  =      $category;
            $model->location         =      $location;
            $model->startDateTime    =      $startDateTime;
            $model->endDateTime      =      $endDateTime;
        }
    }
?>
