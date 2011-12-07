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
     * Element to expose explicit permission setting on a model in a simple way in the user interface. While this
     * does not have a full user interface offering of the available permission setting mechanisms, this element
     * provides a way for a user to quickly and easily add a group to a model in a view.  In the future this element
     * will support adding ad-hoc groups and users to a model.  Any selection made using this element assumes
     * that the explicit action is both read and write.
     * @see ExplicitReadWriteModelPermissions
     * @see ExplicitReadWriteModelPermissionsUtil
     */
    class ExplicitReadWriteModelPermissionsElement extends Element
    {
        protected function getExplicitReadWriteModelPermissions()
        {
            return $this->model->{$this->attribute};
        }

        protected function getAttributeName()
        {
            return $this->attribute;
        }

        /**
         * Renders the setting as a radio list.  The second radio option also has a dropdown of available groups
         * as part of the label.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $this->assertModelIsValid();
            list($data, $dataSelectOption)  = $this->resolveData();
            $content                        = ZurmoHtml::radioButtonList(
                                                        $this->getEditableInputName($this->getAttributeName(), 'type'),
                                                        $this->resolveSelectedType(),
                                                        $data,
                                                        $this->getEditableHtmlOptions(),
                                                        $dataSelectOption);
            return $content;
        }

        protected function assertModelIsValid()
        {
            assert('$this->model instanceof ModelForm || $this->model instanceof ConfigurableMetadataModel ||
                    $this->model instanceof SecurableItem');
            assert('$this->getExplicitReadWriteModelPermissions() instanceof ExplicitReadWriteModelPermissions');
            assert('$this->getExplicitReadWriteModelPermissions()->getReadOnlyPermitablesCount() == 0');
            assert('$this->getExplicitReadWriteModelPermissions()->getReadWritePermitablesCount() >= 0');
            assert('$this->getExplicitReadWriteModelPermissions()->getReadWritePermitablesCount() < 2');
        }

        protected function renderControlNonEditable()
        {
            $selectedType = $this->resolveSelectedType();

            $permissionTypes = $this->getPermissionTypes();
            if ($selectedType == ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP)
            {
                $selectedGroups = $this->getSelectableGroupsData();
                $stringContent  = ArrayUtil::getArrayValue($permissionTypes, $selectedType);
                $stringContent .= '&#160;';
                $stringContent  = ArrayUtil::getArrayValue($selectedGroups, $this->resolveSelectedGroup());
            }
            else
            {
                $stringContent = ArrayUtil::getArrayValue($permissionTypes, $selectedType);
            }
            return Yii::app()->format->text($stringContent);
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
            return CHtml::label(Yii::t('Default', 'Who can read and write'), false);
        }

        /**
         * This type of element does not support ActiveForm errors
         * @return error content
         */
        protected function renderError()
        {
            return null;
        }

        public function getEditableHtmlOptions()
        {
            $htmlOptions = array(
                'id'   => $this->getEditableInputId($this->getAttributeName(), 'type'),
            );
            $htmlOptions['template'] =  '<div class="radio-input">{input}{label}</div>';
            return $htmlOptions;
        }

        /**
         * @return array of options for the radio drop down.
         */
        protected function resolveData()
        {
            $selectableGroupsDropDownContent     =  $this->renderSelectableGroupsContent();
            $data                                =  $this->getPermissionTypes();
            $dataIndex                           =  ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP;
            $dataSelectOption                    =  array();
            if ($selectableGroupsDropDownContent != null)
            {
                $dataSelectOption[$dataIndex]        = '&#160;' . $selectableGroupsDropDownContent;
            }
            else
            {
                unset($data[$dataIndex]);
            }
            return array($data, $dataSelectOption);
        }

        /**
         * Based on the model's attribute value being a explicitReadWriteModelPermissions object,
         * resolves the selected type value.
         * @return string
         */
        protected function resolveSelectedType()
        {
            $permitables = $this->getExplicitReadWriteModelPermissions()->getReadWritePermitables();
            if ($permitables == null)
            {
                return null;
            }
            else
            {
                assert(current($permitables) instanceof Group); // Not Coding Standard
                if (current($permitables)->name == Group::EVERYONE_GROUP_NAME)
                {
                    return ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
                }
                else
                {
                    return ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP;
                }
            }
        }

        /**
         * Based on the model's attribute value being a explicitReadWriteModelPermissions object,
         * resolves the selected group value if available.
         * @return string
         */
        protected function resolveSelectedGroup()
        {
            $permitables = $this->getExplicitReadWriteModelPermissions()->getReadWritePermitables();
            if ($permitables == null)
            {
                return null;
            }
            else
            {
                assert(count($permitables) == 1); // Not Coding Standard
                reset($permitables);
                $permitable = current($permitables);
                if ($permitable->name == Group::EVERYONE_GROUP_NAME)
                {
                    return null;
                }
                else
                {
                    return $permitable->id;
                }
            }
        }

        protected function getPermissionTypes()
        {
            return array(
                null                                                                 => Yii::t('Default', 'Owner'),
                ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP  => Yii::t('Default', 'Owner and users in'),
                ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP     => Yii::t('Default', 'Everyone'));
        }

        protected function renderSelectableGroupsContent()
        {
            $htmlOptions = array(
                'id'        => $this->getEditableInputId   ($this->getAttributeName(), 'nonEveryoneGroup'),
                'onclick'   => 'document.getElementById("{bindId}").checked="checked";',
            );
            $name        = $this->getEditableInputName($this->getAttributeName(), 'nonEveryoneGroup');
            $dropDownArray = $this->getSelectableGroupsData();
            if ($dropDownArray == null)
            {
                return null;
            }
            return CHtml::dropDownList($name, $this->resolveSelectedGroup(), $dropDownArray, $htmlOptions);
        }

        protected function getSelectableGroupsData()
        {
            $groups     = Group::getAll();
            $groupsData = array();
            foreach ($groups as $group)
            {
                if ($group->name != Group::EVERYONE_GROUP_NAME && $group->name != Group::SUPER_ADMINISTRATORS_GROUP_NAME)
                {
                    $groupsData[$group->id] = strval($group);
                }
            }
            return $groupsData;
        }
    }
?>