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

    class ZurmoRedBeanLinkManager
    {
        public static function getLinkField( $typeName, $name = null )
        {
            $fieldName = strtolower( $typeName )."_id";
            if ($name !== null)
            {
                $fieldName = "{$name}_$fieldName";
            }
            $fieldName = preg_replace( "/\W/", "", $fieldName );
            return $fieldName;
         }

        public static function link(RedBean_OODBBean $bean1, RedBean_OODBBean $bean2, $name = null)
        {
            if (!$bean2->id)
            {
                R::store( $bean2 );
            }
            $fieldName = self::getLinkField( $bean2->getMeta("type"), $name);
            $bean1->$fieldName = $bean2->id;
            return true;
        }

        public static function breakLink( RedBean_OODBBean $bean, $typeName, $name = null)
        {
            $fieldName = self::getLinkField($typeName, $name);
            $bean->$fieldName = null;
        }

        public static function getBean( RedBean_OODBBean $bean, $typeName, $name = null)
        {
            $fieldName = self::getLinkField($typeName, $name);
            $id = (int)$bean->$fieldName;
            if ($id)
            {
                return R::load($typeName, $id);
            }
            else
            {
                return null;
            }
        }

        public static function getKeys( RedBean_OODBBean $bean, $typeName, $name = null)
        {
            $fieldName    = self::getLinkField($typeName, $name);
            $id           = (int)$bean->$fieldName;
            $columnPrefix = self::resolveColumnPrefix($name);
            $columnName   = $columnPrefix . $bean->getMeta("type") . '_id';

            $ids = R::getCol("select id from {$typeName} where " . $columnName . " = {$bean->id}");
            return $ids;
        }

        public static function resolveColumnPrefix($linkName)
        {
            if ($linkName != null)
            {
                return $linkName .= '_';
            }
        }
    }
?>