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

        public static function getKeys( RedBean_OODBBean $bean, $typeName )
        {
            $fieldName = self::getLinkField($typeName);
            $id = (int)$bean->$fieldName;
            $ids = R::getCol("select id from {$typeName} where " . $bean->getMeta("type") . "_id" . " = {$bean->id}");
            return $ids;
        }
    }
?>