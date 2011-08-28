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

    // This is for testing details of how PDO works.
    class PDOTest extends BaseTest
    {
        public function testPDOTypesToShowTheDodginessOfNotBeingAbleToGetNumbersOut()
        {
            $wukka = R::dispense('wukka');
            $wukka->integer = 69;
            R::store($wukka);
            $id = $wukka->id;
            unset($wukka);

            $pdo = new PDO(Yii::app()->db->connectionString, Yii::app()->db->username, Yii::app()->db->password); // Not Coding Standard

            $phpVersion = substr(phpversion(), 0, 5);

            $statement = $pdo->prepare('select version() as version;');
            $statement->execute();
            $rows = $statement->fetchAll();
            $mysqlVersion = substr($rows[0]['version'], 0, 3);

            // These is what we are interested in. They seem to be ignored in
            // php 5.3 with mysql 5.1, but works in php 5.3.6 & mysql 5.5.
            // Both are needed to be set false.
            // Whether it is the newer php version or the newer mysql version
            // or both together, and at exactly which versions it works is
            // unknown. That is for some future investigation.
            $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,  false);

            $wukka = R::load('wukka', $id);

            $statement = $pdo->prepare('select * from wukka;');
            $statement->execute();
            $rows = $statement->fetchAll();
             if (($phpVersion == '5.3.6' || $phpVersion == '5.3.5') && $mysqlVersion == '5.5')
             {
                 $this->assertEquals('integer', gettype($rows[0]['integer'])); // Good! This is what we want!!!
                 $this->assertEquals('string',  gettype($wukka->integer));     // Dodgy!!!
             }
             else
             {
                 $this->assertEquals('string',  gettype($rows[0]['integer'])); // Dodgy!!!
                 $this->assertEquals('string',  gettype($wukka->integer));     // Dodgy!!!
             }
        }
    }
?>
