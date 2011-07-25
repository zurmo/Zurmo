<?php
    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class MeetingsDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $values = array(
                        'Meeting',
                        'Call',
            );
            static::makeCustomFieldDataByValuesAndDefault('MeetingCategories', $values, $values[0]);
        }
    }
?>