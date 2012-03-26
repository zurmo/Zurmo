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
     * Element for displaying a roll up filter for a latest activity view.
     */
    class LatestActivitiesRollUpFilterRadioElement extends Element
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
            return Yii::t('Default', 'Roll up') . ':' . $content . ' ' . self::renderHelpContent();
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
                        '' => Yii::t('Default', 'Off'),
                        1  => Yii::t('Default', 'On')
                    );

            return $data;
        }

        protected static function renderHelpContent()
        {
            $title       = Yii::t('Default', 'Turn roll up on to see activities from related records.');
            $content     = '<span id="activities-rollup-tooltip" class="tooltip" title="' . $title . '">';
            $content    .= '?</span>';
            Yii::import('application.extensions.qtip.QTip');
            $qtip = new QTip();
            $qtip->addQTip("#activities-rollup-tooltip");
            return $content;
        }
    }
?>