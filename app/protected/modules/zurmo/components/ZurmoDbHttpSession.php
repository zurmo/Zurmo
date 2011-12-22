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

    class ZurmoDbHttpSession extends CHttpSession
    {
        public $_compareIpBlocks = 0;

        public $_compareIpAddress = false;

        public $_compareUserAgent = false;

      /**
       * Returns a value indicating whether to use custom session storage.
       * This method overrides the parent implementation and always returns true.
       * @return boolean whether to use custom storage.
       */
      public function getUseCustomStorage()
      {
        return true;
      }

      /**
       * Updates the current session id with a newly generated one .
       * Please refer to {@link http://php.net/session_regenerate_id} for more details.
       * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
       * @since 1.1.8
       */
      public function regenerateID($deleteOldSession=false)
      {
        $oldID = session_id();
        parent::regenerateID(false);
        $newID = session_id();

        $session = Session::getBySessionIdIpAddressAndUserAgent($oldID);

        if($session !== false)
        {
          if($deleteOldSession)
          {
            $session->sessionId = $newID;
            $session->save();
          }
          else
          {
            $newSession = new Session();
            $newSession->sessionId = $newID;
            $newSession->ipAddress = $session->ipAddress;
            $newSession->userAgent = $session->userAgent;
            $nesSession->expire    = $session->expire;
            $newSession->data      = $session->data;
            $newSession->save();
          }
        }
        else
        {
          // shouldn't reach here normally
            $newSession = new Session();
            $newSession->sessionId = $newID;
            $nesSession->expire    = $session->expire;
            $newSession->save();
          ;
        }
      }

      /**
       * Session open handler.
       * Do not call this method directly.
       * @param string $savePath session save path
       * @param string $sessionName session name
       * @return boolean whether session is opened successfully
       */
      public function openSession($savePath,$sessionName)
      {
        Session::deleteExpiredSessions();
        return true;
      }

      /**
   * MyCDbHttpSession::readSession()
   *
   * @param mixed $id
   * @return mixed $data on success, empty string on failure
   */
  public function readSession($id)
  {

        $ipAddress = null;
        $userAgent = null;
        if($this->getCompareIpAddress())
        {
            if($this->getCompareIpBlocks() > 0)
                $ipAddress = sprintf("%u", ip2long($this->getClientIpBlocks()));
            else
                $ipAddress = sprintf("%u", ip2long(Yii::app()->request->getUserHostAddress()));
        }
        if($this->getCompareUserAgent())
        {
            $userAgent = md5(Yii::app()->request->getUserAgent());
        }
        $session = Session::getBySessionIdIpAddressAndUserAgent($id, $ipAddress, $userAgent);
        return (false === $session) ? '' : $session->data;
  }

      /**
   * MyCDbHttpSession::writeSession()
   *
   * @param mixed $id
   * @param mixed $data
   * @return boolean
   */
  public function writeSession($id, $data)
  {
        try
        {
            $expire=time() + $this->getTimeout();
            $ipAddress = null;
            $userAgent = null;
            if($this->getCompareIpAddress())
            {
                if($this->getCompareIpBlocks() > 0)
                    $ipAddress = sprintf("%u", ip2long($this->getClientIpBlocks()));
                else
                    $ipAddress = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
            }
            if($this->getCompareUserAgent())
            {
                $userAgent = md5(Yii::app()->request->getUserAgent());
            }
            $session = Session::getBySessionIdIpAddressAndUserAgent($id, $ipAddress, $userAgent);


            if(false===$session)
            {
                $session = Session::getBySessionIdIpAddressAndUserAgent($id);
                $session->delete();

                $newSession = new Session();
                if($this->getCompareIpAddress())
                {
                    if($this->getCompareIpBlocks() > 0)
                        $newSession->ipAddress = sprintf("%u", ip2long($this->getClientIpBlocks()));
                    else
                        $newSession->ipAddress = sprintf("%u", ip2long(Yii::app()->request->getUserHostAddress()));
                }
                if($this->getCompareUserAgent())
                {
                    $newSession->userAgent = md5(Yii::app()->request->getUserAgent());
                }
                $newSession->expire = $expire;
                $newSession->data = $data;
                $newSession->save();
            }
            else
            {
                // Session is not expired, refresh expiry time.
                $session->expire = $expire;
                $session->data = $data;
                $session->save();
            }
        }
        catch (Exception $e)
        {
            throw new NotSupportedException(
            Yii::t('Default', 'An error occured while saving session.'));
            return false;
        }
        return true;
  }

      /**
       * Session destroy handler.
       * Do not call this method directly.
       * @param string $id session ID
       * @return boolean whether session is destroyed successfully
       */
      public function destroySession($id)
      {
        $session = Session::getBySessionIdIpAddressAndUserAgent($id);
        if (false !== $session)
        {
            $session->delete();
        }
        return true;
      }

      /**
       * Session GC (garbage collection) handler.
       * Do not call this method directly.
       * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
       * @return boolean whether session is GCed successfully
       */
      public function gcSession($maxLifetime)
      {
        Session::deleteExpiredSessions();
        return true;
      }

      /**
      * MyCDbHttpSession::getClientIpBlocks()
      *
      * @return on success newly created ip based on block, on failure, localhost ip
      *
      * Note, we could use a regular expression like:
      * /^([0-9]{1,3}+\.)([0-9]{1,3}+\.)([0-9]{1,3}+\.)([0-9]{1,3}+)$/
      * But, i think it's better this way because we have more control over the IP blocks.
      */
      public function getClientIpBlocks()
      {
          $remoteIp=Yii::app()->request->getUserHostAddress();
          if(strpos($remoteIp,'.')!==false)
          {
              $blocks=explode('.',$remoteIp);
              $partialIp=array();
              $continue=false;
              $i=0;
              if(count($blocks)==4)
              {
                  $continue=true;
                  foreach($blocks AS $block)
                  {
                      ++$i;
                      if(false===is_numeric($block)||$block<0||$block>255)
                      {
                          $continue=false;
                          break;
                      }
                      if($i<=$this->getCompareIpBlocks())
                      $partialIp[]=$block;
                      else
                      $partialIp[]=0;
                  }
              }
              if($continue)
              return implode('.',$partialIp);
          }
          return '127.0.0.1';
      }

      /**
       * MyCDbHttpSession::setCompareIpBlocks()
       *
       * @param int $int
       */
      public function setCompareIpBlocks($int)
      {
          $int=(int)$int;
          if($int < 0)
          $this->_compareIpBlocks=0;
          elseif($int > 4)
          $this->_compareIpBlocks=4;
          else
          $this->_compareIpBlocks=$int;
      }

      /**
       * MyCDbHttpSession::getCompareIpBlocks()
       */
      public function getCompareIpBlocks()
      {
          return $this->_compareIpBlocks;
      }

      /**
       * MyCDbHttpSession::setCompareIpAddress()
       *
       * @param bool $bool
       */
      public function setCompareIpAddress($bool)
      {
          $this->_compareIpAddress=(bool)$bool;
      }

      /**
       * MyCDbHttpSession::getCompareIpAddress()
       */
      public function getCompareIpAddress()
      {
          return $this->_compareIpAddress;
      }

      /**
       * MyCDbHttpSession::setCompareUserAgent()
       *
       * @param bool $bool
       */
      public function setCompareUserAgent($bool)
      {
          $this->_compareUserAgent=(bool)$bool;
      }

      /**
       * MyCDbHttpSession::getCompareUserAgent()
       */
      public function getCompareUserAgent()
      {
          return $this->_compareUserAgent;
      }
    }
?>
