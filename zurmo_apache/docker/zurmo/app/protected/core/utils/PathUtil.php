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

    abstract class PathUtil
    {
        public static function getAllClassNamesByPathAlias($alias)
        {
            assert('is_string($alias)');
            try
            {
                // not using default value to save cpu cycles on requests that follow the first exception.
                $classNames = GeneralCache::getEntry($alias . '.ClassNames');
            }
            catch (NotFoundException $e)
            {
                $classNames = array();
                $pathOfAlias = Yii::getPathOfAlias($alias . '.*');
                if (is_dir($pathOfAlias))
                {
                    $directoryFiles = ZurmoFileHelper::findFiles($pathOfAlias);
                    $classNames = array();
                    foreach ($directoryFiles as $filePath)
                    {
                        $filePathInfo = pathinfo($filePath);
                        if ($filePathInfo['extension'] == 'php')
                        {
                            $classNames[] = $filePathInfo['filename'];
                        }
                    }
                }
                GeneralCache::cacheEntry($alias, $classNames);
            }
            return $classNames;
        }

        public static function getAllPrimaryModelClassNames($filter = null)
        {
            try
            {
                $allPrimaryModelClasses = GeneralCache::getEntry('allPrimaryModelClassNames');
            }
            catch (NotFoundException $e)
            {
                $allPrimaryModelClasses             = array();
                $modules                            = Module::getModuleObjects();
                foreach ($modules as $module)
                {
                    $modelClass = $module::getPrimaryModelName();
                    if (!empty($modelClasses))
                    {
                        $allPrimaryModelClasses[] = $modelClass;
                    }
                }
                GeneralCache::cacheEntry('allPrimaryModelClassNames', $allPrimaryModelClasses);
            }
            if ($filter && is_callable($filter))
            {
                $allPrimaryModelClasses = array_filter($allPrimaryModelClasses, $filter);
            }
            $allPrimaryModelClasses = array_unique($allPrimaryModelClasses);
            return $allPrimaryModelClasses;
        }

        public static function getAllModelClassNames($filter = null)
        {
            try
            {
                $allModelClasses = GeneralCache::getEntry('allModelClassNames');
            }
            catch (NotFoundException $e)
            {
                $allModelClasses            = array();
                $nonModuleModelPathAliases  = Yii::app()->additionalModelsConfig->resolvePathAliases();
                $modules                    = Module::getModuleObjects();
                foreach ($modules as $module)
                {
                    $modelClasses = $module::getModelClassNames();
                    if (!empty($modelClasses))
                    {
                        $allModelClasses = CMap::mergeArray($allModelClasses, array_values($modelClasses));
                    }
                }
                foreach ($nonModuleModelPathAliases as $alias)
                {
                    $models             = array_values(static::getAllClassNamesByPathAlias($alias));
                    $allModelClasses    = CMap::mergeArray($allModelClasses, $models);
                }
                GeneralCache::cacheEntry('allModelClassNames', $allModelClasses);
            }
            if ($filter && is_callable($filter))
            {
                $allModelClasses = array_filter($allModelClasses, $filter);
            }
            $allModelClasses = array_unique(array_values($allModelClasses));
            return $allModelClasses;
        }

        public static function getAllCanHaveBeanModelClassNames()
        {
            return static::getAllModelClassNamesWithFilterFromCache('canHaveBeanModelClassNames',
                                                                    'static::filterCanHaveBeanModels');
        }

        public static function getAllReadSubscriptionModelClassNames()
        {
            return static::getAllModelClassNamesWithFilterFromCache('readPermissionsSubscriptionModelClassNames',
                                                                    'static::filterReadSubscriptionModels');
        }

        public static function getAllMungableModelClassNames()
        {
            return static::getAllModelClassNamesWithFilterFromCache('mungableModelClassNames',
                                                                    'static::filterMungableModels');
        }

        public static function getAllEmailTemplateElementClassNames($filter = null)
        {
            $emailTemplatePathAliases  = 'application.modules.emailTemplates.elements';
            $elements   = array_values(static::getAllClassNamesByPathAlias($emailTemplatePathAliases));
            if ($filter && is_callable($filter))
            {
                $elements = array_filter($elements, $filter);
            }
            $elements = array_unique(array_values($elements));
            return $elements;
        }

        public static function getAllUIAccessibleBuilderElementClassNames()
        {
            return static::getAllEmailTemplateElementClassNamesWithFilterFromCache(
                                                                'uiAccessibleBuilderElementClassNames',
                                                                'static::filterUIAccessibleBuilderElementClassNames');
        }

        protected static function getAllModelClassNamesWithFilterFromCache($identifier, $filter)
        {
            try
            {
                $filteredModelClassNames = GeneralCache::getEntry($identifier);
            }
            catch (NotFoundException $e)
            {
                $filteredModelClassNames = static::getAllModelClassNames($filter);
                GeneralCache::cacheEntry($identifier, $filteredModelClassNames);
            }
            return $filteredModelClassNames;
        }

        protected static function filterCanHaveBeanModels($model)
        {
            return (is_subclass_of($model, 'RedBeanModel') && $model::getCanHaveBean());
        }

        protected static function filterReadSubscriptionModels($model)
        {
            return (is_subclass_of($model, 'OwnedSecurableItem') && $model::hasReadPermissionsSubscriptionOptimization());
        }

        protected static function filterMungableModels($model)
        {
            return (is_subclass_of($model, 'SecurableItem') && $model::hasReadPermissionsOptimization());
        }

        protected static function getAllEmailTemplateElementClassNamesWithFilterFromCache($identifier, $filter)
        {
            try
            {
                $filteredElementClassNames = GeneralCache::getEntry($identifier);
            }
            catch (NotFoundException $e)
            {
                $filteredElementClassNames = static::getAllEmailTemplateElementClassNames($filter);
                GeneralCache::cacheEntry($identifier, $filteredElementClassNames);
            }
            return $filteredElementClassNames;
        }

        protected static function filterUIAccessibleBuilderElementClassNames($className)
        {
            return (is_subclass_of($className, 'BaseBuilderElement') && $className::isUIAccessible());
        }
    }
?>