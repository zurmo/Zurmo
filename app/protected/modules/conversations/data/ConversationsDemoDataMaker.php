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
     * Class that builds demo conversations.
     */
    class ConversationsDemoDataMaker extends DemoDataMaker
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

            $conversations = array();
            foreach (self::getConversationData() as $randomConversationData)
            {
                $postData                    = array();
                $conversation                = new Conversation();
                $conversation->setScenario('importModel');
                $conversation->owner         = $demoDataHelper->getRandomByModelName('User');
                $conversation->createdByUser = $conversation->owner;
                $conversation->conversationItems->add($demoDataHelper->getRandomByModelName('Account'));
                $conversation->subject       = $randomConversationData['subject'];
                $conversation->description   = $randomConversationData['description'];
                //Add some comments
                foreach ($randomConversationData['comments'] as $commentDescription)
                {
                    $comment                = new Comment();
                    $comment->setScenario('importModel');
                    $comment->createdByUser = $demoDataHelper->getRandomByModelName('User');
                    $comment->description   = $commentDescription;
                    $conversation->comments->add($comment);
                    self::addItemIdToPostData($postData, $comment->createdByUser->getClassId('Item'));
                }

                //Add Super user
                $comment                = new Comment();
                $comment->description   = 'Great idea guys. Keep it coming.';
                $conversation->comments->add($comment);
                self::addItemIdToPostData($postData, Yii::app()->user->userModel->getClassId('Item'));

                $saved = $conversation->save();
                assert('$saved');

                //any user who has made a comment should be added as a participant and resolve permissions
                $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                     makeBySecurableItem($conversation);
                ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                                $conversation, $postData, $explicitReadWriteModelPermissions);
                $saved = $conversation->save();
                assert('$saved');
                $success = ExplicitReadWriteModelPermissionsUtil::
                            resolveExplicitReadWriteModelPermissions($conversation, $explicitReadWriteModelPermissions);
                $saved = $conversation->save();
                assert('$success');
                $conversations[] = $conversation->id;
            }
            $demoDataHelper->setRangeByModelName('Conversation', $conversations[0], $conversations[count($conversations)-1]);
        }

        protected static function addItemIdToPostData(& $postData, $itemId)
        {
            if (isset($postData['itemIds']))
            {
                $postData['itemIds'] .= ',' . $itemId; // Not Coding Standard
            }
            else
            {
                $postData['itemIds'] = $itemId;
            }
        }

        protected static function getConversationData()
        {
            $data = array(
                    array('subject'     => 'Should we consider building a new corporate headquarters on Mars?',
                          'description' => 'We are running out of good locations to put our offices. I am thinking we should open an office on Mars.',
                          'comments'    => array(
                              'Interesting Idea',
                              'I am not sure Mars is best.  What about Titan?  It offers some advantages.',
                              'Are we allowed to hire aliens?',
                              'Some info about Mars: Mars is the fourth planet from the Sun in the Solar System. ' .
                              'Named after the Roman god of war, Mars, it is often described as the "Red Planet" as ' .
                              'the iron oxide prevalent on its surface gives it a reddish appearance',
                          )),
                    array('subject'     => 'I am considering a new marketing campaign that uses elephants.  What do you guys think?',
                          'description' => 'We are going to maybe do a tv commercial and I need to make it compelling.',
                          'comments'    => array(
                              'Elephants are cool.',
                              'What about giraffes.  Here is some info: he giraffe (Giraffa camelopardalis) is an African ' .
                              'even-toed ungulate mammal, the tallest living terrestrial animal and the largest ruminant. ' .
                              'Its specific name refers to its camel-like face and the patches of color on its fur, ' .
                              'which bear a vague resemblance to a leopard\'s spots.',
                              'I think something like a snake eating a mouse could be funny.'
                          )),
                    array('subject'     => 'Vacation time in December',
                          'description' => 'My wife and I are thinking about going to Hawaii in December.  Does this time of year work?',
                          'comments'    => array(
                              'That should be fun.  Bring your laptop in case we need you!',
                              'Do not bring your laptop.  That would ruin the fun.',
                              'Make sure you hike up the volcano.',
                              'I want to take a vacation.',
                              'We should have a company retreat in Hawaii.  That would be fun!'
                          )),
            );
            return $data;
        }
    }
?>