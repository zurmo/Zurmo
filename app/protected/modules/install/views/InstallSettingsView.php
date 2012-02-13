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
     * The install settings view. This is the view where users can enter settings during an installation.
     */
    class InstallSettingsView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $model;

        public function __construct($controllerId, $moduleId, $model)
        {
            assert('$model instanceof CFormModel');
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->model          = $model;
        }

        /**
         * Override of parent function. Makes use of the CActiveForm
         * widget to provide an editable form.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'CActiveForm',
                                                                array_merge(
                                                                    array('id' => 'install-form'),
                                                                    $this->resolveActiveFormAjaxValidationOptions()
                                                                )
                                                            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;

            $content .= '</div>';
            return $content;
        }

        /**
         * Render an install settings view.
         * @return A string containing the element's content.
         */
        protected function renderFormLayout($form = null)
        {
            $metadata = self::getMetadata();
            $content  = '<table>';
            $content .= '<colgroup><col/><col/><col/></colgroup>';
            assert('count($metadata["global"]["panels"]) == 1');
            foreach ($metadata['global']['panels'] as $key => $panel)
            {
                $content .= '<tbody>';
                foreach ($panel['rows'] as $row)
                {
                    $content .= '<tr>';
                    foreach ($row['cells'] as $cell)
                    {
                        if (!empty($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                $elementclassname = $elementInformation['type'] . 'Element';
                                $element = new $elementclassname($this->model, $elementInformation['attributeName'],
                                                                 $form, array_slice($elementInformation, 2));
                                $element->editableTemplate = '<th  nowrap="nowrap">{label}</th><td
                                                              colspan="{colspan}">{content}{error}</td>';
                                $content .= $element->render();
                                $content .= '<td>' . Yii::app()->format->text($elementInformation['description']) . '</td>';
                            }
                        }
                    }
                    $content .= '</tr>';
                }
                $element  = new SaveButtonActionElement($this->controllerId, $this->moduleId,
                                                        null, array('label' => Yii::t('Default', 'Install')));
                $content .= '<tr><td colspan="3" style="text-align:right;">' . $element->render() . '</td></tr>';
                $content .= '</tbody>';
            }
            $content .= '</table>';
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'databaseHostname', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'Can either be a domain ' .
                                                      'name or an IP address.')),
                                            ),
                                        ),
                                    ),
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'databaseAdminUsername', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'Leave this blank unless you ' .
                                                      'would like to create the user and database for Zurmo to run in.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'databaseAdminPassword', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'Leave this blank unless you ' .
                                                      'would like to create the user and database for Zurmo to run in.'))
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'databaseName', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'The name of the database you ' .
                                                      'want to run Zurmo in.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'removeExistingData', 'type' => 'CheckBox',
                                                      'description' => Yii::t('Default', 'WARNING! - If the database ' .
                                                      'already exists the data will be completely removed. ' .
                                                      'This must be checked if you are specifying an existing database.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'databaseUsername', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'User who can connect ' .
                                                      'to the database.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'databasePassword', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'User`s password.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'superUserPassword', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'Zurmo administrative password. ' .
                                                      'The username is `super`.  You can change this later.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'memcacheHostname', 'type' => 'MemcacheText',
                                                      'description' => Yii::t('Default', 'Memcache host name. Default ' .
                                                      'is 127.0.0.1')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'memcachePortNumber', 'type' => 'MemcacheText',
                                                      'description' => Yii::t('Default', 'Memcache port number. Default ' .
                                                      'is 11211')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'installDemoData', 'type' => 'CheckBox',
                                                      'description' => Yii::t('Default', 'Install demo data.')),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'hostInfo', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'Host name where Zurmo will be installed.'))
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'scriptUrl', 'type' => 'Text',
                                                      'description' => Yii::t('Default', 'The relative path where ' .
                                                      'Zurmo will be installed.')),
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

        protected function resolveActiveFormAjaxValidationOptions()
        {
            return array('enableAjaxValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                    'validateOnChange' => false,
                    'inputContainer' => 'td',
                )
            );
        }
    }
?>
