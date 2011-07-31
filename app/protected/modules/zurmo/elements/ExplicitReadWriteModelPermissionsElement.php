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
     *
     */
    class ExplicitReadWriteModelPermissionsElement extends Element
    {
        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('$this->model instanceof ModelForm || $this->model instanceof ConfigurableMetadataModel');
            assert('$this->model->{$this->attribute} instanceof ExplicitReadWriteModelPermissions');
            assert('$this->model->{$this->attribute}->getReadOnlyPermitablesCount() == 0');
            assert('$this->model->{$this->attribute}->getReadWritePermitablesCount() >= 0');
            assert('$this->model->{$this->attribute}->getReadWritePermitablesCount() < 2');
            $content      = CHtml::radioButtonList($this->getEditableInputName($this->attribute, 'type'),
                                                   $this->resolveSelectedType(),
                                                   $this->resolveData(),
                                                   $this->getEditableHtmlOptions());
            return $content;
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
            return Yii::t('Default', 'Who can read and write');
        }

        public function getEditableHtmlOptions()
        {
            $htmlOptions = array(
                'id'   => $this->getEditableInputId($this->attribute, 'type'),
            );
            $htmlOptions['template'] =  '<div style="float:left">{input}</div><div style="margin-top:3px;">{label}</div>';
            return $htmlOptions;
        }

        protected function resolveData()
        {
            $selectableGroupsDropDownContent     = $this->renderSelectableGroupsContent();
            $data                                = $this->getPermissionTypes();
            $dataIndex                           = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP;
            if($selectableGroupsDropDownContent != null)
            {

                $data[$dataIndex]                = $data[$dataIndex] . '&#160;' . $selectableGroupsDropDownContent;
            }
            else
            {
                unset($data[$dataIndex]);
            }
            return $data;
        }

        protected function resolveSelectedType()
        {
            $permitables = $this->model->{$this->attribute}->getReadWritePermitables();
            if($permitables == null)
            {
                return null;
            }
            else
            {
                assert($permitables[0] instanceof Group);
                if($permitables[0]->name == Group::EVERYONE_GROUP_NAME)
                {
                    return ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
                }
                else
                {
                    return ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP;
                }
            }
        }

        protected function resolveSelectedGroup()
        {
            $permitables = $this->model->{$this->attribute}->getReadWritePermitables();
            if($permitables == null)
            {
                return null;
            }
            else
            {
                assert($permitables[0] instanceof Group);
                if($permitables[0]->name == Group::EVERYONE_GROUP_NAME)
                {
                    return null;
                }
                else
                {
                    return $permitables[0]->id;
                }
            }
        }

        protected function getPermissionTypes()
        {
            return array(
                null                                                                 => yii::t('Default', 'Owner'),
                ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP  => yii::t('Default', 'Owner and users in'),
                ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP     => yii::t('Default', 'Everyone'));
        }

        protected function renderSelectableGroupsContent()
        {
            $htmlOptions = array(
                'id'   => $this->getEditableInputId   ($this->attribute, 'nonEveryoneGroup'),
            );
            $name        = $this->getEditableInputName($this->attribute, 'nonEveryoneGroup');
            $dropDownArray = $this->getSelectableGroupsData();
            if($dropDownArray == null)
            {
                return null;
            }
            return CHtml::dropDownList($name, $this->resolveSelectedGroup(), $dropDownArray, $htmlOptions);
        }

        protected function getSelectableGroupsData()
        {
            $groups     = Group::getAll();
            $groupsData = array();
            foreach($groups as $group)
            {
                if($group->name != Group::EVERYONE_GROUP_NAME && $group->name != Group::SUPER_ADMINISTRATORS_GROUP_NAME)
                {
                    $groupsData[$group->id] = strval($group);
                }
            }
            return $groupsData;
        }
    }
?>