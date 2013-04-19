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
     * Base class for managing workflow components.  Time Trigger, triggers and actions all extend this class
     */
    abstract class ComponentForWorkflowForm extends ConfigurableMetadataModel implements RowKeyInterface
    {
        /**
         * Divider used for displaying labels that cross relations. An example is Account's >> Opportunities
         */
        const DISPLAY_LABEL_RELATION_DIVIDER     = '>>';

        /**
         * Component type for time trigger
         */
        const TYPE_TIME_TRIGGER                  = 'TimeTrigger';

        /**
         * Component type for display attributes
         */
        const TYPE_TRIGGERS                      = 'Triggers';

        /**
         * Component type for actions
         */
        const TYPE_ACTIONS                       = 'Actions';

        /**
         * Component type for email messages
         */
        const TYPE_EMAIL_MESSAGES                  = 'EmailMessages';

        /**
         * @var string
         */
        protected $moduleClassName;

        /**
         * @var string
         */
        protected $modelClassName;

        /**
         * @var array
         */
        protected $attributeAndRelationData;

        /**
         * @var string
         */
        protected $workflowType;

        /**
         * @var string
         */
        private   $attribute;

        /**
         * @var string
         */
        private   $_attributeIndexOrDerivedType;

        /**
         * @var int
         */
        private $_rowKey;

        /**
         * Override in children class to @return the correct component type
         * @throws NotImplementedException
         */
        public static function getType()
        {
            throw new NotImplementedException();
        }

        /**
         * @return int
         */
        public function getRowKey()
        {
            return $this->_rowKey;
        }

        /**
         * @return array
         */
        public function attributeNames()
        {
            return array_merge(parent::attributeNames(), array('attributeIndexOrDerivedType'));
        }

        /**
         * Special override to handle setting attributeIndexOrDerivedType
         * @param string $name
         * @param mixed $value
         * @return mixed|void
         */
        public function __set($name, $value)
        {
            if ($name == 'attributeIndexOrDerivedType')
            {
                $this->_attributeIndexOrDerivedType = $value;
                $this->resolveAttributeOrRelationAndAttributeDataByIndexType($value);
            }
            else
            {
                parent::__set($name, $value);
            }
        }

        /**
         * @return string
         */
        public function getAttribute()
        {
            return $this->attribute;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array(array('attributeIndexOrDerivedType', 'safe'));
        }

        /**
         * @return array
         */
        public function attributeLabels()
        {
            return array();
        }

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @param string $workflowType
         * @param int $rowKey
         */
        public function __construct($moduleClassName, $modelClassName, $workflowType, $rowKey = 0)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            assert('is_int($rowKey)');
            $this->moduleClassName = $moduleClassName;
            $this->modelClassName  = $modelClassName;
            $this->workflowType    = $workflowType;
            $this->_rowKey         = $rowKey;
        }

        /**
         * @return string
         */
        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        /**
         * @return string
         */
        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        /**
         * @return string
         */
        public function getWorkflowType()
        {
            return $this->workflowType;
        }

        /**
         * @return string
         */
        public function getAttributeIndexOrDerivedType()
        {
            return $this->_attributeIndexOrDerivedType;
        }

        /**
         * If the attribute is on a relation then attributeAndRelationData should be populated otherwise it will
         * return the $this->attribute
         * @return array|string
         */
        public function getAttributeAndRelationData()
        {
            if ($this->attributeAndRelationData == null)
            {
                return $this->attribute;
            }
            return $this->attributeAndRelationData;
        }

        /**
         * An attribute on a relation such as from an Account, opportunities name would return true. whereas just
         * from an Account, name would return false.
         * @return bool
         */
        public function hasRelatedData()
        {
            if ($this->attribute != null)
            {
                return false;
            }
            return true;
        }

        /**
         * Resolves the attribute name for the relation.  Both account name and account's opportunities name would
         * resolve as just 'name'
         * @return mixed|string
         */
        public function getResolvedAttribute()
        {
            if ($this->attribute != null)
            {
                return $this->attribute;
            }
            return $this->resolveAttributeFromData($this->attributeAndRelationData);
        }

        /**
         * In the case of account's opportunities name, the returned ModuleClassName would be OpportunitiesModule
         * @return string
         */
        public function getResolvedAttributeModuleClassName()
        {
            if ($this->attribute != null)
            {
                return $this->moduleClassName;
            }
            return $this->resolveAttributeModuleClassNameFromData($this->attributeAndRelationData,
                                                                  $this->moduleClassName, $this->modelClassName);
        }

        /**
         * In the case of account's opportunities name, the returned ModelClassName would be Opportunity
         * @return string
         */
        public function getResolvedAttributeModelClassName()
        {
            if ($this->attribute != null)
            {
                return $this->modelClassName;
            }
            return $this->resolveAttributeModelClassNameFromData($this->attributeAndRelationData, $this->moduleClassName, $this->modelClassName);
        }

        /**
         * An example where the attribute is not the real attribute would be a trigger owner__User
         * In this case the real attribute returned would be 'owner'
         * @return string
         */
        public function getResolvedAttributeRealAttributeName()
        {
            return ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName($this->getResolvedAttribute());
        }

        /**
         * @return string real attribute name. For example owner__User would resolve as owner
         */
        public function getResolvedRealAttributeNameForFirstRelation()
        {
            return ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName($this->attributeAndRelationData[0]);
        }

        /**
         * @return string real attribute name. For example owner__User would resolve as owner
         */
        public function getResolvedRealAttributeNameForPenultimateRelation()
        {
            return $this->resolveRealAttributeNameForPenultimateRelation($this->attributeAndRelationData);
        }

        /**
         * An example of coming from Account -> opportunities name, the penultimate model would be Account
         * @return mixed
         * @throws NotSupportedException
         */
        public function getPenultimateModelClassName()
        {
            if ($this->attribute != null)
            {
                throw new NotSupportedException();
            }
            return $this->resolvePenultimateModelClassNameFromData($this->attributeAndRelationData, $this->modelClassName);
        }

        /**
         * An example of coming from Account -> opportunities name, the penultimate relation would be opportunities
         * @return mixed
         * @throws NotSupportedException
         */
        public function getPenultimateRelation()
        {
            if ($this->attribute != null)
            {
                throw new NotSupportedException();
            }
            return $this->resolvePenultimateRelationFromData($this->attributeAndRelationData);
        }

        /**
         * Builds the display label based on either the attribute or attributeAndRelationData and returns the string
         * content.
         * @return string.
         */
        public function getDisplayLabel()
        {
            $modelClassName       = $this->modelClassName;
            $moduleClassName      = $this->moduleClassName;
            if ($this->attribute != null)
            {
                $modelToWorkflowAdapter = ModelRelationsAndAttributesToWorkflowAdapter::
                                        make($moduleClassName, $modelClassName, $this->workflowType);
                return $modelToWorkflowAdapter->getAttributeLabel($this->attribute);
            }
            else
            {
                $content = null;
                foreach ($this->attributeAndRelationData as $relationOrAttribute)
                {
                    if ($content != null)
                    {
                        $content .= ' ' . self::DISPLAY_LABEL_RELATION_DIVIDER . ' ';
                    }

                    $modelToWorkflowAdapter = ModelRelationsAndAttributesToWorkflowAdapter::
                                            make($moduleClassName, $modelClassName, $this->workflowType);
                    if ($modelToWorkflowAdapter->isUsedAsARelation($relationOrAttribute))
                    {
                        $modelClassName   = $modelToWorkflowAdapter->getRelationModelClassName($relationOrAttribute);
                        $moduleClassName  = $modelToWorkflowAdapter->getRelationModuleClassName($relationOrAttribute);
                        $typeToUse = 'Plural';
                        if ($modelToWorkflowAdapter->isRelationASingularRelation($relationOrAttribute))
                        {
                            $typeToUse = 'Singular';
                        }
                        if ($moduleClassName != $modelClassName::getModuleClassName())
                        {
                            $content         .= $moduleClassName::getModuleLabelByTypeAndLanguage($typeToUse);
                        }
                        else
                        {
                            $content         .= $modelClassName::getModelLabelByTypeAndLanguage($typeToUse);
                        }
                    }
                    else
                    {
                        $content   .= $modelToWorkflowAdapter->getAttributeLabel($relationOrAttribute);
                    }
                }
            }
            return $content;
        }

        /**
         * @return ModelRelationsAndAttributesToWorkflowAdapter based object
         */
        public function makeResolvedAttributeModelRelationsAndAttributesToWorkflowAdapter()
        {
            $moduleClassName      = $this->getResolvedAttributeModuleClassName();
            $modelClassName       = $this->getResolvedAttributeModelClassName();
            return ModelRelationsAndAttributesToWorkflowAdapter::make($moduleClassName, $modelClassName, $this->workflowType);
        }

        /**
         * Based on the attribute, what kind of display element should be utilized to render the attribute's value.
         * @return string
         * @throws NotSupportedException
         */
        public function getDisplayElementType()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $modelToWorkflowAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToWorkflowAdapter();
            return $modelToWorkflowAdapter->getDisplayElementType($this->getResolvedAttribute());
        }

        /**
         * Based on the attribute's displayElementType, is the displayElementType a currency type of display
         * @return bool
         */
        public function isATypeOfCurrencyValue()
        {
            $displayElementType = $this->getDisplayElementType();
            if ($displayElementType == 'CurrencyValue')
            {
                return true;
            }
            return false;
        }

        /**
         * Passing in attributeIndexOrDerivedType, return an array representing the attribute and relation data or
         * if there is just a single attribute, then return a string representing the attribute
         * @param string $indexType
         * @return string or array
         */
        protected function resolveAttributeOrRelationAndAttributeDataByIndexType($indexType)
        {
            $attributeOrRelationAndAttributeData    = explode(FormModelUtil::RELATION_DELIMITER, $indexType);
            if (count($attributeOrRelationAndAttributeData) == 1)
            {
                $attributeOrRelationAndAttributeData = $attributeOrRelationAndAttributeData[0];
            }
            $this->setAttributeAndRelationData($attributeOrRelationAndAttributeData);
        }

        /**
         * @param array $attributeAndRelationData
         * @return string
         */
        protected function resolveAttributeFromData(Array $attributeAndRelationData)
        {
            assert(count($attributeAndRelationData) > 0); // Not Coding Standard
            return end($attributeAndRelationData);
        }

        /**
         * @param array $attributeAndRelationData
         * @param $moduleClassName
         * @param $modelClassName
         * @return string $moduleClassName
         */
        protected function resolveAttributeModuleClassNameFromData(Array $attributeAndRelationData, $moduleClassName,
                                                                   $modelClassName)
        {
            assert(count($attributeAndRelationData) > 0); // Not Coding Standard
            foreach ($attributeAndRelationData as $relationOrAttribute)
            {
                $modelToWorkflowAdapter = ModelRelationsAndAttributesToWorkflowAdapter::
                                        make($moduleClassName, $modelClassName, $this->workflowType);
                if ($modelToWorkflowAdapter->isUsedAsARelation($relationOrAttribute))
                {
                    $moduleClassName   = $modelToWorkflowAdapter->getRelationModuleClassName($relationOrAttribute);
                    $modelClassName    = $modelToWorkflowAdapter->getRelationModelClassName($relationOrAttribute);
                }
            }
            return $moduleClassName;
        }

        /**
         * @param array $attributeAndRelationData
         * @param $moduleClassName
         * @param $modelClassName
         * @return string $modelClassName
         */
        protected function resolveAttributeModelClassNameFromData(Array $attributeAndRelationData, $moduleClassName,
                                                                  $modelClassName)
        {
            assert('count($attributeAndRelationData) > 0');
            foreach ($attributeAndRelationData as $relationOrAttribute)
            {
                $modelToWorkflowAdapter = ModelRelationsAndAttributesToWorkflowAdapter::
                                        make($moduleClassName, $modelClassName, $this->workflowType);
                if ($modelToWorkflowAdapter->isUsedAsARelation($relationOrAttribute))
                {
                    $moduleClassName   = $modelToWorkflowAdapter->getRelationModuleClassName($relationOrAttribute);
                    $modelClassName    = $modelToWorkflowAdapter->getRelationModelClassName($relationOrAttribute);
                }
            }
            return $modelClassName;
        }

        /**
         * @param array $attributeAndRelationData
         * @param $modelClassName
         * @return string $lastModelClassName
         */
        protected function resolvePenultimateModelClassNameFromData(Array $attributeAndRelationData, $modelClassName)
        {
            assert('count($attributeAndRelationData) > 0');
            array_pop($attributeAndRelationData);
            foreach ($attributeAndRelationData as $relationOrAttribute)
            {
                $lastModelClassName = $modelClassName;
                $modelToWorkflowAdapter = ModelRelationsAndAttributesToWorkflowAdapter::
                                        make($modelClassName::getModuleClassName(), $modelClassName, $this->workflowType);
                if ($modelToWorkflowAdapter->isUsedAsARelation($relationOrAttribute))
                {
                    $modelClassName     = $modelToWorkflowAdapter->getRelationModelClassName($relationOrAttribute);
                }
            }
            return $lastModelClassName;
        }

        /**
         * @param array $attributeAndRelationData
         * @return string
         */
        protected function resolvePenultimateRelationFromData(Array $attributeAndRelationData)
        {
            assert('count($attributeAndRelationData) > 0');
            array_pop($attributeAndRelationData);
            return array_pop($attributeAndRelationData);
        }

        /**
         * @param array $attributeAndRelationData
         * @return string
         */
        protected function resolveRealAttributeNameForPenultimateRelation(Array $attributeAndRelationData)
        {
            assert('count($this->attributeAndRelationData) > 0');
            array_pop($attributeAndRelationData);
            return ModelRelationsAndAttributesToWorkflowAdapter::resolveRealAttributeName(end($attributeAndRelationData));
        }

        /**
         * @param $attributeOrRelationAndAttributeData
         */
        private function setAttributeAndRelationData($attributeOrRelationAndAttributeData)
        {
            assert('is_string($attributeOrRelationAndAttributeData) || is_array($attributeOrRelationAndAttributeData)');
            if (!is_array($attributeOrRelationAndAttributeData))
            {
                $this->attribute                = $attributeOrRelationAndAttributeData;
                $this->attributeAndRelationData = null;
            }
            else
            {
                $this->attribute                = null;
                $this->attributeAndRelationData = $attributeOrRelationAndAttributeData;
            }
        }
    }
?>