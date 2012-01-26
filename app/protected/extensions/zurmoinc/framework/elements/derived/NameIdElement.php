<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Display the name and hidden id of the model.
     * Displays a select button and auto-complete input
     */
    abstract class NameIdElement extends ModelElement implements DerivedElementInterface
    {
        protected static $moduleId;

        /**
         * Model or form's attributeName for the model 'name'
         */
        protected $nameAttributeName;

        protected function renderControlEditable()
        {
            assert('$this->attribute == "null"');
            return $this->renderEditableContent();
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                throw new NotImplementedException();
            }
            $id = $this->getIdForTextField();
            return $this->form->labelEx($this->model, $this->nameAttributeName, array('for' => $id));
        }

        protected function renderError()
        {
            return $this->form->error($this->model, $this->nameAttributeName);
        }

        protected function getIdForHiddenField()
        {
            return $this->getEditableInputId($this->idAttributeId);
        }

        protected function getNameForHiddenField()
        {
            return $this->getEditableInputName($this->idAttributeId);
        }

        protected function getIdForTextField()
        {
            return $this->getEditableInputId($this->nameAttributeName);
        }

        protected function getNameForTextField()
        {
            return $this->getEditableInputName($this->nameAttributeName);
        }

        protected function getIdForSelectLink()
        {
            return $this->getEditableInputId($this->resolveModuleId(), 'SelectLink');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'name',
                'id',
            );
        }

        protected function getName()
        {
            return $this->model->{$this->nameAttributeName};
        }

        protected function getId()
        {
            return $this->model->{$this->idAttributeId};
        }

        /**
         * Override to return the model, since there are no
         * related models on the model passed into this element.
         */
        protected function getResolvedModel()
        {
            return $this->model;
        }
    }