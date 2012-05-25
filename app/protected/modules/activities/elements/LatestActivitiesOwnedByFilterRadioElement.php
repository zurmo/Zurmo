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
     * Element for displaying a owned by filter for a latest activity view.  You can either
     * view all or 'owned' activities.
     */
    class LatestActivitiesOwnedByFilterRadioElement extends Element
    {
        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('$this->model instanceof LatestActivitiesConfigurationForm');
            $content = $this->form->radioButtonList(
                $this->model,
                $this->attribute,
                $this->getArray(),
                $this->getEditableHtmlOptions()
            );
            return Yii::t('Default', 'View') . ':' . $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        /**
         * Override to ensure label is pointing to the right input id
         * @return A string containing the element's label
         */
        protected function renderLabel()
        {
            if ($this->form === null)
            {
                throw new NotImplementedException();
            }
            $for = CHtml::ID_PREFIX . $this->getEditableInputId();
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $for));
        }

        public function getEditableHtmlOptions()
        {
            $htmlOptions = array(
                'name'      => $this->getEditableInputName(),
                'id'        => $this->getEditableInputId(),
                'separator' => '',
                'template'  => '{input}{label}',
            );
            return $htmlOptions;
        }

        protected function getArray()
        {
            $data = array(
                        LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER => Yii::t('Default', 'Mine'),
                        LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL  => Yii::t('Default', 'Everyone')
                    );

            return $data;
        }
    }
?>