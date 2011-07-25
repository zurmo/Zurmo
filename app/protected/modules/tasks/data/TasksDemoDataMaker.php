<?php
    /**
     * Class that builds demo tasks.
     */
    class TasksDemoDataMaker extends DemoDataMaker
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
                $task           = new Task();
                $opportunity    = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName["Opportunity"]);
                $task->owner    = $opportunity->owner;
                $task->activityItems->add($opportunity);
                $task->activityItems->add($opportunity->contacts[0]);
                $task->activityItems->add($opportunity->account);
                $this->populateModel($task);
                $saved = $task->save();
                assert('$saved');
                $demoDataByModelClassName['Task'][] = $task;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Task');
            parent::populateModel($model);
            $taskRandomData    = ZurmoRandomDataUtil::
                                 getRandomDataByModuleAndModelClassNames('TasksModule', 'Task');
            $name              = RandomDataUtil::getRandomValueFromArray($taskRandomData['names']);
            if(RandomDataUtil::getRandomBooleanValue())
            {
                $dueTimeStamp             = time() - (mt_rand(1,50) * 60 * 60 * 24);
                $completedDateTime        = DateTimeUtil::convertTimestampToDbFormatDateTime(
                                            $dueTimeStamp + (mt_rand(1, 24) * 15));
                $model->completedDateTime = $completedDateTime;
                $model->completed         = true;
            }
            else
            {
                $dueTimeStamp    = time() + (mt_rand(1,200) * 60 * 60 * 24);
            }
            $dueDateTime        = DateTimeUtil::convertTimestampToDbFormatDateTime($dueTimeStamp);
            $model->name        = $name;
            $model->dueDateTime = $dueDateTime;
        }
    }
?>
