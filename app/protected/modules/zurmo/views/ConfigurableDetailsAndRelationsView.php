<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * The configurable View for a model detail view with relation views.
     */
    abstract class ConfigurableDetailsAndRelationsView extends DetailsAndRelationsView
    {
        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param array $params
         */
        public function __construct($controllerId, $moduleId, $params)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->uniqueLayoutId      = get_class($this);
            $this->params              = $params;
            $model                     = $params["relationModel"];
            $this->modelId             = $model->id;
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $getData  = GetUtil::getData();
            $metadata = self::getMetadata();
            $portletsAreRemovable   = true;
            $portletsAreMovable     = true;
            $this->resolvePortletConfigurableParams($portletsAreMovable, $portletsAreRemovable);
            $content          = $this->renderActionElementBar(true);
            $viewClassName    = static::getModelRelationsSecuredPortletFrameViewClassName();
            $configurableView = new $viewClassName( $this->controllerId,
                                                    $this->moduleId,
                                                    $this->uniqueLayoutId,
                                                    $this->params,
                                                    $metadata,
                                                    false,
                                                    $portletsAreMovable,
                                                    false,
                                                    '75,25', // Not Coding Standard
                                                    $portletsAreRemovable);
            $content          .=  $configurableView->render();
            $content          .= $this->renderScripts();
            return $content;
        }

        /**
         * @param bool $renderedInForm
         * @return A|string
         */
        protected function renderActionElementBar($renderedInForm)
        {
            $getData = GetUtil::getData();
            $toolbarContent = '';
            if (Yii::app()->userInterface->isMobile() === false)
            {
                $isViewLocked     = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
                $lockTitle        = Zurmo::t('Core', 'Unlock to edit this screen\'s layout');
                $unlockTitle      = Zurmo::t('Core', 'Lock and prevent layout changes to this screen');
                if ($isViewLocked === false)
                {
                    $url = $this->resolveLockPortletUrl((int)$getData['id'], '1');
                    $link = ZurmoHtml::link('<!--' . Zurmo::t('Core', 'Lock') . '-->', $url, array('class' => 'icon-unlock',
                                                                                                    'title' => $unlockTitle));
                    $content = parent::renderActionElementBar($renderedInForm) . $link;
                }
                else
                {
                    $url = $this->resolveLockPortletUrl((int)$getData['id'], '0');
                    $link = ZurmoHtml::link('<!--' . Zurmo::t('Core', 'Unlock') . '-->', $url, array('class' => 'icon-lock',
                                                                                                      'title' => $lockTitle));
                    $content = $link;
                }
                $toolbarContent = ZurmoHtml::tag('div', array('class' => 'view-toolbar'), $content);
            }
            $toolbarContent = ZurmoHtml::tag('div', array('class' => 'view-toolbar-container widgets-lock clearfix '), $toolbarContent);
            return $toolbarContent;
        }

        /**
         * @return array
         */
        protected static function resolveAjaxOptionsForAddPortlet()
        {
            $title = Zurmo::t('HomeModule', 'Add Portlet');
            return ModalView::getAjaxOptionsForModalLink($title);
        }

        /**
         * Resolves url for lock/unlock functionality
         * @param string $id
         * @param string $lockPortlets
         * @return string
         */
        private function resolveLockPortletUrl($id, $lockPortlets)
        {
            assert('is_string($lockPortlets)');
            assert('is_int($id)');
            $url = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/details',
                                                        array('id' => $id, 'lockPortlets' => $lockPortlets));
            return $url;
        }

        /**
         * Resolves portlet configurable parameters
         * @param boolean $portletsAreMovable
         * @param boolean $portletsAreRemovable
         */
        private function resolvePortletConfigurableParams(& $portletsAreMovable, & $portletsAreRemovable)
        {
            assert('is_bool($portletsAreMovable)');
            assert('is_bool($portletsAreRemovable)');
            $getData = GetUtil::getData();
            if (isset($getData['lockPortlets']))
            {
                $lockPortlets = (bool)$getData['lockPortlets'];
                if ($lockPortlets)
                {
                    ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', true);
                }
                else
                {
                    ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', false);
                }
            }
            $isViewLocked = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
            //Default case for the first time
            if ($isViewLocked === null)
            {
                ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', true);
                $isViewLocked = true;
            }
            if ($isViewLocked == true)
            {
                $portletsAreRemovable   = false;
                $portletsAreMovable     = false;
            }
        }
    }
?>