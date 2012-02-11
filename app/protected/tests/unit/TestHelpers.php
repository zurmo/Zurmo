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

    class TestHelpers
    {
        public static function isClassInArray($className, array $array)
        {
            foreach ($array as $item)
            {
                if (get_class($item) == $className)
                {
                    return true;
                }
            }
            return false;
        }

        public static function makeRandomSentence($wordCount)
        {
            $words = array();
            for ($i = 0; $i < $wordCount; $i++)
            {
                $words[] = TestHelpers::makeRandomWord();
            }
            return implode(' ', $words);
        }

        public static function makeRandomWord($letterCount = 5)
        {
            $word = '';
            for ($i = 0; $i < $letterCount; $i++)
            {
                $word .= chr(rand(ord('a'), ord('z')));
            }
            return $word;
        }

        public static function makeUniqueRandomUsernames($count)
        {
            $usernames = array();
            for ($i = 0; $i < $count; $i++)
            {
                do
                {
                    $username = TestHelpers::makeRandomWord(6);
                }
                while (in_array($username, $usernames));
                $usernames[] = $username;
            }
            return $usernames;
        }

        public static function createControllerAndModuleByRoute($route)
        {
            $data = Yii::app()->createController($route);
            assert('$data[0]->getModule() instanceof Module');
            Yii::app()->setController($data[0]);
        }
    }
?>
