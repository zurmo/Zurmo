<?php
    /**
     * Class to make default data that needs to be created upon an installation.
     */
    class OpportunitiesDefaultDataMaker extends DefaultDataMaker
    {
        public function make()
        {
            $values = array(
                        'Prospecting',
                        'Qualification',
                        'Negotiating',
                        'Verbal',
                        'Closed Won',
                        'Closed Lost',
            );
            static::makeCustomFieldDataByValuesAndDefault('SalesStages', $values, $values[0]);
        }
    }
?>