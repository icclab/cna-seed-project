<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for working with Starred Models
     */
    class StarredUtil
    {
        public static function modelHasStarredInterface($modelClassName, $reflectionClass = null)
        {
            if (!isset($reflectionClass))
            {
                $reflectionClass = new ReflectionClass($modelClassName);
            }
            return $reflectionClass->implementsInterface('StarredInterface');
        }

        public static function modelHasStarredInterfaceAndNotAbstract($modelClassName)
        {
            $reflectionClass = new ReflectionClass($modelClassName);
            return (static::modelHasStarredInterface($modelClassName, $reflectionClass) &&
                        !$reflectionClass->isAbstract());
        }

        public static function markModelAsStarred(RedBeanModel $model)
        {
            static::markModelAsStarredForUser(get_class($model),
                                              Yii::app()->user->userModel->id,
                                              $model->id);
        }

        protected static function markModelAsStarredForUser($modelClassName, $userId, $modelId)
        {
            static::ensureModelClassNameImplementsStarredInterface($modelClassName);
            if (static::isModelStarredForUser($modelClassName, $userId, $modelId))
            {
                return;
            }
            $starredModelClassName = static::getStarredModelClassName($modelClassName);
            $starredModelClassName::markModelAsStarredByUserIdAndModelId($userId, $modelId);
        }

        public static function unmarkModelAsStarred(RedBeanModel $model)
        {
            static::unmarkModelAsStarredForUser(get_class($model),
                                                Yii::app()->user->userModel->id,
                                                $model->id);
        }

        protected static function unmarkModelAsStarredForUser($modelClassName, $userId, $modelId)
        {
            static::ensureModelClassNameImplementsStarredInterface($modelClassName);
            if (!static::isModelStarredForUser($modelClassName, $userId, $modelId))
            {
                return;
            }
            $starredModelClassName = static::getStarredModelClassName($modelClassName);
            $starredModelClassName::unmarkModelAsStarredByUserIdAndModelId($userId, $modelId);
        }

        public static function isModelStarred(RedBeanModel $model)
        {
            return static::isModelStarredForUser(get_class($model),
                                                 Yii::app()->user->userModel->id,
                                                 $model->id);
        }

        protected static function isModelStarredForUser($modelClassName, $userId, $modelId)
        {
            static::ensureModelClassNameImplementsStarredInterface($modelClassName);
            $starredModelClassName = static::getStarredModelClassName($modelClassName);
            return (bool)$starredModelClassName::getCountByUserIdAndModelId($userId, $modelId);
        }

        public static function unmarkModelAsStarredForAllUsers(RedBeanModel $model)
        {
            $modelClassName = get_class($model);
            static::ensureModelClassNameImplementsStarredInterface($modelClassName);
            $modelId        = $model->id;
            $starredModelClassName = static::getStarredModelClassName($modelClassName);
            $starredModelClassName::unmarkModelAsStarredByUserIdAndModelId(null, $modelId);
        }

        public static function toggleModelStarStatus($modelClassName, $modelId)
        {
            $model = $modelClassName::getById($modelId);
            $isModelStarred = static::isModelStarred($model);
            if ($isModelStarred)
            {
                static::unmarkModelAsStarred($model);
            }
            else
            {
                static::markModelAsStarred($model);
            }
            if ($isModelStarred)
            {
                return 'icon-star unstarred';
            }
            return 'icon-star starred';
        }

        public static function getToggleStarStatusLink($data, $row)
        {
            $starredClass   = 'icon-star unstarred';
            $text           = 'w'; //w = Star in Icon-Font
            if (static::isModelStarred($data))
            {
                $starredClass = 'icon-star starred';
            }
            $starId = 'star-' . get_class($data) . '-' . $data->id;
            $link = ZurmoHtml::ajaxLink(
                        $text,
                        Yii::app()->createUrl('zurmo/default/toggleStar',
                                array('modelClassName' => get_class($data),
                                      'modelId'        => $data->id)),
                        array('success' => "function(data){\$('#{$starId}').removeClass().addClass(data)}"),
                        array('class'       => $starredClass,
                              'id'          => $starId,
                              'namespace'   => 'update'));
            return $link;
        }

        public static function renderToggleStarStatusLink($data, $row)
        {
            echo static::getToggleStarStatusLink($data, $row);
        }

        public static function getStarredModelClassName($modelClassName)
        {
            if (!StringUtil::endsWith($modelClassName, 'Starred'))
            {
                $modelClassName .= 'Starred';
            }
            return $modelClassName;
        }

        protected static function ensureModelClassNameImplementsStarredInterface($modelClassName)
        {
            if (!static::modelHasStarredInterface($modelClassName))
            {
                throw new NotSupportedException($modelClassName . " does not implement StarredInterface");
            }
        }
    }
?>