<?php
    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class ZurmoDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $values = array('Mr', 'Mrs', 'Ms', 'Dr');
            static::makeCustomFieldDataByValuesAndDefault('Titles', $values);
        }
    }
?>