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

    /**
     * Jquery multi-select auto complete based on Jquery Token input script.
     * http://loopj.com/jquery-tokeninput/
     */
    class MultiSelectAutoComplete extends ZurmoWidget
    {
        public $scriptFile = 'jquery.tokeninput.js';

        public $cssFile = null;

        /**
         * Input field name.
         * @var string
         */
        public $name;

        /**
         * Action url to use for the search results.
         * @var string
         */
        public $sourceUrl;

        public $htmlOptions;

        /**
         * The text to show in the dropdown label which appears when you first click in the search field. default:
         * Type a search term
         */
        public $hintText;

        /**
         * Callback function when an item is added
         * @var string
         */
        public $onAdd;

        /**
         * Callback function when an item is deleted
         * @var string
         */
        public $onDelete;

        /**
         * Property to search in the response, defaults to name
         * @var string
         */
        public $propertyToSearch = 'name';

        /**
         * Parameter to populate the outgoing query with
         * @var string
         */
        public $queryParam = 'term';

        /**
         * The delay, in milliseconds, between the user finishing typing and the search being performed.
         * @var int
         */
        public $searchDelay = 300;

        /**
         * The minimum number of characters the user must enter before a search is performed.
         * @var int
         */
        public $minChars = 1;

        /**
         * The maximum number of results shown in the drop down. Use null to show all the matching results.
         * @var string
         */
        public $resultsLimit = 'null';

        /**
         * Prevent user from selecting duplicate values by setting this to true.
         * @var string
         */
        public $preventDuplicates = 'true';

        /**
         * Prepopulate the tokeninput with existing data. Set to an array of JSON objects,
         * eg: [{id: 3, name: "John Smith", id: 5, name: "Jill Smith"}] to pre-fill the input. default: null
         */
        public $jsonEncodedIdsAndLabels;

        public function run()
        {
            $id = $this->getId();
            $this->htmlOptions['id'] = $id;
            if (isset($this->htmlOptions['disabled']) && $this->htmlOptions['disabled'] == 'disabled')
            {
                $tokenListClassSuffix = ' disabled';
            }
            else
            {
                $tokenListClassSuffix = '';
            }
            echo ZurmoHtml::textField($this->name, null, $this->htmlOptions);
            $javaScript  = "$(document).ready(function () { ";
            $javaScript .= "$('#$id').tokenInput('{$this->sourceUrl}', { ";
            $javaScript .= "queryParam: '" . $this->queryParam . "',"; // Not Coding Standard
            if ($this->hintText != null)
            {
                $javaScript .= "hintText: '" . Yii::app()->format->text($this->hintText) . "',"; // Not Coding Standard
            }
            if ($this->onAdd != null)
            {
                $javaScript .= "onAdd: " . $this->onAdd . ","; // Not Coding Standard
            }
            if ($this->onDelete != null)
            {
                $javaScript .= "onDelete: " . $this->onDelete . ","; // Not Coding Standard
            }
            if ($this->jsonEncodedIdsAndLabels != null)
            {
                $javaScript .= "prePopulate: " . $this->jsonEncodedIdsAndLabels . ","; // Not Coding Standard
            }
            // Begin Not Coding Standard
            $javaScript .= "propertyToSearch: '" . $this->propertyToSearch . "',";
            $javaScript .= "searchDelay: " . $this->searchDelay . ",";
            $javaScript .= "minChars: " . $this->minChars . ",";
            $javaScript .= "preventDuplicates: '" . $this->preventDuplicates . "',";
            $javaScript .= "resultsLimit: '" . $this->resultsLimit . "',";
            $javaScript .= "classes: {tokenList: 'token-input-list" . $tokenListClassSuffix . "'}";
            $javaScript .= "});";
            $javaScript .= "});";
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, $javaScript);
        }

        /**
         * Determine the package installation path.
         * This method will identify the JavaScript root URL and theme root URL.
         * If they are not explicitly specified, it will publish the included JUI package
         * and use that to resolve the needed paths.
         */
        protected function resolvePackagePath()
        {
            if ($this->scriptUrl === null || $this->themeUrl === null)
            {
                $cs = Yii::app()->getClientScript();
                if ($this->scriptUrl === null)
                {
                    $this->scriptUrl = Yii::app()->getAssetManager()->publish(
                                        Yii::getPathOfAlias('application.extensions.juitokeninput.assets'));
                }
            }
        }
    }
