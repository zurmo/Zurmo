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

    class MessageSource extends RedBeanModel
    {
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'category',
                    'source',
                ),
                'rules' => array(
                    array('category',           'required'),
                    array('category',           'type', 'type' => 'string'),
                    array('category',           'length',  'min'  => 1, 'max' => 30),
                    array('source',             'required'),
                    array('source',             'type', 'type' => 'blob')
                )
            );
            return $metadata;
        }

        /**
         * Gets a model from the database by category and source message
         * @param $category String Category fo the source
         * @param $source String The source message
         * @param $modelClassName Pass only when getting it at runtime
         *                        gets the wrong name.
         * @return A model of the type of the extending model.
         */
        public static function getByCategoryAndSource($category, $source, $modelClassName = null)
        {
            assert('!empty($category)');
            assert('!empty($source)');
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            if ($modelClassName === null)
            {
                $modelClassName = get_called_class();
            }
            $tableName = self::getTableName($modelClassName);
            $bean = R::findOne(
                               $tableName,
                               ' category = :category AND source = :source',
                               array(
                                     ':category' => $category,
                                     ':source'   => $source
                                     )
                               );
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if (!is_object($bean))
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean, $modelClassName);
        }

        /**
         * Adds new message source to the database
         *
         * @param String $category Category of the source message
         * @param String $source The source message
         */
        public static function addNewSource($category, $source)
        {
            assert('is_string($category) && !empty($category)');
            assert('is_string($source) && !empty($source)');
            $model = new MessageSource();
            $model->category = $category;
            $model->source   = $source;
            if (!$model->save())
            {
                throw new FailedToSaveModelException();
            }

            return $model;
        }
    }
?>
