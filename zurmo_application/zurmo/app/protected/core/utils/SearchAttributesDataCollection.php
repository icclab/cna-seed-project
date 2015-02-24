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
     * Base class for managing the source of search attributes.  Attributes can be coming from a $_GET, a $_POST or
     * potentially a model as a saved search.
     */
    class SearchAttributesDataCollection
    {
        protected $sourceData;

        protected $model;

        public function __construct($model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            $this->model = $model;
        }

        public function getModel()
        {
            return $this->model;
        }

        public function setSourceData($sourceData)
        {
            assert('is_array($sourceData)');
            $this->sourceData = $sourceData;
        }

        public function getSourceData()
        {
            if (isset($this->sourceData))
            {
                return $this->sourceData;
            }
            return $_GET;
        }

        public function getDynamicSearchAttributes()
        {
            $dynamicSearchAttributes = SearchUtil::
                                        getDynamicSearchAttributesFromArray(get_class($this->model), $this->getSourceData());
            if ($dynamicSearchAttributes == null)
            {
                return array();
            }
            return $dynamicSearchAttributes;
        }

        public function getSanitizedDynamicSearchAttributes()
        {
            $dynamicSearchAttributes = SearchUtil::
                                        getDynamicSearchAttributesFromArray(get_class($this->model), $this->getSourceData());
            if ($dynamicSearchAttributes == null)
            {
                return array();
            }
            return SearchUtil::
                   sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($this->model, $dynamicSearchAttributes);
        }

        public function getDynamicStructure()
        {
            return SearchUtil::getDynamicSearchStructureFromArray(get_class($this->model), $this->getSourceData());
        }

        public function getAnyMixedAttributesScopeFromModel()
        {
            return $this->model->getAnyMixedAttributesScope();
        }

        public function getSelectedListAttributesFromModel()
        {
            if ($this->model->getListAttributesSelector() != null)
            {
                return $this->model->getListAttributesSelector()->getSelected();
            }
        }

        public function getFilterByStarred()
        {
            return SearchUtil::getFilterByStarredFromArray(get_class($this->model), $this->getSourceData());
        }

        public function getFilteredBy()
        {
            return SearchUtil::getFilteredByFromArray(get_class($this->model), $this->getSourceData());
        }

        public function hasKanbanBoard()
        {
            if ($this->model->getKanbanBoard() == null)
            {
                return false;
            }
            return true;
        }

        public function getKanbanBoard()
        {
            return $this->model->getKanbanBoard();
        }

        public function shouldClearStickyForKanbanBoard()
        {
            if ($this->model->getKanbanBoard() == null)
            {
                throw new NotSupportedException();
            }
            elseif ($this->model->getKanbanBoard()->getClearSticky())
            {
                return true;
            }
            return false;
        }

        public function getKanbanBoardGroupByAttributeVisibleValuesFromModel()
        {
            if ($this->model->getKanbanBoard() != null)
            {
                return $this->model->getKanbanBoard()->getGroupByAttributeVisibleValues();
            }
        }

        public function getKanbanBoardSelectedThemeFromModel()
        {
            if ($this->model->getKanbanBoard() != null)
            {
                return $this->model->getKanbanBoard()->getSelectedTheme();
            }
        }

        public function resolveSearchAttributesFromSourceData()
        {
            return SearchUtil::resolveSearchAttributesFromArray(get_class($this->model),
                                                                get_class($this->model),
                                                                $this->getSourceData());
        }

        public function resolveAnyMixedAttributesScopeForSearchModelFromSourceData()
        {
            return SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromArray($this->model,
                                                                                     get_class($this->model),
                                                                                     $this->getSourceData());
        }

        public function resolveSelectedListAttributesForSearchModelFromSourceData()
        {
            return SearchUtil::resolveSelectedListAttributesForSearchModelFromArray($this->model,
                                                                                    get_class($this->model),
                                                                                    $this->getSourceData());
        }

        public function resolveKanbanBoardOptionsForSearchModelFromSourceData()
        {
            return KanbanBoard::resolveKanbanBoardOptionsForSearchModelFromArray($this->model,
                                                                                 get_class($this->model),
                                                                                 $this->getSourceData());
        }

        public function resolveSortAttributeFromSourceData($name)
        {
            assert('is_string($name)');
            $sortAttribute = SearchUtil::resolveSortAttributeFromArray($name, $this->getSourceData());
            if ($sortAttribute == null)
            {
                if (!empty($this->model->sortAttribute))
                {
                    $sortAttribute = $this->model->sortAttribute;
                }
                else
                {
                    $sortAttribute = null;
                }
            }

            return $sortAttribute;
        }

        public function resolveSortDescendingFromSourceData($name)
        {
            assert('is_string($name)');
            $sortDescending =  SearchUtil::resolveSortDescendingFromArray($name, $this->getSourceData());
            if (!isset($sortDescending))
            {
                if (!empty($this->model->sortDescending))
                {
                    $sortDescending = true;
                }
                else
                {
                    $sortDescending = false;
                }
            }
            return $sortDescending;
        }

        public function resolveFilterByStarredFromSourceData()
        {
            SearchUtil::resolveFilterByStarredFromArray($this->model,
                                                        get_class($this->model),
                                                        $this->getSourceData());
        }

        public function resolveFilteredByFromSourceData()
        {
            SearchUtil::resolveFilteredByFromArray($this->model,
                                                   get_class($this->model),
                                                   $this->getSourceData());
        }
    }
?>
