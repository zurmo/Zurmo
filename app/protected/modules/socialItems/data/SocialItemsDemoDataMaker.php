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

    /**
     * Class that builds demo social feed data
     */
    class SocialItemsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array('users', 'groups', 'accounts');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');
            assert('$demoDataHelper->isSetRange("Account")');

            $socialItems = array();
            $data        = self::getSocialItemData();
            shuffle($data);
            foreach ($data as $randomSocialItemData)
            {
                $postData               = array();
                $socialItem                = new SocialItem();
                $socialItem->setScenario('importModel');
                $socialItem->owner         = $demoDataHelper->getRandomByModelName('User');
                $socialItem->createdByUser = $socialItem->owner;
                //check if we should connect to a note
                if (isset($randomSocialItemData['noteDescription']))
                {
                    $note              = new Note();
                    $account           = $demoDataHelper->getRandomByModelName('Account');
                    $note->description = $randomSocialItemData['noteDescription'];
                    $note->owner       = $socialItem->owner;
                    $note->activityItems->add($account);
                    $this->populateModel($note);
                    $saved = $note->save();
                    assert('$saved');
                    $socialItem->note  = $note;
                }
                else
                {
                    $socialItem->description   = $randomSocialItemData['description'];
                }
                //Add some comments
                foreach ($randomSocialItemData['comments'] as $commentDescription)
                {
                    $comment                = new Comment();
                    $comment->setScenario('importModel');
                    $comment->createdByUser = $demoDataHelper->getRandomByModelName('User');
                    $comment->description   = $commentDescription;
                    $socialItem->comments->add($comment);
                }
                $socialItem->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME),
                                            Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                $saved = $socialItem->save();
                assert('$saved');
                $socialItems[] = $socialItem->id;
            }
            $demoDataHelper->setRangeByModelName('SocialItem', $socialItems[0], $socialItems[count($socialItems) - 1]);
        }

        protected static function getSocialItemData()
        {
            $data = array(
                    array('description' => 'Where should we have the Christmas party?',
                          'comments'    => array(
                              'How about at a museum?',
                              'I am going to be out of town, so I can\'t attend.',
                              'I guess i can take this on.',
                          )),
                    array('description' => 'Golf time',
                          'comments'    => array(
                              'I wish i was in sales..',
                              'Dude, IT just twiddles their thumbs most of the time anyways :)',
                              'Yeah whatever..',
                              'I am in for golf, primarly drinking and riding the cart.'
                          )),
                    array('description' => 'Anyone interested in going to San Diego for the trade show?',
                          'comments'    => array()),
                    array('description' => 'Just stubbed my toe. Ouch!',
                          'comments'    => array()),
                    array('description' => 'Ask Barry why we can\'t use our cell phones in the conference room',
                          'comments'    => array()),
                    array('description' => 'I love fridays!',
                          'comments'    => array(
                              'Dude, get to work',
                              'Lets get some beers',
                          )),
                    array('noteDescription' => 'Bam. Closed another deal!',
                          'comments'    => array(
                              'Awesome!',
                              'I second that.',
                              'You are buying drinks tonight'
                          )),
                    array('noteDescription' => 'This account is heating up!',
                          'comments'    => array(
                              'I would love us to get this guy as a customer',
                              'I second that.',
                              'Would be an amazing case study'
                          )),
                    array('noteDescription' => 'Why is this customer having so many problems. Sigh',
                          'comments'    => array(
                              'Did you contact Sarah in client services yet?',
                              'That is probably a good idea',
                              'Only if sarah is having a good day',
                          )),
                    array('description' => 'Game on! I received a new badge: For being awesome',
                          'comments'    => array()),
                    array('description' => 'Game on! I reached level 2',
                          'comments'    => array()),
                    array('description' => 'Game on! I received a new badge: 15 accounts created',
                          'comments'    => array()),
                    array('description' => 'Game on! I reached level 3',
                          'comments'    => array()),
                    array('description' => 'Game on! I received a new badge: 5 opportunities searched',
                          'comments'    => array()),
                    array('description' => 'Game on! I reached level 4',
                          'comments'    => array()),
                    array('description' => 'Game on! I received a new badge: Logged in 5 times at night',
                          'comments'    => array()),
                    array('description' => 'Game on! I reached level 5',
                          'comments'    => array()),
            );
            return $data;
        }
    }
?>