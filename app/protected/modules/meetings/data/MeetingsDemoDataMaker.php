<?php
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

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');
            assert('isset($demoDataByModelClassName["User"])');
            assert('isset($demoDataByModelClassName["Opportunity"])');
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $meeting        = new Meeting();
                $opportunity    = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName["Opportunity"]);
                $meeting->owner = $opportunity->owner;
                $meeting->activityItems->add($opportunity);
                $meeting->activityItems->add($opportunity->contacts[0]);
                $meeting->activityItems->add($opportunity->account);
                $this->populateModel($meeting);
                $saved = $meeting->save();
                assert('$saved');
                $demoDataByModelClassName['Meeting'][] = $meeting;
            }
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
            $startTimeStamp    = time() + (mt_rand(1,200) * 60 * 60 * 24);
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
