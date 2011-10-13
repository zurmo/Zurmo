<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Display the fullName of a person
     */
    class TitleFullNameElement extends Element implements DerivedElementInterface
    {
        protected function renderControlEditable()
        {
            $content = null;
            $editableTemplate = '{label}<br/>{content}{error}<br/>';
            foreach ($this->getElementViewMetadata() as $elementInformation)
            {
                $elementclassname      = $elementInformation['type'] . 'Element';
                $params                = array_slice($elementInformation, 2);
                $params['inputPrefix'] = $this->resolveInputPrefix();
                $element               = new $elementclassname($this->model,
                                                               $elementInformation['attributeName'],
                                                               $this->form,
                                                               $params);
                $element->editableTemplate = $editableTemplate;
                $content .= $element->render();
            }
            return $content;
        }

        protected function renderError()
        {
        }

        /**
         * Render the full name as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "null"');
            assert('$this->model instanceof Person || $this->model instanceof User');
            $title = null;
            if($this->model->title != null && $this->model->title->value != null)
            {
                $title  = Yii::app()->format->text($this->model->title);
                $title .= ' ';
            }
            return Yii::app()->format->text($title . $this->model);
        }

        protected function renderLabel()
        {
            return Yii::app()->format->text(Yii::t('Default', 'Name'));
        }

        public static function getDisplayName()
        {
            return Yii::t('Default', 'Title/First/LastName');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'title',
                'firstName',
                'lastName',
            );
        }

        protected function getElementViewMetadata()
        {
            return array(
                array('attributeName' => 'title', 'type' => 'DropDown', 'addBlank' => true),
                array('attributeName' => 'firstName', 'type' => 'Text'),
                array('attributeName' => 'lastName', 'type' => 'Text'),
            );
        }
    }
?>