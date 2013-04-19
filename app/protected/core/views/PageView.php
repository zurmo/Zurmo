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
        /**
         * Flags that the error handler was called.
         */
        public static $foundErrors = false;

        public static $xhtmlValidationErrors = array();

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
            static::registerAllPagesScriptFiles();
            $content = $this->renderXHtmlStart()     .
                       $this->renderXHtmlHead()      .
                       $this->renderXHtmlBodyStart() .
                       parent::render()              .
                       $this->renderXHtmlBodyEnd()   .
                       $this->renderXHtmlEnd();
            Yii::app()->getClientScript()->render($content);
            $performanceMessage = null;
            if (YII_DEBUG && SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
            {
                $endTime = microtime(true);
                $performanceMessage .= 'Page render time: ' . number_format(($endTime - $startTime), 3) . ' seconds.<br />';
            }
            if (YII_DEBUG)
            {
                if (defined('XHTML_VALIDATION') && XHTML_VALIDATION)
                {
                    $this->validate($content);
                    if (!empty(self::$xhtmlValidationErrors))
                    {
                        foreach (self::$xhtmlValidationErrors as $error)
                        {
                            $content = $this->appendContentBeforeXHtmlBodyEndAndXHtmlEnd($content, $error);
                        }
                    }
                }
                if (SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
                {
                    $endTime      = microtime(true);
                    $endTotalTime = Yii::app()->performance->endClockAndGet();
                    if (defined('XHTML_VALIDATION') && XHTML_VALIDATION)
                    {
                        $performanceMessage .= '<span>Total page view time including validation: ' . number_format(($endTime - $startTime), 3) . ' seconds.</span><br />';
                    }
                    else
                    {
                        $performanceMessage .= '<span>Total page view time: ' . number_format(($endTime - $startTime), 3) . ' seconds.</span><br />';
                    }
                    $performanceMessage .= '<span>Total page time: ' . number_format(($endTotalTime), 3) . ' seconds.</span><br />';
                }
            }
            else
            {
                if (SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
                {
                    $endTime      = microtime(true);
                    $endTotalTime = Yii::app()->performance->endClockAndGet();
                    $performanceMessage .= 'Load time: ' . number_format(($endTotalTime), 3) . ' seconds.<br />';
                }
            }
            if (SHOW_PERFORMANCE && Yii::app()->isApplicationInstalled())
            {
                if (SHOW_QUERY_DATA)
                {
                    $performanceMessage .= self::makeShowQueryDataContent();
                }
                foreach (Yii::app()->performance->getTimings() as $id => $time)
                {
                    $performanceMessage .= 'Timing: ' . $id . ' total time: ' . number_format(($time), 3) . "</br>";
                }
                $performanceMessageHtml = '<div class="performance-info">' . $performanceMessage . '</div>';
                $content = $this->appendContentBeforeXHtmlBodyEndAndXHtmlEnd($content, $performanceMessageHtml);
            }
            if (YII_DEBUG && Yii::app()->isApplicationInstalled())
            {
                $dbInfoHtml = '<span style="background-color: lightgreen; color: green">Database: \'' . Yii::app()->db->connectionString . '\', username: \'' . Yii::app()->db->username . '\'.</span><br />';
                $content = $this->appendContentBeforeXHtmlBodyEndAndXHtmlEnd($content, $dbInfoHtml);
            }
            return $content;
        }

        /**
         * Validates the page content against the XHTML schema
         * and writes the problems directly to output in bright
         * red on yellow. Is public for access by unit tests.
         */
        public static function validate($content)
        {
            $wrapper = '<span style="background-color: yellow; color: #c00000;"><b>{text}</b></span><br />';
            $valid = false;
            try
            {
                $xhtmlValidationErrors = W3CValidatorServiceUtil::validate($content);

                if (count($xhtmlValidationErrors))
                {
                    foreach ($xhtmlValidationErrors as $xhtmlValidationError)
                    {
                        self::$xhtmlValidationErrors[] = str_replace('{text}', $xhtmlValidationError, $wrapper);
                    }
                }
                else
                {
                    $valid = true;
                }

                if (!count(self::$xhtmlValidationErrors))
                {
                    $valid = true;
                }
            }
            catch (Exception $e)
            {
                self::$xhtmlValidationErrors[] = str_replace('{text}', 'Error accessing W3C validation service.', $wrapper);
                self::$xhtmlValidationErrors[] = str_replace('{text}', $e->getMessage(), $wrapper);
            }
            return $valid;
        }

        /**
         * Error handler that writes the errors directly to
         * output in bright red on yellow.
         */
        public static function schemeValidationErrorHandler($errno, $errstr, $errfile, $errline)
        {
            static $first = true;

            if ($first)
            {
                self::$xhtmlValidationErrors[] = '<span style="background-color: yellow; color: #c00000;"><b>THIS IS NOT A VALID XHTML FILE</b></span><br />';
                $first = false;
            }
            self::$xhtmlValidationErrors[] = "<span style=\"background-color: yellow; color: #c00000;\">$errstr</span><br />";

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
            $themeUrl = Yii::app()->baseUrl . '/themes';
            $theme    = Yii::app()->theme->name;
            if (!MINIFY_SCRIPTS && Yii::app()->isApplicationInstalled())
            {
                Yii::app()->clientScript->registerScriptFile(
                    Yii::app()->getAssetManager()->publish(
                        Yii::getPathOfAlias('application.core.views.assets')) . '/less-1.2.0.min.js');
            }
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/ZurmoDialog.js');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/interactions.js');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/mobile-interactions.js');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/jquery.truncateText.js');
            return '<!DOCTYPE html>' .
                   '<!--[if IE 8]><html class="ie8" lang="en"><![endif]-->' .
                   '<!--[if gt IE 8]><!--><html lang="en"><!--<![endif]-->';
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
            //$cs->registerMetaTag('UTF-8', null, 'charset'); // Not Coding Standard
            $cs->registerCssFile(Yii::app()->baseUrl . '/' . $theme . '/css/keyframes.css');

            $specialCssContent = null;
            if (!MINIFY_SCRIPTS && Yii::app()->isApplicationInstalled())
            {
                $specialCssContent .= '<link rel="stylesheet/less" type="text/css" id="newui" href="' .
                                      Yii::app()->baseUrl . '/' . $theme . '/less/newui.less"/>';
                if (Yii::app()->userInterface->isMobile())
                {
                    $specialCssContent .= '<link rel="stylesheet/less" type="text/css" id="mobile" href="' .
                        Yii::app()->baseUrl . '/' . $theme . '/less/mobile.less"/>';
                }
                $specialCssContent .= '<!--[if lt IE 9]><link rel="stylesheet/less" type="text/css" href="' .
                                      Yii::app()->baseUrl . '/' . $theme . '/less/ie.less"/><![endif]-->';
            }
            else
            {
                $cs->registerCssFile(Yii::app()->baseUrl . '/' . $theme . '/css/newui.css');
                if (Yii::app()->userInterface->isMobile())
                {
                    $cs->registerCssFile(Yii::app()->baseUrl . '/' . $theme . '/css/mobile.css');
                }
            }
            if (MINIFY_SCRIPTS)
            {
                Yii::app()->minScript->generateScriptMap('css');
                if (!YII_DEBUG && !defined('IS_TEST'))
                {
                    Yii::app()->minScript->generateScriptMap('js');
                }
            }
            if (Yii::app()->browser->getName() == 'msie' && Yii::app()->browser->getVersion() < 9)
            {
                $cs->registerCssFile(Yii::app()->baseUrl . '/' . $theme . '/css' . '/ie.css', 'screen, projection');
            }

            foreach ($this->getStyles() as $style)
            {
                if ($style != 'ie')
                {
                    if (file_exists("$theme/css/$style.css"))
                    {
                        $cs->registerCssFile(Yii::app()->baseUrl . '/' . $theme . '/css/' . $style. '.css'); // Not Coding Standard
                    }
                }
            }

            if (file_exists("$theme/ico/favicon.ico"))
            {
                $cs->registerLinkTag('shortcut icon', null, Yii::app()->baseUrl . '/' . $theme . '/ico/favicon.ico');
            }
            else
            {
                $cs->registerLinkTag('shortcut icon', null, Yii::app()->baseUrl . '/' . $defaultTheme . '/ico/favicon.ico');
            }
            return '<head>' .
                   '<meta charset="utf-8">' .
                   '<meta http-equiv="X-UA-Compatible" content="IE=edge" />' . // Not Coding Standard
                   '<meta name="viewport"  content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">' . // Not Coding Standard
                   '<meta name="apple-mobile-web-app-capable" content="yes" />' . // Not Coding Standard
                   '<link rel="apple-touch-icon" sizes="144x144" href="/themes/default/images/touch-icon-iphone4.png" />'  . //also add 57px, 72px, 144px // Not Coding Standard
                   $specialCssContent .
                   '<title>' . $title . '</title>'  .
                   '</head>';
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
            $classContent      = Yii::app()->themeManager->getActiveThemeColor();
            $backgroundTexture = Yii::app()->themeManager->getActiveBackgroundTexture();
            if ($backgroundTexture != null)
            {
                $classContent .= ' ' . $backgroundTexture;
            }
            if (Yii::app()->userInterface->isMobile())
            {
                $classContent .= ' mobile-app';
            }
            return '<body class="' . $classContent . '">';
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

        public static function makeShowQueryDataContent()
        {
            $performanceMessage  = static::getTotalAndDuplicateQueryCountContent();
            $duplicateData = Yii::app()->performance->getRedBeanQueryLogger()->getDuplicateQueriesData();
            if (count($duplicateData) > 0)
            {
                $performanceMessage .= '</br></br>' . 'Duplicate Queries:' . '</br>';
            }
            foreach ($duplicateData as $query => $count)
            {
                $performanceMessage .= 'Count: ' . $count . '&#160;&#160;&#160;Query: ' . $query . '</br>';
            }
            return $performanceMessage;
        }

        public static function getTotalAndDuplicateQueryCountContent()
        {
            $performanceMessage  = 'Total/Duplicate Queries: ' . Yii::app()->performance->getRedBeanQueryLogger()->getQueriesCount();
            $performanceMessage .= '/'   . Yii::app()->performance->getRedBeanQueryLogger()->getDuplicateQueriesCount();
            return $performanceMessage;
        }

        public static function makeNonHtmlDuplicateCountAndQueryContent()
        {
            $content = null;
            $duplicateData = Yii::app()->performance->getRedBeanQueryLogger()->getDuplicateQueriesData();
            foreach ($duplicateData as $query => $count)
            {
                $content .= 'Count: ' . $count . '--Query: ' . $query . "\n";
            }
            return $content;
        }

        /**
         * Register into clientScript->scriptFiles any scripts that should load on all pages
         * @see getScriptFilesThatLoadOnAllPages
         */
        public static function registerAllPagesScriptFiles()
        {
            Yii::app()->clientScript->registerCoreScript('jquery');
            Yii::app()->clientScript->registerCoreScript('jquery.ui');
        }

        /**
         * @return array of script files that are loaded on all pages @see registerAllPagesScriptFiles
         */
        public static function getScriptFilesThatLoadOnAllPages()
        {
            //When debug is on, the application never minifies
            $scriptData = array();
            if (MINIFY_SCRIPTS && !YII_DEBUG)
            {
                foreach (Yii::app()->minScript->usingAjaxShouldNotIncludeJsPathAliasesAndFileNames as $data)
                {
                   $scriptData[] = Yii::app()->getAssetManager()->getPublishedUrl(Yii::getPathOfAlias($data[0])) . $data[1];
                }
            }
            return $scriptData;
        }

        /**
         * Add additional html conent before html body end("</body>") tag and html end tag ("</html>")
         * @param string $content
         * @param string $additionalContent
         * @return string
         */
        public function appendContentBeforeXHtmlBodyEndAndXHtmlEnd($content, $additionalContent)
        {
            $content = str_replace($this->renderXHtmlBodyEnd() . $this->renderXHtmlEnd() ,
                                   $additionalContent . $this->renderXHtmlBodyEnd() . $this->renderXHtmlEnd(),
                                   $content );
            return $content;
        }
    }
?>
