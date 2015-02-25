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

    Yii::import('zii.widgets.CMenu');

    /**
     * MbMenu class file.
     *
     * @author Mark van den Broek (mark@heyhoo.nl)
     * @copyright Copyright &copy; 2010 HeyHoo
     *
     */
    class MbMenu extends CMenu
    {
        private $baseUrl;

        protected $themeUrl;

        protected $theme;

        protected $cssFile;

        protected $cssIeStylesFile = null;

        private $nljs;

        public $activateParents    = true;

        public $navContainerClass  = 'nav-container';

        public $navBarClass        = 'nav-bar';

        public $labelPrefix        = null;

        public $labelPrefixOptions = array();

        public $linkPrefix         = null;

        /**
         * The javascript needed.
         */
        protected function createJsCode()
        {
            $js  = '';
            $js .= '  $(".nav li").hover('                   . $this->nljs;
            $js .= '    function () {'                       . $this->nljs; // Not Coding Standard
            $js .= '      if ($(this).hasClass("parent")) {' . $this->nljs; // Not Coding Standard
            $js .= '        $(this).addClass("over");'       . $this->nljs;
            $js .= '      }'                                 . $this->nljs;
            $js .= '    },'                                  . $this->nljs; // Not Coding Standard
            $js .= '    function () {'                       . $this->nljs; // Not Coding Standard
            $js .= '      $(this).removeClass("over");'      . $this->nljs;
            $js .= '    }'                                   . $this->nljs;
            $js .= '  );'                                    . $this->nljs;
            return $js;
        }

        /**
        * Give the last items css 'last' style.
        */
        protected function cssLastItems($items)
        {
            $i = max(array_keys($items));
            $item = $items[$i];
            if (isset($item['itemOptions']['class']))
            {
                $items[$i]['itemOptions']['class'] .= ' last';
            }
            else
            {
                $items[$i]['itemOptions']['class'] = 'last';
            }
            foreach ($items as $i => $item)
            {
                if (isset($item['items']))
                {
                    $items[$i]['items'] = $this->cssLastItems($item['items']);
                }
            }
            return array_values($items);
        }

        /**
        * Give the last items css 'parent' style.
        */
        protected function cssParentItems($items)
        {
            foreach ($items as $i => $item)
            {
                if (isset($item['items']))
                {
                    if (isset($item['itemOptions']['class']))
                    {
                        $items[$i]['itemOptions']['class'] .= ' parent';
                    }
                    else
                    {
                        $items[$i]['itemOptions']['class'] = 'parent';
                    }
                    $items[$i]['items'] = $this->cssParentItems($item['items']);
                }
            }
            return array_values($items);
        }

        /**
        * Initialize the widget.
        */
        public function init()
        {
            if (!$this->getId(false))
            {
                $this->setId('nav');
            }
            $this->themeUrl = Yii::app()->themeManager->baseUrl;
            $this->theme = Yii::app()->theme->name;
            $this->nljs = "\n";
            $this->items = $this->cssParentItems($this->items);
            $this->items = $this->cssLastItems($this->items);
            $route = $this->getController()->getRoute();
            $hasActiveChild = null;
            $this->items = $this->normalizeItems(
                $this->items,
                $this->getController()->getRoute(),
                $hasActiveChild
            );
            $this->resolveNavigationClass();
        }

        /**
        * Registers the external javascript files.
        */
        public function registerClientScripts()
        {
            // add the script
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');
            $js = $this->createJsCode();
            $cs->registerScript('mbmenu_' . $this->getId(), $js, CClientScript::POS_READY);
        }

        public function registerCssFile()
        {
            $cs = Yii::app()->getClientScript();
            if ($this->cssFile != null)
            {
                $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssFile, 'screen');
            }
            if (Yii::app()->browser->getName() == 'msie' && Yii::app()->browser->getVersion() < 8 && $this->cssIeStylesFile != null)
            {
                $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssIeStylesFile, 'screen');
            }
        }

        protected function renderMenuRecursive($items)
        {
            foreach ($items as $item)
            {
                $liCloseTag        = null;
                if ($this->doRenderMenuHeader($item))
                {
                    $liOptions      = $this->resolveItemOptions($item);
                    $liOpenTag      = ZurmoHtml::openTag('li', $liOptions);
                    $menuItem       = $this->renderMenuItem($item);
                    $liCloseTag     = ZurmoHtml::closeTag('li') . "\n";
                    echo $liOpenTag;
                    echo $menuItem;
                }
                if ($this->doRenderSubMenu($item))
                {
                    $this->renderSubMenu($item);
                }
                echo $liCloseTag;
            }
        }

        protected function resolveItemOptions(array $item)
        {
            $liOptions  = array();
            if (isset($item['itemOptions']))
            {
                $liOptions  =  $item['itemOptions'];
            }
            return $liOptions;
        }

        protected function resolveHtmlOptions(array $item)
        {
            $htmlOptions = array();
            if (isset($item['linkOptions']))
            {
                $htmlOptions = $item['linkOptions'];
            }
            return $htmlOptions;
        }

        protected function resolveLabelContent(array $item)
        {
            $label      = $item['label'];
            if (isset($item['labelSpanHtmlOptions']))
            {
                $labelSpanHtmlOptions = $item['labelSpanHtmlOptions'];
            }
            else
            {
                $labelSpanHtmlOptions = array();
            }
            $content    = $this->renderLabelPrefix() . ZurmoHtml::tag('span', $labelSpanHtmlOptions,  $label);
            return $content . $this->resolveAndGetSpanAndDynamicLabelContent($item);
        }

        protected function renderMenuItem($item)
        {
            $htmlOptions            = $this->resolveHtmlOptions($item);
            $resolvedLabelContent   = $this->resolveLabelContent($item);
            if ((isset($item['ajaxLinkOptions'])))
            {
                return ZurmoHtml::ajaxLink($resolvedLabelContent, $item['url'], $item['ajaxLinkOptions'], $htmlOptions);
            }
            elseif (isset($item['url']))
            {
                return ZurmoHtml::link($this->renderLinkPrefix() . $resolvedLabelContent, $item['url'], $htmlOptions);
            }
            else
            {
                return $this->renderMenuItemWithNoURLSpecified($resolvedLabelContent, $htmlOptions, $item);
            }
        }

        protected function renderMenuItemWithNoURLSpecified($resolvedLabelContent, array $htmlOptions, array $item)
        {
            return ZurmoHtml::link($resolvedLabelContent, "javascript:void(0);", $htmlOptions);
        }

        protected function renderSubMenu(array $item)
        {
            $nestedUlOpen   = null;
            $nestedUlClose  = null;
            if ($this->doRenderMenuHeader($item))
            {
                // only nest it if we rendered the header, no point in nesting it if we didn't.
                $nestedUlOpen   = "\n" . ZurmoHtml::openTag('ul', $this->submenuHtmlOptions) . "\n";
                $nestedUlClose  = ZurmoHtml::closeTag('ul') . "\n";
            }
            echo $nestedUlOpen;
            $this->renderMenuRecursive($item['items']);
            echo $nestedUlClose;
        }

        protected function resolveAndGetSpanAndDynamicLabelContent(array $item)
        {
            if (isset($item['dynamicLabelContent']))
            {
                return $item['dynamicLabelContent'];
            }
        }

        protected function resolveNavigationClass()
        {
            if (isset($this->htmlOptions['class']))
            {
                $this->htmlOptions['class'] .= ' nav';
            }
            else
            {
                $this->htmlOptions['class'] = 'nav';
            }
        }

        protected function normalizeItems($items, $route, &$active, $ischild = 0)
        {
            foreach ($items as $i => $item)
            {
                if (isset($item['visible']) && !$item['visible'])
                {
                    unset($items[$i]);
                    continue;
                }
                if ($this->encodeLabel)
                {
                    $items[$i]['label'] = Yii::app()->format->text($item['label']);
                }
                $hasActiveChild = false;
                if (isset($item['items']))
                {
                    $items[$i]['items'] = $this->normalizeItems($item['items'], $route, $hasActiveChild, 1);
                    if (empty($items[$i]['items']) && $this->hideEmptyItems)
                    {
                        unset($items[$i]['items']);
                    }
                }
                if (!isset($item['active']))
                {
                    if (($this->activateParents && $hasActiveChild) || $this->isItemActive($item, $route))
                    {
                        $active = $items[$i]['active'] = true;
                    }
                    else
                    {
                        $items[$i]['active'] = false;
                    }
                }
                elseif ($item['active'])
                {
                    $active = true;
                }
                if ($items[$i]['active'] && $this->activeCssClass != '')
                {
                    if (isset($item['itemOptions']['class']))
                    {
                        $items[$i]['itemOptions']['class'] .= ' ' . $this->activeCssClass;
                    }
                    else
                    {
                        $items[$i]['itemOptions']['class'] = $this->activeCssClass;
                    }
                }
            }
            return array_values($items);
        }

        protected function renderLabelPrefix()
        {
            if ($this->labelPrefix)
            {
                return ZurmoHtml::tag($this->labelPrefix, $this->labelPrefixOptions, '');
            }
        }

        protected function renderLinkPrefix()
        {
            if ($this->linkPrefix)
            {
                return ZurmoHtml::tag($this->linkPrefix, array(), '');
            }
        }

        protected function doRenderMenuHeader(array $item)
        {
            return true;
        }

        protected function doRenderSubMenu(array $item)
        {
            return (isset($item['items']) && count($item['items']));
        }

        /**
        * Run the widget.
        */
        public function run()
        {
            $this->registerClientScripts();
            $this->registerCssFile();
            parent::run();
        }
    }
?>
