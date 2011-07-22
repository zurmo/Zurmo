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
     * The base View for a module's list view.
     */
    abstract class ListView extends ModelView
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * True/false to decide if each row in the list view widget
         * will have a checkbox.
         */
        protected $rowsAreSelectable;

        /**
         * Unique identifier of the list view widget. Allows for multiple list view
         * widgets on a single page.
         * @see $
         */
        protected $gridId;

        /**
         * Additional unique identifier.
         * @see $gridId
         */
        protected $gridIdSuffix;

        /**
         * Array of model ids. Each id is for a different row checked off
         * @see selectAll
         */
        protected $selectedIds;

        /**
         * True/false whether to select the entire results of a list view display or not.
         * If set to true, then the selectedIds value will become null.
         * @see selectedIds
         */
        protected $selectAll;

        /**
         * Constructs a list view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider,
            $selectedIds,
            $selectAll,
            $gridIdSuffix = null
        )
        {
            assert('is_array($selectedIds)');
            assert('is_bool($selectAll)');
            assert('is_string($modelClassName)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->modelClassName         = $modelClassName;
            $this->dataProvider           = $dataProvider;
            $this->rowsAreSelectable      = true;
            $this->selectedIds            = $selectedIds;
            $this->selectAll              = $selectAll;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridId                 = 'list-view';
        }

        /**
         * Renders content for a list view. Utilizes a CActiveDataprovider
         * and a CGridView widget.
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.ExtendedGridView', $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content = $this->renderViewToolBar();
            $content .= $cClipWidget->getController()->clips['ListView'] . "\n";
            if ($this->rowsAreSelectable)
            {
                $content .= CHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
                $content .= CHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectAll', $this->selectAll) . "\n";
            }
            return $content;
        }

        protected function getCGridViewParams()
        {
            $columns = $this->getCGridViewColumns();
            assert('is_array($columns)');
            return array(
                'id' => $this->gridId . $this->gridIdSuffix,
                'htmlOptions' => array(
                    'class' => 'cgrid-view'
                ),
                'loadingCssClass' => 'cgrid-view-loading',
                'dataProvider' => $this->getDataProvider(),
                'selectableRows' => $this->getCGridViewSelectableRowsCount(),
                'selectAll' => $this->selectAll,
                //'filter' => $this->model, add in when search is working
                'pager' => $this->getCGridViewPagerParams(),
                'beforeAjaxUpdate' => $this->getCGridViewBeforeAjaxUpdate(),
                'afterAjaxUpdate'  => 'js:function(id, data) {processAjaxSuccessError(id, data)}',
                'cssFile' => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                'columns' => $columns,
                'nullDisplay' => '&#160;',
                'massActionMenu' => $this->getMassActionMenuForCurrentUser(),
            );
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'cssFile' => Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/cgrid-view.css',
                    'firstPageLabel' => '&lt;&lt;',
                    'prevPageLabel' => '&lt;',
                    'nextPageLabel' => '&gt;',
                    'lastPageLabel' => '&gt;&gt;',
                );
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         */
         protected function getCGridViewColumns()
         {
            $columns = array();
            if ($this->rowsAreSelectable)
            {
                if ($this->selectAll)
                {
                    $checked = 'true';
                    $checkBoxHtmlOptions = array('disabled' => 'disabled');
                }
                else
                {
                    $checked = 'in_array($data->id, array(' . implode(',', $this->selectedIds) . '))'; // Not Coding Standard
                    $checkBoxHtmlOptions = array();
                }
                $firstColumn = array(
                    'class'               => 'CheckBoxColumn',
                    'checked'             => $checked,
                    'id'                  => $this->gridId . $this->gridIdSuffix . '-rowSelector', // Always specify this as -rowSelector.
                    'checkBoxHtmlOptions' => $checkBoxHtmlOptions,
                );
                array_push($columns, $firstColumn);
            }
            $metadata = self::getMetadata();
            $modelClassName = $this->modelClassName;
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        foreach ($cell['elements'] as $columnInformation)
                        {
                            $columnClassName = $columnInformation['type'] . 'ListViewColumnAdapter';
                            $columnAdapter  = new $columnClassName($columnInformation['attributeName'], $this, array_slice($columnInformation, 1));
                            array_push($columns, $columnAdapter->renderGridViewData());
                        }
                    }
                }
            }
            $lastColumn = $this->getCGridViewLastColumn();
            if (!empty($lastColumn))
            {
                array_push($columns, $lastColumn);
            }
            return $columns;
         }

        protected function getCGridViewBeforeAjaxUpdate()
        {
            if ($this->rowsAreSelectable)
            {
                return 'js:function(id, options) {addListViewSelectedIdsAndSelectAllToUrl(id, options);}';
            }
            else
            {
                return null;
            }
         }

        /**
         * Returns meta data for use in automatically generating the view.
         * The meta data is comprised of columns. The parameters match the
         * parameters used in CGridView. See link below for more information.
         * http://www.yiiframework.com/doc/api/1.1/CGridView/
         *
         * The below example is a simple listview with the 'id' and 'name' attributes
         * The 'name' column has a hyperlink to the detail view for that record.
         *
         * @code
            <?php
                $metadata = array(
                    array(
                        'class' => 'CDataColumn',
                        'name'  => 'id',
                    ),
                    array(
                        'class'           => 'CLinkColumn',
                        'header'          => Yii::t('Default', 'Name'),
                        'labelExpression' => '$data->name',
                        'urlExpression'   => 'Yii::app()->createUrl("/{$this->grid->getOwner()->getModule()->getId()}/{$this->grid->getOwner()->getId()}/details", array("id" => $data->id))',
                    )
                );
            ?>
         * @endcode
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected function getCGridViewSelectableRowsCount()
        {
            if ($this->rowsAreSelectable)
            {
                return 2;
            }
            else
            {
                return 0;
            }
        }

        protected function getCGridViewLastColumn()
        {
            $url  = 'Yii::app()->createUrl("' . $this->getGridViewActionRoute('edit');
            $url .= '", array("id" => $data->id))';
            return array(
                'class'           => 'CButtonColumn',
                'template'        => '{update}',
                'buttons' => array(
                    'update' => array(
                    'url' => $url,
                    ),
                ),
            );
        }

        protected function getGridViewActionRoute($action)
        {
            return '/' . $this->moduleId . '/' . $this->controllerId . '/' . $action;
        }

        public function getLinkString($attributeString)
        {
            $string  = 'CHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'Yii::app()->createUrl("' . $this->getGridViewActionRoute('details') . '", array("id" => $data->id))';
            $string .= ')';
            return $string;
        }

        public static function getDesignerRulesType()
        {
            return 'ListView';
        }

        protected function getMassActionMenuForCurrentUser()
        {

            if (Right::ALLOW == Yii::app()->user->userModel->getEffectiveRight(
                    'ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE))
            {
                return array(
                    ''           => Yii::t('Default', 'Perform Action'),
                    'massEdit'   => Yii::t('Default', 'Update Selected'),
                );
            }
        }

        /**
         * Module class name for models linked from rows in the grid view.
         */
        protected function getActionModuleClassName()
        {
            return get_class(Yii::app()->getModule($this->moduleId));
        }

        protected function getDataProvider()
        {
            return $this->dataProvider;
        }
    }
?>
