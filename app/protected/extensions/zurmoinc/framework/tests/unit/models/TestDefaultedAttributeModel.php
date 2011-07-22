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

    class TestDefaultedAttributeModel extends RedBeanModel
    {
        public function __construct($setDefaults = true, RedBean_OODBBean $bean = null)
        {
            parent::__construct($setDefaults, $bean);
            if ($bean == null)
            {
                // The NOT recommended way, but is set at
                // construction, but won't be saved with
                // the metadata.
                $this->email1 = 'no-reply@nowhere.com';
            }
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'email1',
                    'email2',
                ),
                'rules' => array(
                    // The recommended way, because it is the yii way,
                    // but is set at validation, not at construction.
                    // Take note of the setOnEmpty property
                    // of CDefaultValueValidator.
                    // http://www.yiiframework.com/doc/api/1.1/CDefaultValueValidator
                    array('email2', 'default', 'value' => 'no-reply@nowhere.com'),
                )
            );
            return $metadata;
        }
    }
?>
