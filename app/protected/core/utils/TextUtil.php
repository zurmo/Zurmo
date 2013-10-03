<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Text Util contain text related helper functions
     */
    class TextUtil
    {
        /**
         * Convert string to lowercase, based on encoding
         * @param string $string
         * @return string
         */
        public static function strToLowerWithDefaultEncoding($string)
        {
            assert('is_string($string)');
            return mb_strtolower($string, Yii::app()->charset);
        }

        /**
         * @param string $text
         * @return string
         */
        public static function textWithUrlToTextWithLink($text)
        {
            assert('is_string($text)');
            // Begin Not Coding Standard
            $rexProtocol  = '(https?://)?';
            $rexDomain    = '(?:[-a-zA-Z0-9]{1,63}\.)+[a-zA-Z][-a-zA-Z0-9]{1,62}';
            $rexIp        = '(?:[1-9][0-9]{0,2}\.|0\.){3}(?:[1-9][0-9]{0,2}|0)';
            $rexPort      = '(:[0-9]{1,5})?';
            $rexPath      = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
            $rexQuery     = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
            $rexFragment  = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
            $rexUsername  = '[^]\\\\\x00-\x20\"(),:-<>[\x7f-\xff]{1,64}';
            $rexPassword  = $rexUsername;
            $rexUrl       = "$rexProtocol(?:($rexUsername)(:$rexPassword)?@)?($rexDomain|$rexIp)($rexPort$rexPath$rexQuery$rexFragment)";
            $rexUrlLinker = "{\\b$rexUrl(?=[?.!,;:\"]?(\s|$))}";
            // End Not Coding Standard
            $html = '';

            $position = 0;
            $validTlds = array_fill_keys(explode(" ", ".ac .ad .ae .aero .af .ag .ai .al .am .an .ao .aq .ar .arpa .as .asia .at .au .aw .ax .az .ba .bb " .
                                                      ".bd .be .bf .bg .bh .bi .biz .bj .bm .bn .bo .br .bs .bt .bv .bw .by .bz .ca .cat .cc .cd .cf .cg " .
                                                      ".ch .ci .ck .cl .cm .cn .co .com .coop .cr .cu .cv .cx .cy .cz .de .dj .dk .dm .do .dz .ec .edu .ee " .
                                                      ".eg .er .es .et .eu .fi .fj .fk .fm .fo .fr .ga .gb .gd .ge .gf .gg .gh .gi .gl .gm .gn .gov .gp .gq " .
                                                      ".gr .gs .gt .gu .gw .gy .hk .hm .hn .hr .ht .hu .id .ie .il .im .in .info .int .io .iq .ir .is .it .je ".
                                                      ".jm .jo .jobs .jp .ke .kg .kh .ki .km .kn .kp .kr .kw .ky .kz .la .lb .lc .li .lk .lr .ls .lt .lu .lv " .
                                                      ".ly .ma .mc .md .me .mg .mh .mil .mk .ml .mm .mn .mo .mobi .mp .mq .mr .ms .mt .mu .museum .mv .mw .mx " .
                                                      ".my .mz .na .name .nc .ne .net .nf .ng .ni .nl .no .np .nr .nu .nz .om .org .pa .pe .pf .pg .ph .pk .pl " .
                                                      ".pm .pn .pr .pro .ps .pt .pw .py .qa .re .ro .rs .ru .rw .sa .sb .sc .sd .se .sg .sh .si .sj .sk .sl .sm " .
                                                      ".sn .so .sr .st .su .sv .sy .sz .tc .td .tel .tf .tg .th .tj .tk .tl .tm .tn .to .tp .tr .travel .tt .tv " .
                                                      ".tw .tz .ua .ug .uk .us .uy .uz .va .vc .ve .vg .vi .vn .vu .wf .ws "), true);

            while (preg_match($rexUrlLinker, $text, $match, PREG_OFFSET_CAPTURE, $position))
            {
                list($url, $urlPosition) = $match[0];

                // Add the text leading up to the URL.
                $html .= substr($text, $position, $urlPosition - $position);

                $protocol    = $match[1][0];
                $username    = $match[2][0];
                $password    = $match[3][0];
                $domain      = $match[4][0];
                $afterDomain = $match[5][0]; // everything following the domain
                $port        = $match[6][0];
                $path        = $match[7][0];

                // Check that the TLD is valid or that $domain is an IP address.
                $tld = strtolower(strrchr($domain, '.'));
                if (preg_match('{^\.[0-9]{1,3}$}', $tld) || isset($validTlds[$tld])) // Not Coding Standard
                {
                    // Do not permit implicit protocol if a password is specified, as
                    // this causes too many errors (e.g. "my email:foo@example.org").
                    if (!$protocol && $password)
                    {
                        $html .= $username;

                        // Continue text parsing at the ':' following the "username".
                        $position = $urlPosition + strlen($username);
                        continue;
                    }

                    if (!$protocol && $username && !$password && !$afterDomain)
                    {
                        // Looks like an email address.
                        $completeUrl = "mailto:$url";
                        $linkText = $url;
                    }
                    else
                    {
                        // Prepend http:// if no protocol specified
                        $completeUrl = $protocol ? $url : "http://$url";
                        $linkText = "$domain$port$path";
                    }

                    $linkHtml = '<a href="' . $completeUrl . '">'
                        . $linkText
                        . '</a>';

                    // Cheap e-mail obfuscation to trick the dumbest mail harvesters.
                    $linkHtml = str_replace('@', '@', $linkHtml);

                    // Add the hyperlink.
                    $html .= $linkHtml;
                }
                else
                {
                    // Not a valid URL.
                    $html .= $url;
                }

                // Continue text parsing from after the URL.
                $position = $urlPosition + strlen($url);
            }
            // Add the remainder of the text.
            $html .= substr($text, $position);
            return $html;
        }
    }
?>
