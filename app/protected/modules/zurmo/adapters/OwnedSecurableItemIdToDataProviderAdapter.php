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
     * Special handling for OwnedSecurableItem. @see OwnedSecurableItem::resolveReadPermissionsOptimizationToSqlQuery
     */
    class OwnedSecurableItemIdToDataProviderAdapter extends RedBeanModelAttributeToDataProviderAdapter
    {
        /**
         * Extended to ensure the attribute specified is null, since it is not used when using this adapter.
         * @param string $modelClassName
         * @param unknown_type $attribute
         * @param unknown_type $relatedAttribute
         */
        public function __construct($modelClassName, $attribute, $relatedAttribute = null)
        {
            assert('is_string($modelClassName)');
            assert('$attribute == null');
            assert('$relatedAttribute == null');
            $this->modelClassName   = $modelClassName;
        }

        /**
         * Extended to only return 'OwnedSecurableItem' as the attribute model class name.
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getAttributeModelClassName()
         */
        public function getAttributeModelClassName()
        {
            return 'OwnedSecurableItem';
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getColumnName()
         */
        public function getColumnName()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::isRelation()
         */
        public function isRelation()
        {
            return false;
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelationType()
         */
        public function getRelationType()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelationModel()
         */
        public function getRelationModel()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelationModelClassName()
         */
        public function getRelationModelClassName()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelatedAttributeModelClassName()
         */
        public function getRelatedAttributeModelClassName()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelationTableName()
         */
        public function getRelationTableName()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelatedAttributeTableName()
         */
        public function getRelatedAttributeTableName()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see RedBeanModelAttributeToDataProviderAdapter::getRelatedAttributeColumnName()
         */
        public function getRelatedAttributeColumnName()
        {
            throw new NotSupportedException();
        }
    }
?>