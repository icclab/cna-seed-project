<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    Yii::import('ext.cssIn.src.CSSIN');
    class ZurmoCssInUtil extends CSSIN
    {
        protected $combineStyleBlocks       = false;

        protected $moveStyleBlocksToBody    = false;

        /**
         * The HTML to process
         *
         * @var	string
         */
        protected $html;

        public function setCombineStyleBlock($combineStyleBlocks = true)
        {
            $this->combineStyleBlocks  = $combineStyleBlocks;
        }

        public function setMoveStyleBlocksToBody($moveStyleBlocksToBody = true)
        {
            $this->moveStyleBlocksToBody = $moveStyleBlocksToBody;
        }

        protected function moveStyleBlocks($html)
        {
            $this->html         = $html;
            $styles             = $this->resolveStyleBlockContent();
            $html               = $this->stripOriginalStyleTags($html);
            if ($this->moveStyleBlocksToBody)
            {
                return $this->combineAndMoveStylesToBody($styles, $html);
            }
            return $this->combineAndMoveStylesToHead($styles, $html);
        }

        protected function stripOriginalStyleTags($html)
        {
            return preg_replace('|<style(.*)>(.*)</style>|isU', '', $html);
        }

        protected function combineAndMoveStylesToBody($styles, $html)
        {
            $html           = $this->combineAndMoveStyles($styles, $html, false);
            return $html;
        }

        protected function combineAndMoveStylesToHead($styles, $html)
        {
            $html           = $this->combineAndMoveStyles($styles, $html, true);
            return $html;
        }

        protected function combineAndMoveStyles($styles, $html, $moveToHead)
        {
            $search     = 'body';
            if ($moveToHead)
            {
                $search = '/head';
            }
            $matches        = preg_split('#(<' . $search . '.*?>)#i', $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (count($matches) > 1)
            {
                if ($moveToHead)
                {
                    $styles     = $styles . $matches[1];
                }
                else
                {
                    $styles     = $matches[1] . $styles;
                }
                $html           = $matches[0] . $styles . $matches[2];
            }
            return $html;
        }

        protected function resolveStyleBlockContent()
        {
            $html               = $this->html;
            $matches            = array();
            preg_match_all('|<style(.*)>(.*)</style>|isU', $html, $matches);
            if ($this->combineStyleBlocks)
            {
                $styleBlockContent  = implode(PHP_EOL, $matches[2]);
                $style              = ZurmoHtml::tag('style', array(), $styleBlockContent);
            }
            else
            {
                $style              = implode(PHP_EOL, $matches[0]);
            }
            return $style;
        }

        function inlineCSS($url, $contents = null)
        {
            // Download the HTML if it was not provided
            if ($contents === null)
            {
                $html = file_get_html($url, false, null, -1, -1, true, true, DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
            }
            // Else use the data provided!
            else
            {
                $html = str_get_html($contents, true, true, DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
            }

            if (!is_object($html))
            {
                return false;
            }

            $cloneNodesArray = array();
            foreach ($html->nodes as $node)
            {
                $cloneNodesArray[] = clone $node;
            }

            $css_urls = array();

            // Find all stylesheets and determine their absolute URLs to retrieve them
            foreach ($html->find('link[rel="stylesheet"]') as $style)
            {
                $css_urls[] = self::absolutify($url, $style->href);
                $style->outertext = '';
            }

            $css_blocks = $this->processStylesCleanup($html);

            $raw_css = '';
            if (!empty($css_urls))
            {
                $raw_css .= $this->getCSSFromFiles($css_urls);
            }
            if (!empty($css_blocks))
            {
                $raw_css .= $css_blocks;
            }

            // Get the CSS rules by decreasing order of specificity.
            // This is an array with, amongst other things, the keys 'properties', which hold the CSS properties
            // and the 'selector', which holds the CSS selector
            $rules = $this->parseCSS($raw_css);

            // We loop over each rule by increasing order of specificity, find the nodes matching the selector
            // and apply the CSS properties
            foreach ($rules as $rule)
            {
                foreach ($html->find($rule['selector']) as $node)
                {
                    // Unserialize the style array, merge the rule's CSS into it...
                    $style = array_merge(self::styleToArray($node->style), $rule['properties']);
                    // And put the CSS back as a string!
                    $node->style = self::arrayToStyle($style);
                }
            }

            // Now a tricky part: do a second pass with only stuff marked !important
            // because !important properties do not care about specificity, except when fighting
            // agains another !important property
            foreach ($rules as $rule)
            {
                foreach ($rule['properties'] as $key => $value)
                {
                    if (strpos($value, '!important') !== false)
                    {
                        foreach ($html->find($rule['selector']) as $node)
                        {
                            $style = self::styleToArray($node->style);
                            $style[$key] = $value;
                        }
                    }
                }
            }

            foreach ($html->nodes as $index => $node)
            {
                $nodeStyle = self::styleToArray($node->style);
                $cloneNodeStyle = self::styleToArray($cloneNodesArray[$index]->style);
                $style = $this->mergeStyles(self::styleToArray($node->style), self::styleToArray($cloneNodesArray[$index]->style));
                $style = self::arrayToStyle($style);
                if ($style != '')
                {
                    $node->style = $style;
                }
            }
            // Let simple_html_dom give us back our HTML with inline CSS!
            $html = $this->moveStyleBlocks((string)$html);
            return $html;
        }

        protected function mergeStyles(Array $firstStyle, Array $secondStyle)
        {
            $stylesToRemove = array();
            foreach ($secondStyle as $styleTag => $value)
            {
                $matches = array();
                preg_match('#(.*)-(.*)#i', $styleTag, $matches);
                if (isset($matches[1]))
                {
                    $stylesToRemove[] = $matches[1];
                }
            }
            $stylesToAddInTheBegining = array();
            foreach ($stylesToRemove as $styleRoRemove)
            {
                if (isset($firstStyle[$styleRoRemove]))
                {
                    $stylesToAddInTheBegining[$styleRoRemove] = $firstStyle[$styleRoRemove];
                    unset($firstStyle[$styleRoRemove]);
                }
            }
            $mergeArray = array_merge($firstStyle, $secondStyle);
            return array_merge($stylesToAddInTheBegining, $mergeArray);
        }

        protected function processStylesCleanup($html)
        {
            $css_blocks = '';
            // Find all <style> blocks and cut styles from them (leaving media queries)
            foreach ($html->find('style') as $style)
            {
                list($_css_to_parse, $_css_to_keep) = self::splitMediaQueries($style->innertext());
                $css_blocks .= $_css_to_parse;
            }
            return $css_blocks;
        }

        public static function calculateCSSSpecifity($selector)
        {
            // cleanup selector
            $selector = str_replace(array('>', '+'), array(' > ', ' + '), $selector); // Not Coding Standard

            // init var
            $specifity = 0;

            // split the selector into chunks based on spaces
            $chunks = explode(' ', $selector);

            // loop chunks
            foreach ($chunks as $chunk)
            {
                // an ID is important, so give it a high specifity
                if (strstr($chunk, '#') !== false) $specifity += 100;

                // classes are more important than a tag, but less important then an ID
                elseif (strstr($chunk, '.')) $specifity += 10;

                // anything else isn't that important
                else $specifity += 1;
            }

            // return
            return $specifity;
        }

        public function parseCSS($text)
        {
            $css  = new \csstidy();
            $css->parse($text);
            $rules      = array();
            $position   = 0;

            foreach ($css->css as $declarations)
            {
                foreach ($declarations as $selectors => $properties)
                {
                    foreach (explode(",", $selectors) as $selector) // Not Coding Standard
                    {
                        $rules[] = array(
                            'position'      => $position,
                            'specificity'   => self::calculateCSSSpecifity($selector),
                            'selector'      => $selector,
                            'properties'    => $properties
                        );
                    }

                    $position += 1;
                }
            }

            usort($rules, function($a, $b)
            {
                if ($a['specificity'] > $b['specificity'])
                {
                    return 1;
                }
                elseif ($a['specificity'] < $b['specificity'])
                {
                    return -1;
                }
                else
                {
                    if ($a['position'] > $b['position'])
                    {
                        return 1;
                    }
                    else
                    {
                        return -1;
                    }
                }
            });

            return $rules;
        }

        public static function splitMediaQueries($css)
        {
            // Remove CSS-Comments
            $css = preg_replace('/\/\*.*?\*\//ms', '', $css);
            return parent::splitMediaQueries(' ' . $css . ' ');
        }
    }
?>