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

    class Right extends OwnedModel
    {
        const NONE  = 0x0;
        const ALLOW = 0x1;
        const DENY  = 0x2;

        public static function getByModuleNameAndRightName($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName != ""');
            $bean = R::findOne('_right', "modulename = '$moduleName' and name = '$rightName'");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

        public static function removeAllForPermitable(Permitable $permitable)
        {
            R::exec("delete from _right where permitable_id = :id;",
                    array('id' => $permitable->getClassId('Permitable')));
        }

        public static function removeAll()
        {
            R::exec("delete from _right;");
        }

        public static function rightToString($right)
        {
            switch ($right)
            {
                case self::NONE:
                    return Zurmo::t('Core', '(None)');

                case self::ALLOW:
                    return Zurmo::t('ZurmoModule', 'Allow');

                case self::DENY:
                    return Zurmo::t('ZurmoModule', 'Deny');

                default:
                    return '???';
            }
        }

        public function __toString()
        {
            $s  = self::rightToString($this->type);
            $s .= ':';
            $s .= Zurmo::t('ZurmoModule', $this->name);
            return $s;
        }

        public static function mangleTableName()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'moduleName',
                    'name',
                    'type',
                ),
                'relations'   => array(
                    'permitable' => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'Permitable'),
                ),
                'rules' => array(
                    array('moduleName', 'required'),
                    array('moduleName', 'type',      'type' => 'string'),
                    array('moduleName', 'length',    'min'  => 3, 'max' => 64),
                    array('name',       'required'),
                    array('name',       'type',      'type' => 'string'),
                    array('name',       'length',    'min'  => 3, 'max' => 64),
                    array('type',       'required'),
                    array('type',       'type',      'type' => 'integer'),
                    array('type',       'numerical', 'min'  => 0, 'max' => 2),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>