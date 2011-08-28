<?php
    /**
     * Class that builds demo notes.
     */
    class NotesDemoDataMaker extends DemoDataMaker
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
                $note           = new Note();
                $opportunity    = RandomDataUtil::getRandomValueFromArray($demoDataByModelClassName["Opportunity"]);
                $note->owner    = $opportunity->owner;
                $note->activityItems->add($opportunity);
                $note->activityItems->add($opportunity->contacts[0]);
                $note->activityItems->add($opportunity->account);
                $this->populateModel($note);
                $saved = $note->save();
                assert('$saved');
                $demoDataByModelClassName['Note'][] = $note;
            }
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Note');
            parent::populateModel($model);
            $taskRandomData            = ZurmoRandomDataUtil::
                                         getRandomDataByModuleAndModelClassNames('NotesModule', 'Note');
            $description               = RandomDataUtil::getRandomValueFromArray($taskRandomData['descriptions']);
            $occurredOnTimeStamp       = time() - (mt_rand(1, 200) * 60 * 60 * 24);
            $occurredOnDateTime        = DateTimeUtil::convertTimestampToDbFormatDateTime($occurredOnTimeStamp);
            $model->description        = $description;
            $model->occurredOnDateTime = $occurredOnDateTime;
        }
    }
?>
