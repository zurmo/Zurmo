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

    class AttributesCollectionView extends MetadataView
    {
        protected $cssClasses =  array( 'TableOfContentsView');

        protected $controllerId;

        protected $moduleId;

        protected $attributesCollection;

        protected $moduleClassName;

        protected $modelClassName;

        public function __construct($controllerId, $moduleId, $attributesCollection, $moduleClassName, $modelClassName, $title)
        {
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->attributesCollection   = $attributesCollection;
            $this->moduleClassName        = $moduleClassName;
            $this->modelClassName         = $modelClassName;
            $this->modelId                = null;
            $this->title                  = $title;
        }

        protected function renderContent()
        {
            $content  = null;
            $content .= $this->renderBeforeTableContent();
            if (count($this->attributesCollection) > 0)
            {
                $content .= '<div>';
                $content .= $this->renderTitleContent();
                $content .= '<ul class="configuration-list">';
                foreach ($this->attributesCollection as $attributeName => $information)
                {
                    $route = $this->moduleId . '/' . $this->controllerId . '/AttributeEdit/';
                    $attributeFormClassName = AttributesFormFactory::getFormClassNameByAttributeType($information['elementType']);
                    if ($information['elementType'] == 'EmailAddressInformation' ||
                        $information['elementType'] == 'Address' ||
                        $information['elementType'] == 'User' ||
                        $information['isReadOnly'] ||
                        $attributeName == 'id' ||
                        $this->isAttributeOnModelOrCastedUp($attributeName))
                    {
                        //temporary until we figure out how to handle these types.
                        $linkContent = null;
                    }
                    else
                    {
                        $url         = Yii::app()->createUrl($route,
                                            array(
                                                'moduleClassName' => $this->moduleClassName,
                                                'attributeTypeName' => $information['elementType'],
                                                'attributeName' => $attributeName)
                                            );
                        $linkContent = static::renderConfigureLinkContent($url, 'edit-link-' . $attributeName);
                    }
                    $content .= '<li>';
                    $content .= '<h4>' . $information['attributeLabel'] . '</h4>';
                    $content .= ' - ' . $attributeFormClassName::getAttributeTypeDisplayName();
                    $content .= $linkContent;
                    $content .= '</li>';
                }
                $content .= '</ul>';
                $content .= '</div>';
            }
            return $content;
        }

        /**
         * If the attribute is not on the same model class but nested up, it should be blocked from being configured
         * in the designer tool since it can have side effects. You can still manually override this in the code if
         * necessary.
         */
        protected function isAttributeOnModelOrCastedUp($attributeName)
        {
            assert('is_string($attributeName)');
            $attributeAdapter = new RedBeanModelAttributeToDataProviderAdapter($this->modelClassName, $attributeName);
            if (!$attributeAdapter->getModel()->isAttribute($attributeName))
            {
                return false;
            }
            if ($attributeAdapter->getAttributeModelClassName() != $this->modelClassName)
            {
                return true;
            }
            return false;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function renderBeforeTableContent()
        {
        }

        protected static function renderConfigureLinkContent($url, $id)
        {
            assert('is_string($url) || $url == null');
            assert('is_string($id)');
            return ZurmoHtml::link(ZurmoHtml::wrapLabel(Zurmo::t('DesignerModule', 'Configure')),
                                $url, array('id' => $id, 'class' => 'z-button'));
        }
    }
?>