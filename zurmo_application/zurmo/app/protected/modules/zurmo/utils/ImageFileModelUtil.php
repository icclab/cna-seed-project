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
     * Helper class to deal with @see ImageFileModel
     */
    class ImageFileModelUtil
    {
        /**
         * Reads image or thumb if it exists on cache.
         * If it don't exists cache it from the ImageFileModel first
         * @param $fileName The filename of image
         * @param bool $shouldGetThumbnail True if we want the thumbnail of the image
         */
        public static function readImageFromCache($fileName, $shouldGetThumbnail = false)
        {
            assert('is_string($fileName)');
            assert('is_bool($shouldGetThumbnail)');
            $imagePath = ImageFileModel::getImageCachePathByFileName($fileName, $shouldGetThumbnail);
            if (!file_exists($imagePath))
            {
                $imageFileModel = ImageFileModel::getByFileName($fileName);
                $imageFileModel->createImageCache($shouldGetThumbnail);
            }
            $mime               = ZurmoFileHelper::getMimeType($imagePath);
            $size               = filesize($imagePath);
            $name               = pathinfo($imagePath, PATHINFO_FILENAME);
            header('Content-Type: '     .   $mime);
            header('Content-Length: '   .   $size);
            header('Content-Name: '     .   $name);
            readfile($imagePath);
            Yii::app()->end(0, false);
        }

        public static function getUrlForActionUpload()
        {
            return Yii::app()->createAbsoluteUrl('zurmo/imageModel/upload');
        }

        public static function getUrlForActionGetUploaded()
        {
            return Yii::app()->createAbsoluteUrl('zurmo/imageModel/getUploaded');
        }

        public static function getUrlForGetImageFromImageFileName($fileName, $shouldReturnForThumbnail = false)
        {
            assert('is_string($fileName)');
            assert('is_bool($shouldReturnForThumbnail)');
            $path = 'zurmo/imageModel/getImage';
            if ($shouldReturnForThumbnail)
            {
                $path = 'zurmo/imageModel/getThumb';
            }
            return Yii::app()->createAbsoluteUrl($path, array('fileName' => $fileName));
        }

        public static function getImageSummary(ImageFileModel $imageFileModel, $layout = null)
        {
            $data = array();
            if ($layout == null)
            {
                $layout = static::getDefaultLayout();
            }
            $imagePath = ImageFileModel::getImageCachePathByFileName($imageFileModel->getImageCacheFileName(), false);
            if (!file_exists($imagePath))
            {
                $imageFileModel->createImageCache();
            }
            static::resolveWidthAndHeightAttributesIfTheyAreMissing($imageFileModel);
            $url                   = static::getUrlForGetImageFromImageFileName($imageFileModel->getImageCacheFileName(), true);
            $urlForPreview         = Yii::app()->createAbsoluteUrl('zurmo/imageModel/modalPreview', array('fileName' => $imageFileModel->getImageCacheFileName()));
            $data['{image}']       = ZurmoHtml::image($url, '', array('data-url' => $urlForPreview));
            $data['{name}']        = $imageFileModel->name;
            $data['{size}']        = FileModelDisplayUtil::convertSizeToHumanReadableAndGet((int) $imageFileModel->size);
            $data['{dimensions}']  = $imageFileModel->width . ' × ' . $imageFileModel->height;
            $data['{creator}']     = $imageFileModel->createdByUser;
            $data['{createdTime}'] = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($imageFileModel->createdDateTime);
            $data['{selectLink}']  = static::getSelectLink();
            $data['{editLink}']    = static::getEditLink();
            return strtr($layout, $data);
        }

        protected static function resolveWidthAndHeightAttributesIfTheyAreMissing(ImageFileModel $imageFileModel)
        {
            if ($imageFileModel->width == null && $imageFileModel->height == null)
            {
                $imageProperties        = getimagesize($imageFileModel->getImageCachePath());
                $imageFileModel->width  = $imageProperties[0];
                $imageFileModel->height = $imageProperties[1];
                $imageFileModel->save();
            }
        }

        protected static function getEditLink()
        {
            $editText = Zurmo::t('Core', 'Edit');
            return static::getLink($editText, ImageElement::IMAGE_EDIT_LINK_CLASS_NAME, 'simple-link');
        }

        protected static function getSelectLink()
        {
            $linkText = Zurmo::t('ZurmoModule', 'Change');
            return static::getLink($linkText, ImageElement::IMAGE_SELECT_LINK_CLASS_NAME, 'simple-link');
        }

        protected static function resolveInsertLink($imageFileModel)
        {
            $summary = static::getImageSummary($imageFileModel);
            return "javascript:parent.transferModalImageValues({$imageFileModel->id}, '{$summary}')";
        }

        public static function getLink($linkText, $class, $type)
        {
            assert('is_string($type)');
            if ($type == 'simple-link')
            {
                $content = ZurmoHtml::link($linkText, '#', array('class' => 'simple-link ' . $class));
            }
            else
            {
                $content = ZurmoHtml::link(
                    '<span class="z-spinner"></span>' . ZurmoHtml::tag( 'span', array( 'class' => 'z-label' ), $linkText ),
                    '#',
                    array( 'class' => 'secondary-button ' . $class ) );
            }
            return $content;
        }

        protected static function getDefaultLayout()
        {
            $createdByLabel = Zurmo::t('ZurmoModule', 'Created by');
            $onLabel        = Zurmo::t('ZurmoModule', 'on');
            return '<div class="builder-uploaded-image-thumb">{image}</div>'.
                   '<div class="image-links">{selectLink} · {editLink}</div>'.
                   '<div class="builder-image-details">' .
                   '<strong>{name}</strong><br />{size} · {dimensions} · ' . $createdByLabel .
                   ' {creator} ' . $onLabel . ' {createdTime}</div>';
        }

        public static function getImageFileNameWithDimensions($imageFileName, $width, $height)
        {
            assert('is_string($imageFileName)');
            assert('is_int($width)');
            assert('is_int($height)');
            $imageFileName = preg_replace("/^\d.*x\d.*\s/", "", $imageFileName);
            $imageFileName = $width . 'x' . $height . ' ' . $imageFileName;
            return $imageFileName;
        }

        public static function getImageFromHtmlImgTag($htmlContent)
        {
            assert('is_string($htmlContent)');
            $matches = array();
            preg_match("/<img.*src=[\"'](.*)[\"']/i", $htmlContent, $matches); // Not Coding Standard
            $url = $matches[1];
            $matches = array();
            if (preg_match("/\?fileName\=(.*)/i", $url, $matches) == 1) // Not Coding Standard
            {
                $imageFileModel         = ImageFileModel::getByFileName($matches[1]);
                return $imageFileModel;
            }
            else
            {
                $params  = LabelUtil::getTranslationParamsForAllModules();
                $icon    = ZurmoHtml::tag('i', array('class' => 'icon-notice'), '');
                $message =  Zurmo::t('ZurmoModule',
                    'Due to recent improvements in the Zurmo email template builder, you are required ' .
                    'to re-import the image from the external URL. The image currently in use is located ' .
                    'here {url}. You can also select from an existing image in the gallery.',
                    array_merge($params, array('{url}' => $url)));
                $message = ZurmoHtml::tag('p', array(), $message);
                return ZurmoHtml::tag('div', array('class' => 'image-legacy-message general-issue-notice'), $icon . $message);
            }
        }

        public static function saveImageFromTemporaryFile($tempFilePath, $name)
        {
            $fileContent                 = new FileContent();
            $fileContent->content        = file_get_contents($tempFilePath);
            $imageProperties             = getimagesize($tempFilePath);
            $imageFileModel              = new ImageFileModel();
            static::resolveImageName($name, $imageProperties['mime']);
            $imageFileModel->name        = $name;
            $imageFileModel->size        = filesize($tempFilePath);
            $imageFileModel->type        = $imageProperties['mime'];
            $imageFileModel->width       = $imageProperties[0];
            $imageFileModel->height      = $imageProperties[1];
            $imageFileModel->fileContent = $fileContent;
            if ($imageFileModel->save())
            {
                $imageFileModel->createImageCache();
                $fileUploadData = array(
                    'id'   => $imageFileModel->id,
                    'name' => $imageFileModel->name,
                    'summary' => ImageFileModelUtil::getImageSummary($imageFileModel),
                    'size' => FileModelDisplayUtil::convertSizeToHumanReadableAndGet($imageFileModel->size),
                    'thumbnail_url' => Yii::app()->createAbsoluteUrl('zurmo/imageModel/getThumb',
                            array('fileName' => $imageFileModel->getImageCacheFileName())),
                    'filelink' => Yii::app()->createAbsoluteUrl('zurmo/imageModel/getImage',
                            array('fileName' => $imageFileModel->getImageCacheFileName())),
                    'insert_link' => static::resolveInsertLink($imageFileModel),
                );
            }
            else
            {
                $message = Zurmo::t('ZurmoModule', 'Error uploading the image');
                $fileUploadData = array('error' => $message);
            }
            return $fileUploadData;
        }

        protected static function resolveImageName(& $name, $mimeType)
        {
            if (preg_match("/\..{3,4}\z/i", $name) == 0) // Not Coding Standard
            {
                $extension = str_replace('image/', '', $mimeType);
                $name .= $extension == 'jpeg' ? '.jpg' : '.' . $extension;
            }
        }

        public static function importFromUrl($url)
        {
            $tempFilePath = tempnam(sys_get_temp_dir(), 'upload_image_from_url_');
            $name = preg_replace("#.*\/#", '', $url);
            file_put_contents($tempFilePath, file_get_contents($url));
            $fileUploadData = static::saveImageFromTemporaryFile($tempFilePath, $name);
            return $fileUploadData;
        }
    }
?>