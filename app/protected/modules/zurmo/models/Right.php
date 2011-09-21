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
                    return Yii::t('Default', '(None)');

                case self::ALLOW:
                    return Yii::t('Default', 'Allow');

                case self::DENY:
                    return Yii::t('Default', 'Deny');

                default:
                    return '???';
            }
        }

        public function __toString()
        {
            $s  = self::rightToString($this->type);
            $s .= ':';
            $s .= Yii::t('Default', $this->name);
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
