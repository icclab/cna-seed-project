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
     * Class EmailTemplateSerializedDataToHtmlUtil
     * Utility class to resolve serializedData of an email template into html with option to also
     * resolve merge tags at the same time.
     */
    class EmailTemplateSerializedDataToHtmlUtil
    {
        /**
         * Resolve html for a builder template provided its id
         * @param $emailTemplateId
         * @param bool $renderForCanvas
         * @param OwnedSecurableItem $attachedMergeTagModel
         * @return bool|null|string
         */
        public static function resolveHtmlByEmailTemplateId($emailTemplateId, $renderForCanvas = false, OwnedSecurableItem $attachedMergeTagModel = null)
        {
            $emailTemplate  = EmailTemplate::getById(intval($emailTemplateId));
            $resolvedHtml   = static::resolveHtmlByEmailTemplateModel($emailTemplate, $renderForCanvas, $attachedMergeTagModel);
            return $resolvedHtml;
        }

        /**
         * Resolve html for a builder template provided the model itself.
         * @param EmailTemplate $emailTemplate
         * @param bool $renderForCanvas
         * @param OwnedSecurableItem $attachedMergeTagModel
         * @return bool|null|string
         */
        public static function resolveHtmlByEmailTemplateModel(EmailTemplate $emailTemplate, $renderForCanvas = false, OwnedSecurableItem $attachedMergeTagModel = null)
        {
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            $serializedData = $emailTemplate->serializedData;
            $resolvedHtml   = static::resolveHtmlBySerializedData($serializedData, $renderForCanvas, $attachedMergeTagModel, $emailTemplate->type, $emailTemplate->language);
            return $resolvedHtml;
        }

        /**
         * Resolve html for provided serialized data
         * @param $serializedData
         * @param bool $renderForCanvas
         * @param OwnedSecurableItem $attachedMergeTagModel
         * @param null $type
         * @param null $language
         * @return bool|null|string
         */
        public static function resolveHtmlBySerializedData($serializedData, $renderForCanvas = false, OwnedSecurableItem $attachedMergeTagModel = null, $type = null, $language = null)
        {
            $unserializedData   = CJSON::decode($serializedData);
            $resolvedHtml       = null;
            if (!empty($unserializedData))
            {
                $resolvedHtml       = static::resolveHtmlByUnserializedData($unserializedData, $renderForCanvas, $attachedMergeTagModel, $type, $language);
            }
            return $resolvedHtml;
        }

        /**
         * Resolve html for provided unserialized data array
         * @param array $unserializedData
         * @param bool $renderForCanvas
         * @param OwnedSecurableItem $attachedMergeTagModel
         * @param null $type
         * @param null $language
         * @return bool|null|string
         */
        public static function resolveHtmlByUnserializedData(array $unserializedData, $renderForCanvas = false, OwnedSecurableItem $attachedMergeTagModel = null, $type = null, $language = null)
        {
            $resolvedHtml   = null;
            $dom            = ArrayUtil::getArrayValue($unserializedData, 'dom');
            if (empty($dom))
            {
                return null;
            }
            $class          = null;
            $properties     = null;
            $content        = null;
            $canvasData     = reset($dom);
            if (!is_array($canvasData))
            {
                return null;
            }
            extract($canvasData);
            $id             = key($dom);
            $resolvedHtml   = BuilderElementRenderUtil::renderNonEditable($class, $renderForCanvas, false, $id, $properties, $content);
            if (empty($resolvedHtml))
            {
                return null;
            }
            if (isset($attachedMergeTagModel))
            {
                $resolvedHtml   = static::resolveMergeTagsByModel($resolvedHtml, $attachedMergeTagModel, $type, $language);
            }
            return $resolvedHtml;
        }

        /**
         * Resolve merge tags present in html
         * @param $html
         * @param OwnedSecurableItem $attachedMergeTagModel
         * @param null $type
         * @param null $language
         * @return bool|string
         * @throws FailedToResolveMergeTagsException
         */
        public static function resolveMergeTagsByModel($html, OwnedSecurableItem $attachedMergeTagModel, $type = null, $language = null)
        {
            $invalidTags = array();
            if (!isset($type))
            {
                $type = EmailTemplate::TYPE_CONTACT;
                if (get_class($attachedMergeTagModel) != 'Contact')
                {
                    $type = EmailTemplate::TYPE_WORKFLOW;
                }
            }
            $mergeTagsUtil              = MergeTagsUtilFactory::make($type, $language, $html);
            $resolvedContent            = $mergeTagsUtil->resolveMergeTags($attachedMergeTagModel, $invalidTags, $language);
            if ($resolvedContent == false)
            {
                throw new FailedToResolveMergeTagsException();
            }
            return $resolvedContent;
        }
    }
?>