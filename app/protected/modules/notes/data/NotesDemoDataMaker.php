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

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');
            assert('$demoDataHelper->isSetRange("Opportunity")');

            $notes = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $note           = new Note();
                $opportunity    = $demoDataHelper->getRandomByModelName('Opportunity');
                $note->owner    = $opportunity->owner;
                $note->activityItems->add($opportunity);
                $note->activityItems->add($opportunity->contacts[0]);
                $note->activityItems->add($opportunity->account);
                $this->populateModel($note);
                $saved = $note->save();
                assert('$saved');
                $notes[] = $note;
            }
            $demoDataHelper->setRangeByModelName('Note', $notes[0]->id, $notes[count($notes)-1]->id);
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
