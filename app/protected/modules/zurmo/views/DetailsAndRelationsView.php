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
     * The base View for a model detail view with relation views.
     */
    abstract class DetailsAndRelationsView extends ConfigurableMetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $uniqueLayoutId;

        protected $params;

        public function __construct($controllerId, $moduleId, $params)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->uniqueLayoutId      = get_class($this);
            $this->params              = $params;
        }

        protected function renderContent()
        {
            $metadata = self::getMetadata();
            $leftBottomMetadataForPortlets['global'] = $metadata['global']['leftBottomView'];
            $rightTopMetadataForPortlets['global']   = $metadata['global']['rightTopView'];

            $detailsViewClassName = $metadata['global']['leftTopView']['viewClassName'];
            $leftTopView = new $detailsViewClassName(                  'Details',
                                                                        $this->params["controllerId"],
                                                                        $this->params["relationModuleId"],
                                                                        $this->params["relationModel"]);
            $leftBottomView = new ModelRelationsSecuredPortletFrameView($this->controllerId,
                                                                        $this->moduleId,
                                                                        $this->uniqueLayoutId . 'LeftBottomView',
                                                                        $this->params,
                                                                        $leftBottomMetadataForPortlets,
                                                                        false,
                                                                        false,
                                                                        $metadata['global']['leftBottomView']['showAsTabbed']);
            $rightTopView = new ModelRelationsSecuredPortletFrameView(  $this->controllerId,
                                                                        $this->moduleId,
                                                                        $this->uniqueLayoutId . 'RightBottomView',
                                                                        $this->params,
                                                                        $rightTopMetadataForPortlets,
                                                                        false,
                                                                        false);
            $leftVerticalGridView  = new GridView(2, 1);
            $leftVerticalGridView->setView($leftTopView, 0, 0);
            $leftVerticalGridView->setView($leftBottomView, 1, 0);
            $rightVerticalGridView  = new GridView(1, 1);
            $rightVerticalGridView->setView($rightTopView, 0, 0);

            $content = $leftVerticalGridView->render();
            $content .= $rightVerticalGridView->render();
            return $content;
        }
    }
?>