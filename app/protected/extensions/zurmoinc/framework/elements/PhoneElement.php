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
     * Override to accomodate numbers coming from the database. If the value is null for the phone, the value
     * will show as 0. This is only an issue if you remove an existing phone value from an attribute.
     * @see http://groups.google.com/group/redbeanorm/browse_thread/thread/e6a0a9d29838d973/90d12a0146544a0b
     */
    class PhoneElement extends TextElement
    {
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute}) || is_string($this->model->{$this->attribute}) ||
            is_integer($this->model->{$this->attribute})');
            $htmlOptions             = array();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            if($this->model->{$this->attribute} == 0)
            {
                $htmlOptions['value'] = '';
            }
            return $this->form->textField($this->model, $this->attribute, $htmlOptions);
        }

        protected function renderControlNonEditable()
        {
            if($this->model->{$this->attribute} == 0)
            {
                return null;
            }
            return parent::renderControlNonEditable();
        }
    }
?>
