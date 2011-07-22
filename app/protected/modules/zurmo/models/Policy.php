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

    class Policy extends OwnedModel
    {
        const NONE = null;
        const NO   = 0;
        const YES  = 1;

        public static function getByModuleNameAndPolicyName($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            $bean = R::findOne('policy', "modulename = '$moduleName' and name = '$policyName'");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

        public static function removeAllForModule($moduleName)
        {
            assert('is_string($moduleName)');
            assert('$moduleName != ""');
            R::exec("delete from policy where modulename = '$moduleName';");
        }

        public static function removeAllForPermitable(Permitable $permitable)
        {
            R::exec("delete from policy where permitable_id = :id;",
                    array('id' => $permitable->getClassId('Permitable')));
        }

        public static function removeAll()
        {
            R::exec("delete from policy;");
        }

        public function __toString()
        {
            $s = "{$this->name} = ";
            if (is_string($this->value))
            {
                $s .= "'{$this->value}'";
            }
            else
            {
                $s .= $this->value;
            }
            return $s;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'moduleName',
                    'name',
                    'value',
                ),
                'relations'   => array(
                    'permitable' => array(RedBeanModel::HAS_MANY_BELONGS_TO, 'Permitable'),
                ),
                'rules' => array(
                    array('moduleName', 'required'),
                    array('moduleName', 'type',   'type' => 'string'),
                    array('moduleName', 'length', 'min'  => 3, 'max' => 64),
                    array('name',       'required'),
                    array('name',       'type',   'type' => 'string'),
                    array('name',       'length', 'min'  => 3, 'max' => 64),
                    array('value',      'type',   'type' => 'string'),
                    array('value',      'length', 'max'  => 64),
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
