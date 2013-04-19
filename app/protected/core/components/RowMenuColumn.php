<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Column for displaying a dropdown menu on a row in a list view.
     * @see CGridView class
     */
    class RowMenuColumn extends CGridColumn
    {
        /**
         * @var array
         */
        public $rowMenu;

        /**
         * @var string
         */
        public $title;

        /**
         * ListView that calls this column.
         * @var object ListView
         */
        public $listView;

        public $redirectUrl;

        /**
         *Utilized to distinguish what model the view is building a list for
         * @var string
         */
        public $modelClassName;

        public function init()
        {
            assert('$this->listView instanceof ListView');
            assert('is_string($this->modelClassName) && $this->modelClassName != null');
        }

        /**
         * Renders the data cell content.
         * This method renders the menu
         * @param integer $row the row number (zero-based)
         * @param mixed $data the data associated with the row
         */
        protected function renderDataCellContent($row, $data)
        {
            $menuItems = array('label' => $this->title, 'items' => array());
            if (count($this->rowMenu['elements']) > 0)
            {
                foreach ($this->rowMenu['elements'] as $elementInformation)
                {
                    $elementclassname = $elementInformation['type'] . 'ActionElement';
                    $class = new ReflectionClass($elementclassname);
                    if ($class->implementsInterface('RowModelShouldRenderInterface') && !$elementclassname::shouldRenderByRowModel($data))
                    {
                            continue;
                    }
                    $params = array_slice($elementInformation, 1);
                    if (!isset($params['redirectUrl']))
                    {
                        $params['redirectUrl'] = $this->redirectUrl;
                    }
                    $params['modelClassName'] = $this->modelClassName;
                    $params['gridId'] = $this->grid->getId();
                    array_walk($params, array($this->listView, 'resolveEvaluateSubString'));
                    $element  = new $elementclassname($this->listView->getControllerId(),
                                                      $this->listView->getModuleId(),
                                                      $data->id, $params);
                    if (!ActionSecurityUtil::canCurrentUserPerformAction( $element->getActionType(), $data) ||
                        (isset($params['userHasRelatedModelAccess']) &&
                        $params['userHasRelatedModelAccess'] == false))
                    {
                        continue;
                    }
                    if ($element->isFormRequiredToUse())
                    {
                        throw new NotSupportedException();
                    }
                    $menuItems['items'][]   = $element->renderMenuItem();
                }
            }
            if (count($menuItems['items']) > 0)
            {
                $cClipWidget = new CClipWidget();
                $cClipWidget->beginClip("OptionMenu");
                $cClipWidget->widget('application.core.widgets.MbMenu', array(
                    'htmlOptions' => array('class' => 'options-menu edit-row-menu'),
                    'items'                   => array($menuItems),
                ));
                $cClipWidget->endClip();
                echo $cClipWidget->getController()->clips['OptionMenu'];
            }
        }

        public function renderDataCellContentFromOutsideClass($row, $data)
        {
            $this->renderDataCellContent($row, $data);
        }
    }
?>
