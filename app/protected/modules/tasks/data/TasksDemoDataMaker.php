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

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');
            assert('$demoDataHelper->isSetRange("Opportunity")');

            $tasks = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $task           = new Task();
                $opportunity    = $demoDataHelper->getRandomByModelName('Opportunity');
                $task->owner    = $opportunity->owner;
                $task->activityItems->add($opportunity);
                $task->activityItems->add($opportunity->contacts[0]);
                $task->activityItems->add($opportunity->account);
                $this->populateModel($task);
                $saved = $task->save();
                assert('$saved');
                $tasks[] = $task;
            }
            $demoDataHelper->setRangeByModelName('Task', $tasks[0]->id, $tasks[count($tasks)-1]->id);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Task');
            parent::populateModel($model);
            $taskRandomData    = ZurmoRandomDataUtil::
                                 getRandomDataByModuleAndModelClassNames('TasksModule', 'Task');
            $name              = RandomDataUtil::getRandomValueFromArray($taskRandomData['names']);
            if (RandomDataUtil::getRandomBooleanValue())
            {
                $dueTimeStamp             = time() - (mt_rand(1, 50) * 60 * 60 * 24);
                $completedDateTime        = DateTimeUtil::convertTimestampToDbFormatDateTime(
                                            $dueTimeStamp + (mt_rand(1, 24) * 15));
                $model->completedDateTime = $completedDateTime;
                $model->completed         = true;
            }
            else
            {
                $dueTimeStamp    = time() + (mt_rand(1, 200) * 60 * 60 * 24);
            }
            $dueDateTime        = DateTimeUtil::convertTimestampToDbFormatDateTime($dueTimeStamp);
            $model->name        = $name;
            $model->dueDateTime = $dueDateTime;
        }
    }
?>
