<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ProductCreateLinkActionElement extends CreateLinkActionElement
    {
        /**
         * Manage security check during render since you have multiple modules to check against
         * @return null|string
         */
        public function getActionType()
        {
            return null;
        }

        /**
         * @return null or string containing create link
         */
        public function render()
        {
            $items = array();
            if (RightsUtil::doesUserHaveAllowByRightName('ProductsModule', ProductsModule::getCreateRight(),
                                                        Yii::app()->user->userModel))
            {
                $items[] = array('label'    => Zurmo::t('ProductsModule', 'Create Product'),
                                  'url'     => Yii::app()->createUrl('products/default/create'));
            }
            if (RightsUtil::doesUserHaveAllowByRightName('ProductTemplatesModule', ProductTemplatesModule::getCreateRight(),
                                                        Yii::app()->user->userModel))
            {
                $items[] = array('label'   => Zurmo::t('ProductTemplatesModule', 'Create Catalog Item'),
                                 'url'     => Yii::app()->createUrl('productTemplates/default/create'));

                $items[] = array('label'   => Zurmo::t('ProductTemplatesModule', 'Create Category'),
                                 'url'     => Yii::app()->createUrl('productTemplates/category/create'));
            }
            if (!empty($items))
            {
                $menuItems      = array( 'label' => $this->getLabel(),
                                         'url'   => null,
                                         'items' => $items);
                $cClipWidget    = new CClipWidget();
                $cClipWidget->beginClip("ActionMenu");
                $cClipWidget->widget('application.core.widgets.MbMenu', array(
                    'htmlOptions' => array('id' => 'MashableInboxCreateDropdown'),
                    'items'       => array($menuItems),
                ));
                $cClipWidget->endClip();
                return $cClipWidget->getController()->clips['ActionMenu'];
            }
            return null;
        }
    }
?>