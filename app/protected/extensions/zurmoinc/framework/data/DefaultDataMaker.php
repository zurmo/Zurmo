<?php
    /**
     * Base class for module default data that needs to be created upon installation.
     */
    abstract class DefaultDataMaker
    {
        abstract public function make();

        protected static function makeCustomFieldDataByValuesAndDefault($name, $values, $defaultValue = null)
        {
            assert('is_string($name)');
            assert('is_array($values)');
            assert('is_string($defaultValue) || $defaultValue == null');
            $fieldData = CustomFieldData::getByName($name);
            $fieldData->serializedData = serialize($values);
            $fieldData->defaultValue = $defaultValue;
            $saved = $fieldData->save();
            assert('$saved');
        }
    }
?>