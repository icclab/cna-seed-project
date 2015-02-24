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
     * Class ImageFileModel
     * Used to store public accessible images
     */
    class ImageFileModel extends FileModel
    {
        const DEFAULT_THUMBNAIL_HEIGHT = 30;
        const DEFAULT_THUMBNAIL_WIDTH  = 65;
        const THUMB_FILE_NAME_PREFIX   = 'thumb_';
        const FILE_NAME_SEPARATOR      = '_';

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'isShared',
                    'width',
                    'height',
                    'inactive'
                ),
                'rules' => array(
                    array('isShared', 'boolean'),
                    array('isShared', 'default', 'value' => false),
                    array('width',    'type',    'type' => 'integer'),
                    array('height',   'type',    'type' => 'integer'),
                    array('inactive', 'boolean'),
                    array('inactive', 'default', 'value' => false),
                ),
            );
            return $metadata;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'isShared'  => Zurmo::t('ZurmoModule', 'Shared',    array(), null, $language),
                    'width'     => Zurmo::t('Core',        'Width',     array(), null, $language),
                    'height'    => Zurmo::t('Core',        'Height',    array(), null, $language),
                    'inactive'  => Zurmo::t('ZurmoModule', 'Inactive',  array(), null, $language),
                )
            );
        }

        /**
         * Get the model by the fileName
         * @param $fileName The filename of the model
         * @return The model
         * @throws NotSupportedException
         */
        public static function getByFileName($fileName)
        {
            return static::getById(static::getIdByFileName($fileName));
        }

        /**
         * Get the id based on the cached model file name
         * @param $fileName The fileName of the model
         * @return int The model id
         * @throws NotFoundException
         */
        protected static function getIdByFileName($fileName)
        {
            $matches = array();
            $pattern = '/^(\d+)' . static::FILE_NAME_SEPARATOR . '/'; // Not Coding Standard
            preg_match($pattern, $fileName, $matches);
            if (count($matches) == 2)
            {
                return (int) $matches[1];
            }
            else
            {
                throw new NotFoundException();
            }
        }

        /**
         * Get the cached model file path
         * @param bool $shouldGetThumbnail True if we should return the thumbnail
         * @return string The path where the cached model is stored
         */
        public function getImageCachePath($shouldGetThumbnail = false)
        {
            if ($shouldGetThumbnail)
            {
                return static::getPathToCachedFiles() . static::THUMB_FILE_NAME_PREFIX . $this->getImageCacheFileName();
            }
            return static::getPathToCachedFiles() . $this->getImageCacheFileName();
        }

        /**
         * Get the cached model file path by the file name of the cached model
         * @param $fileName The filename of the cached model
         * @param $shouldReturnThumbnail True if we should return the thumbnail
         * @return string The path where the cached model is stored
         */
        public static function getImageCachePathByFileName($fileName, $shouldReturnThumbnail)
        {
            assert('is_string($fileName)');
            assert('is_bool($shouldReturnThumbnail)');
            if ($shouldReturnThumbnail)
            {
                $fileName = static::THUMB_FILE_NAME_PREFIX . $fileName;
            }
            return static::getPathToCachedFiles() . $fileName;
        }

        /**
         * Get the path to the directory where we should store cached models
         * @return string
         */
        protected static function getPathToCachedFiles()
        {
            return Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR;
        }

        /**
         * Get the cache model file name
         * @return string The file name
         */
        public function getImageCacheFileName()
        {
            return $this->id . static::FILE_NAME_SEPARATOR . $this->name;
        }

        /**
         * Caches the model in filesystem
         * @param bool $shouldCreateThumbnail True if we want to create the cache for thumbnail
         */
        public function createImageCache($shouldCreateThumbnail = false)
        {
            $this->createCacheDirIfNotExists();
            if (!$this->isImageCached($shouldCreateThumbnail))
            {
                $this->cacheImage($shouldCreateThumbnail);
            }
        }

        /**
         * Create the filesystem dir where cached models will be stored
         */
        protected function createCacheDirIfNotExists()
        {
            if (!is_dir(Yii::getPathOfAlias('application.runtime.uploads')))
            {
                mkdir(Yii::getPathOfAlias('application.runtime.uploads'), 0755, true); // set recursive flag and permissions 0755
            }
        }

        /**
         * Check if the model has cache created on the filesystem
         * @param bool $checkThumbnail True if we are chechink the cached thumbail
         * @return bool True if the cached model is cached
         */
        protected function isImageCached($checkThumbnail = false)
        {
            $imageCachePath = $this->getImageCachePath($checkThumbnail);
            return file_exists($imageCachePath);
        }

        /**
         * Cache the model on the filesystem
         * @param $shouldCacheThumbnail True to create the cache for thumbnail of the model
         */
        protected function cacheImage($shouldCacheThumbnail)
        {
            $imageCachePath = $this->getImageCachePath($shouldCacheThumbnail);
            if ($shouldCacheThumbnail)
            {
                $newWidth  = static::DEFAULT_THUMBNAIL_WIDTH;
                $newHeight = static::DEFAULT_THUMBNAIL_HEIGHT;
                WideImage::load($this->fileContent->content)->resize($newWidth, $newHeight)->saveToFile($imageCachePath);
            }
            else
            {
                file_put_contents($imageCachePath, $this->fileContent->content);
            }
        }

        public function fileTypeValidator($attribute, $params)
        {
            if ($this->type == 'image/png' ||
                 $this->type == 'image/jpg' ||
                 $this->type == 'image/gif' ||
                 $this->type == 'image/jpeg')
            {
                return true;
            }
            else
            {
                $this->addError($attribute, Zurmo::t('ZurmoModule', 'File type is not valid.'));
                return false;
            }
        }

        public static function getModuleClassName()
        {
            return 'ImagesModule';
        }

        public function toggle($attribute)
        {
            if ($this->isToggleable($attribute))
            {
                $this->{$attribute} = !$this->{$attribute};
                $this->save();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function isToggleable($attribute)
        {
            if (Yii::app()->user->userModel->isSame($this->createdByUser) && $attribute == 'isShared')
            {
                return true;
            }
            return false;
        }

        public function canDelete()
        {
            if (Yii::app()->user->userModel->isSame($this->createdByUser))
            {
                return true;
            }
            return false;
        }

        public function isEditableByCurrentUser()
        {
            return (Yii::app()->user->userModel->isSame($this->createdByUser) || $this->isShared);
        }
    }
?>