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

    /**
     * The view that forms the basis of every page. It renders
     * the XHtml html, header, body, etc, and renders its contained
     * view within the body. After rending the page and before
     * returning it it validates the XHtml against the XHtml schema
     * and renders directly to the browser any errors it finds
     * before returning the rendered page to the caller.
     */
    class PageView extends View
    {
        private $containedView;

        /**
         * Constructs the page view specifying the view that it
         * will contain.
         */
        public function __construct(View $containedView)
        {
            $this->containedView = $containedView;
        }

        public function render()
        {
            if (SHOW_PERFORMANCE)
            {
                $startTime = microtime(true);
            }
            $content = $this->renderXHtmlStart()     .
                       $this->renderXHtmlHead()      .
                       $this->renderXHtmlBodyStart() .
                       parent::render()              .
                       $this->renderXHtmlBodyEnd()   .
                       $this->renderXHtmlEnd();
            Yii::app()->getClientScript()->render($content);
            $performanceMessage = null;
            if (YII_DEBUG && SHOW_PERFORMANCE)
            {
                $endTime = microtime(true);
                $performanceMessage .= 'Page render time: ' . number_format(($endTime - $startTime), 3) . ' seconds.<br />';
            }
            if (YII_DEBUG)
            {
                if ($this->validate($content))
                {
                    $content = $this->tidy($content);
                    $content .= '<div class="xhtml-validation-info">Page is valid XHTML and has been tidied.</div>';
                }
                else
                {
                    echo '<span style="background-color: yellow; color: #c00000">Skipping tidy so that the line numbers in the error messages match the source, (if there are any).</span><br />';
                }
                if (SHOW_PERFORMANCE)
                {
                    $endTime      = microtime(true);
                    $endTotalTime = Yii::app()->performance->endClockAndGet();
                    $performanceMessage .= 'Total page view time including validation and tidy: ' . number_format(($endTime - $startTime), 3) . ' seconds.</span><br />';
                    $performanceMessage .= 'Total page time: ' . number_format(($endTotalTime), 3) . ' seconds.</span><br />';
                }
            }
            else
            {
                if (SHOW_PERFORMANCE)
                {
                    $endTime      = microtime(true);
                    $endTotalTime = Yii::app()->performance->endClockAndGet();
                    $performanceMessage .= 'Load time: ' . number_format(($endTotalTime), 3) . ' seconds.<br />';
                }
            }
            if (SHOW_PERFORMANCE)
            {
                foreach (Yii::app()->performance->getTimings() as $id => $time)
                {
                    $performanceMessage .= 'Timing: ' . $id . ' total time: ' . number_format(($time), 3) . "</br>";
                }
                $content .= '<div class="performance-info">' . $performanceMessage . '</div>';
            }
            if (YII_DEBUG && Yii::app()->isApplicationInstalled())
            {
                $content .= '<span style="background-color: lightgreen; color: green">Database: \'' . Yii::app()->db->connectionString . '\', username: \'' . Yii::app()->db->username . '\'.</span><br />';
            }
            return $content;
        }

        /**
         * Tidies the page content as Xhtml with numerical
         * entities. In YII_DEBUG mode the source is indented.
         * @see http://php.net/manual/en/book.tidy.php
         */
        protected static function tidy($content)
        {
            $tidy = new Tidy();
            $tidyConfig = array(
                'accessibility-check' => 3,
                'indent'              => defined('YII_DEBUG'),
                'newline'             => 'LF',
                'numeric-entities'    => true,
                'output-xhtml'        => true,
                'quote-ampersand'     => false,
                'wrap'                => 0,
            );
            $tidy->parseString($content, $tidyConfig);
            $content = $tidy->root()->value;
            return $content;
        }

        /**
         * Validates the page content against the XHTML schema
         * and writes the problems directly to output in bright
         * red on yellow. Is public for access by unit tests.
         */
        public static function validate($content)
        {
            $valid = false;

            set_error_handler(array('PageView', 'schemeValidationErrorHandler'));
            $domDocument = new DomDocument();
            $xHtmlDtd = str_replace('\\', '/', dirname(__FILE__)) . '/../resources/xhtml1-transitional.dtd';

            $document = new DOMDocument();
            $document->loadXML($content);
            $rootNode = $document->getElementsByTagName('html')->item(0);

            if ($rootNode !== null && !self::$foundErrors)
            {
                $implementation = new DOMImplementation();
                $documentType         = $implementation->createDocumentType('html', null, $xHtmlDtd);
                $documentWithLocalDtd = $implementation->createDocument(null, null, $documentType);
                $documentWithLocalDtd->encoding = "utf-8";
                $rootNodeWithLocalDtd = $documentWithLocalDtd->importNode($rootNode, true);
                $documentWithLocalDtd->appendChild($rootNodeWithLocalDtd);
                $valid = $documentWithLocalDtd->validate() && !self::$foundErrors;
            }
            else
            {
                echo '<span style="background-color: yellow; color: #c00000">Loading found errors, skipping validation.</span><br />';
            }

            restore_error_handler();

            return $valid;
        }

        /**
         * Flags that the error handler was called.
         */
        public static $foundErrors = false;

        /**
         * Error handler that writes the errors directly to
         * output in bright red on yellow.
         */
        public static function schemeValidationErrorHandler($errno, $errstr, $errfile, $errline)
        {
            static $first = true;

            if ($first)
            {
                echo '<span style="background-color: yellow; color: #c00000;"><b>THIS IS NOT A VALID XHTML FILE</b></span><br />';
                $first = false;
            }
            echo "<span style=\"background-color: yellow; color: #c00000;\">$errstr</span><br />";

            self::$foundErrors = true;
        }

        protected function renderContent()
        {
            return $this->containedView->render();
        }

        /**
         * Renders the xml declaration, doctype, and the html start tag.
         */
        protected function renderXHtmlStart()
        {
            return '<?xml version="1.0" encoding="utf-8"?>'.
                   '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
                   '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
        }

        /**
         * Renders the XHtml header element containing the title
         * and the default stylesheets screen, print, and ie. Additional
         * stylesheets can be specified by overriding getStyles() in
         * the extending class.
         */
        protected function renderXHtmlHead()
        {
            $title    = Yii::app()->format->text(trim($this->getTitle()));
            $subtitle = Yii::app()->format->text(trim($this->getSubtitle()));
            if ($subtitle != '')
            {
                $title = "$title - $subtitle";
            }
            $defaultTheme = 'themes/default';
            $theme        = 'themes/' . Yii::app()->theme->name;
            $cs = Yii::app()->getClientScript();
            $cs->registerMetaTag('text/html; charset=UTF-8', null, 'Content-Type'); // Not Coding Standard
            $cs->registerCssFile(Yii::app()->baseUrl . '/' . $defaultTheme . '/css' . '/screen.css', 'screen, projection');
            $cs->registerCssFile(Yii::app()->baseUrl . '/' . $defaultTheme . '/css' . '/print.css', 'print');
            if (Yii::app()->browser->getName() == 'msie' && Yii::app()->browser->getVersion() < 8)
            {
                $cs->registerCssFile(Yii::app()->baseUrl . '/' . $defaultTheme . '/css' . '/ie.css', 'screen, projection');
            }
            $cs->registerCssFile(Yii::app()->baseUrl . '/' . $defaultTheme . '/css' . '/theme.css', 'screen, projection');
            foreach ($this->getStyles() as $style)
            {
                $cs->registerCssFile(Yii::app()->baseUrl . '/' . $defaultTheme . '/css' . '/' . $style. '.css'); // Not Coding Standard
                if ($theme != $defaultTheme && file_exists("$theme/css/$style.css"))
                {
                    $cs->registerCssFile(Yii::app()->baseUrl . '/' . $theme . '/css' . '/' . $style. '.css'); // Not Coding Standard
                }
            }
            if (file_exists("$theme/ico/favicon.ico"))
            {
                $cs->registerLinkTag('shortcut icon', null, $theme . '/ico/favicon.ico');
            }
            else
            {
                $cs->registerLinkTag('shortcut icon', null, $defaultTheme . '/ico/favicon.ico');
            }
            return '<head>'                                                                 .
                   "<title>$title</title>"                                                  .
                   '</head>';
        }

        /**
         * Returns the application title. Can be overridden in the extending class.
         */
        protected function getTitle()
        {
            return '';
        }

        /**
         * Returns the application subtitle. Can be overridden in the extending class.
         */
        protected function getSubtitle()
        {
            return '';
        }

        /**
         * Returns an empty array of styles, being the names of stylesheets
         * without a css extention. Can be overridden in the extending class
         * to specify stylesheets additional to those rendered by default.
         * @see renderXHtmlHead()
         */
        protected function getStyles()
        {
            return array();
        }

        /**
         * Renders the body start tag.
         */
        protected function renderXHtmlBodyStart()
        {
            return '<body>';
        }

        /**
         * Renders the body end tag.
         */
        protected function renderXHtmlBodyEnd()
        {
            return '</body>';
        }

        /**
         * Renders the html end tag.
         */
        protected function renderXHtmlEnd()
        {
            return '</html>';
        }
    }
?>
