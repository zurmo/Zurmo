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
            //Future meetings
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
            //Past meetings
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $meeting        = new Meeting();
                $opportunity    = $demoDataHelper->getRandomByModelName('Opportunity');
                $meeting->owner = $opportunity->owner;
                $meeting->activityItems->add($opportunity);
                $meeting->activityItems->add($opportunity->contacts[0]);
                $meeting->activityItems->add($opportunity->account);
                $this->populateModel($meeting, false);
                $saved = $meeting->save();
                assert('$saved');
                $meetings[] = $meeting->id;
            }
            $demoDataHelper->setRangeByModelName('Meeting', $meetings[0], $meetings[count($meetings)-1]);
        }

        public function populateModel(& $model, $setInFuture = true)
        {
            assert('$model instanceof Meeting');
            parent::populateModel($model);

            $meetingRandomData = ZurmoRandomDataUtil::
                                 getRandomDataByModuleAndModelClassNames('MeetingsModule', 'Meeting');
            $name              = RandomDataUtil::getRandomValueFromArray($meetingRandomData['names']);
            $category          = RandomDataUtil::getRandomValueFromArray(
                                 static::getCustomFieldDataByName('MeetingCategories'));
            $location          = RandomDataUtil::getRandomValueFromArray($meetingRandomData['locations']);
            if ($setInFuture)
            {
                $startTimeStamp    = time() + (mt_rand(1, 60) * 60 * 60 * 24);
                $startDateTime     = DateTimeUtil::convertTimestampToDbFormatDateTime($startTimeStamp);
                $endDateTime       = DateTimeUtil::convertTimestampToDbFormatDateTime($startTimeStamp + (mt_rand(1, 24) * 15));
            }
            else
            {
                $startTimeStamp    = time() - (mt_rand(1, 30) * 60 * 60 * 24);
                $startDateTime     = DateTimeUtil::convertTimestampToDbFormatDateTime($startTimeStamp);
                $endDateTime       = DateTimeUtil::convertTimestampToDbFormatDateTime($startTimeStamp + (mt_rand(1, 24) * 15));
            }

            $model->name             =      $name;
            $model->category->value  =      $category;
            $model->location         =      $location;
            $model->startDateTime    =      $startDateTime;
            $model->endDateTime      =      $endDateTime;
        }
    }
?>
