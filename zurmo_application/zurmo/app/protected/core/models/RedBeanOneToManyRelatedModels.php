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

    $basePath = Yii::app()->getBasePath();
    require_once("$basePath/../../redbean/rb.php");

    /**
     * Relates models as RedBean links, so that
     * the relationship is 1:M via a foreign key.
     */
    class RedBeanOneToManyRelatedModels extends RedBeanMutableRelatedModels
    {
        /**
         * @var RedBeanModel
         */
        protected $relatedModel;

        /**
         * @var string
         */
        protected $relatedModelClassName;

        /**
         * @var string
         */
        protected $relatedAttributeName;

        /**
         * Store models that are getting removed when 'removeByIndex' is called. These are later used to process
         * events on those models for onRedBeanOneToManyRelatedModelChange
         * @var string
         */
        protected $deferredUnrelatedModels = array();

        /**
         * @var bool
         */
        protected $owns;

        /**
         * @see RedBeanModel::LINK_TYPE_ASSUMPTIVE
         * @see RedBeanModel::LINK_TYPE_SPECIFIC
         * @see RedBeanModel::LINK_TYPE_POLYMORPHIC
         * @var integer
         */
        protected $linkType;

        /**
         * @var null | string
         */
        protected $linkName;

        /**
         * Cached value when first retrieved
         * @var null | string
         */
        protected $opposingRelationName;

        protected $opposingRelationNameRetrieved = false;

        /**
         * Constructs a new RedBeanOneToManyRelatedModels. The models are retrieved lazily.
         * RedBeanOneToManyRelatedModels are only constructed with beans by the model.
         * Beans are never used by the application directly.
         */
        public function __construct(RedBeanModel $relatedModel, RedBean_OODBBean $bean, $modelClassName, $relatedModelClassName,
                                    $relatedAttributeName,
                                    $owns, $linkType, $linkName = null)
        {
            assert('is_string($modelClassName)');
            assert('is_string($relatedModelClassName)');
            assert('is_string($relatedAttributeName)');
            assert('$modelClassName        != ""');
            assert('$relatedModelClassName != ""');
            assert('$relatedAttributeName != ""');
            assert('is_bool($owns)');
            assert('is_int($linkType)');
            assert('is_string($linkName) || $linkName == null');
            assert('($linkType == RedBeanModel::LINK_TYPE_ASSUMPTIVE && $linkName == null) ||
                    ($linkType != RedBeanModel::LINK_TYPE_ASSUMPTIVE && $linkName != null)');
            $this->rewind();
            $this->relatedModel          = $relatedModel;
            $this->modelClassName        = $modelClassName;
            $this->relatedModelClassName = $relatedModelClassName;
            $this->relatedAttributeName  = $relatedAttributeName;
            $this->owns                  = $owns;
            $this->linkType              = $linkType;
            $this->linkName              = $linkName;
            $this->constructRelatedBeansAndModels($modelClassName, $bean);
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        /**
         * Handles constructing the relatedBeansAndModels with special attention to the case where it is PolyOneToMany
         * @param string $modelClassName
         * @param mixed $sqlOrBean
         */
        private function constructRelatedBeansAndModels($modelClassName, $sqlOrBean = '')
        {
            assert('is_string($sqlOrBean) || $sqlOrBean instanceof RedBean_OODBBean');
            $tableName = $modelClassName::getTableName();
            if (is_string($sqlOrBean))
            {
                $this->relatedBeansAndModels = array_values($beans = ZurmoRedBean::find($tableName, $sqlOrBean));
            }
            else
            {
                assert('$sqlOrBean instanceof RedBean_OODBBean');
                $this->bean = $sqlOrBean;
                try
                {
                    if ($this->bean->id > 0)
                    {
                        if ($this->linkType == RedBeanModel::LINK_TYPE_POLYMORPHIC)
                        {
                            $value           = array();
                            $values['id']    = $this->bean->id;
                            $values['type']  = $this->bean->getMeta('type');

                            $this->relatedBeansAndModels = array_values(ZurmoRedBean::find( $tableName,
                                                                    strtolower($this->linkName) . '_id = :id AND ' .
                                                                    strtolower($this->linkName) . '_type = :type',
                                                                    $values));
                        }
                        else
                        {
                            $relatedIds                  = ZurmoRedBeanLinkManager::getKeys($this->bean, $tableName,
                                                                                            $this->resolveLinkNameForCasing());
                            $this->relatedBeansAndModels = array_values(ZurmoRedBean::batch($tableName, $relatedIds));
                        }
                    }
                    else
                    {
                        $this->relatedBeansAndModels = array();
                    }
                }
                catch (RedBean_Exception_SQL $e)
                {
                    // SQLSTATE[42S02]: Base table or view not found...
                    // SQLSTATE[42S22]: Column not found...
                    if (!in_array($e->getSQLState(), array('42S02', '42S22')))
                    {
                        throw $e;
                    }
                    $this->relatedBeansAndModels = array();
                }
            }
        }

        public function save($runValidation = true)
        {
            if (!parent::save($runValidation))
            {
                return false;
            }
            foreach ($this->deferredRelateBeans as $bean)
            {
                if ($this->linkType == RedBeanModel::LINK_TYPE_POLYMORPHIC)
                {
                    if ($this->bean->id == null)
                    {
                        ZurmoRedBean::store($this->bean);
                    }
                    $polyIdFieldName          = strtolower($this->linkName) . '_id';
                    $polyTypeFieldName        = strtolower($this->linkName) . '_type';
                    $bean->$polyTypeFieldName = $this->bean->getMeta('type');
                    $bean->$polyIdFieldName   = $this->bean->id;
                }
                else
                {
                    ZurmoRedBeanLinkManager::link($bean, $this->bean, $this->resolveLinkNameForCasing());
                }
                ZurmoRedBean::store($bean);
            }
            $this->deferredRelateBeans = array();
            $relatedModelClassName = $this->relatedModelClassName;
            $tableName = $relatedModelClassName::getTableName();
            foreach ($this->deferredUnrelateBeans as $bean)
            {
                if (!$this->owns)
                {
                    if ($this->linkType == RedBeanModel::LINK_TYPE_POLYMORPHIC)
                    {
                        throw new NotSupportedException("Polymorphic relations can not be NOT_OWNED");
                    }
                    ZurmoRedBeanLinkManager::breakLink($bean, $tableName, $this->resolveLinkNameForCasing());
                    ZurmoRedBean::store($bean);
                }
                else
                {
                    ZurmoRedBean::trash($bean);
                }
            }
            $this->deferredUnrelatedBeans = array();
            foreach ($this->deferredUnrelatedModels as $model)
            {
                $event = new CModelEvent($model);
                $model->onRedBeanOneToManyRelatedModelsChange($event);
            }
            $this->deferredUnrelatedModels = array();
            return true;
        }

        protected function resolveLinkNameForCasing()
        {
            if ($this->linkName != null)
            {
                return strtolower($this->linkName);
            }
        }

        /**
         * Return an array of stringified values for each of the contained models.
         */
        public function getStringifiedData()
        {
            $data = null;
            foreach ($this as $containedModel)
            {
                if ($containedModel->id > 0)
                {
                    $data[] = strval($containedModel);
                }
            }
            return $data;
        }

        /**
         * Adds a related model. Override to support finding opposing relation and making adjustment
         * so when accessing the opposing model, it will reflect properly the changes to the relation.
         * Ignoring when the relations are for the same model, because there is currently an issue with this being supported
         * with group/groups. Eventually todo: fix that and we can open it up to relations with the same model.
         */
        public function add(RedBeanModel $model)
        {
            parent::add($model);
            if (get_class($model) != get_class($this->relatedModel) &&
               (null != $opposingRelationName = $this->getOpposingRelationName($model)))
            {
                   $model->$opposingRelationName = $this->relatedModel;
            }
        }

        /**
         * Unrelates a model by index. Override to support finding opposing relation and making adjustment
         * so when accessing the opposing model, it will reflect properly the changes to the relation.
         * Ignoring when the relations are for the same model, because there is currently an issue with this being supported
         * with group/groups. Eventually todo: fix that and we can open it up to relations with the same model.
         */
        public function removeByIndex($i)
        {
            $model = $this->getByIndex($i);
            parent::removeByIndex($i);
            if ((get_class($model) != get_class($this->relatedModel) &&
                null != $opposingRelationName = $this->getOpposingRelationName($model))
                    )
            {
                $model->$opposingRelationName    = null;
                $this->deferredUnrelatedModels[] = $model;
            }
        }

        protected function getOpposingRelationName($model)
        {
            if (!$this->opposingRelationNameRetrieved)
            {
                if (null !== $opposingRelationName = RedBeanModel::
                                                    getHasManyOpposingRelationName($model, $this->relatedModelClassName,
                                                                                   $this->relatedAttributeName))
                {
                    $this->opposingRelationName = $opposingRelationName;
                }
                $this->opposingRelationNameRetrieved = true;
            }
            return $this->opposingRelationName;
        }
    }
?>
