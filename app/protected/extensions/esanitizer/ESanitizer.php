<?php
/*
 * Created on 9 Sep 2009
 * by charles@yorkseo.com
 */

 class ESanitizer extends CApplicationComponent
{


   /**
   * @var boolean whether to sanitize POST input, defaults to true.
   */
  public $sanitizePost = true;
  /**
   * @var boolean whether to sanitize GET input, defaults to false.
   */
  public $sanitizeGet = false;
  /**
   * @var boolean whether to sanitize COOKIE input, defaults to true.
   */
  public $sanitizeCookie = true;
  /**
   * @var boolean whether to sanitize FILES input, defaults to true.
   */
  public $sanitizeFiles = true;
  /**
   * @var boolean whether to nofollow links, defaults to true.
   */
  public $linkNoFollow = true;
  /**
   * @var boolean whether to open links in new window, defaults to false.
   */
  public $linkNewWindow = false;

  public $purifier;
  /**
   * Initializes the application component.
   * This method overrides the parent implementation by preprocessing
   * the user request data.
   */

  public function init()
  {
    parent::init();
    if (($this->sanitizePost && count($_POST) > 0) || ($this->sanitizeGet && count($_GET) > 0) || ($this->sanitizePost && count($_COOKIE) > 0))
    {
      $this->sanitizeRequest();
    }
  }

  public function sanitize($input = "")
  {
    if (!is_object($this->purifier))
    {
      $this->purifier = new CHtmlPurifier;
    }
    if (is_array($input))
    {
      foreach ($input as $i => $v)
      {
        if (is_array($v))
        {
          $input[$i] = $this->sanitize($v);
        }
        else
        {
          $input[$i] = $this->purifier->purify($v);
        }
      }
    }
    elseif(!is_numeric($input))
    {
      $input = $this->purifier->purify($input);
    }


    if ($this->linkNoFollow)
    {
      if ($this->linkNewWindow)
      {
        $input = str_ireplace("<a ","<a target='_blank' rel='nofollow' ",$input);
      }
      else
      {
        $input = str_ireplace("<a ","<a rel='nofollow' ",$input);
      }
    }
    else
    {
      if ($this->linkNewWindow)
      {
        $input = str_ireplace("<a ","<a target='_blank'",$input);
      }
    }
    return($input);
  }
  public function sanitizeRequest()
  {
    if ($this->sanitizeGet)
    {
      $_GET = $this->sanitize($_GET);
    }
    if ($this->sanitizePost)
    {
      $_POST = $this->sanitize($_POST);
    }
    if ($this->sanitizeCookie)
    {
      $_COOKIE = $this->sanitize($_COOKIE);
    }
    if ($this->sanitizeFiles)
    {
      $_FILES = $this->sanitize($_FILES);
    }
  }
}
?>
