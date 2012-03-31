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

    class DropDownDependencyAttributeEditView extends AttributeEditView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton'),
                        ),
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'AttributeType'),
                                            ),
                                        ),
                                    ),
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'attributeName', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'attributeLabels', 'type' => 'AttributeLabel'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function renderAfterFormLayout($form)
        {
            static::renderScripts();
            $content  = '<h3>' . $this->getAfterFormLayoutTranslatedTitleContent() . '</h3>';
            //$content .= '<div class="horizontal-line"></div>' . "\n";
            $content .= $form->error($this->model, 'mappingData');
            $content .= $this->renderContainerAndMappingLayoutContent($this->model, $this->controllerId, $this->moduleId);
            return $content;
        }

        public static function renderContainerAndMappingLayoutContent(DropDownDependencyAttributeForm $model,
                                                                      $controllerId, $moduleId, $renderContainer = true)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_bool($renderContainer)');
            static::renderScripts();
            $mappingDataDivId                = 'DropDownDependencyMappingData';
            $ajaxActionId                    = 'changeDropDownDependencyAttribute';
            $content                         = null;
            if ($renderContainer)
            {
                $content                    .= '<div id="' . $mappingDataDivId . '">';
            }
            $adapter                         = new DropDownDependencyToMappingLayoutAdapter(
                                                    $model->modelClassName, $model->attributeName, 4);
            $dependencyCollection            = $adapter->makeDependencyCollectionByMappingData($model->mappingData);
            $dropDownDependencyMappingLayout = new DropDownDependencyMappingFormLayoutUtil($dependencyCollection,
                                                                                           get_class($model),
                                                                                           $controllerId,
                                                                                           $moduleId,
                                                                                           $ajaxActionId,
                                                                                           $mappingDataDivId);
            $content                        .= $dropDownDependencyMappingLayout->render();
            if ($renderContainer)
            {
                $content                        .= '</div>' . "\n";
            }
            return $content;
        }

        protected function getAfterFormLayoutTranslatedTitleContent()
        {
            return Yii::t('Default', 'Dropdown Dependency Mapping');
        }

        protected static function renderScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/dropDownInteractions.js'));
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets') . '/jquery.dropkick-1.0.0.js'));
        }
    }
?>
