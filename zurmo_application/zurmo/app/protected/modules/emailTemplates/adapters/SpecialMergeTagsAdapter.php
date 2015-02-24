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

    /*
     * This class is responsible from converting special merge tags to relevant attribute values.
     */
    class SpecialMergeTagsAdapter
    {
        protected static $containsNestedMergeTags   = array(
                                'globalMarketingFooterHtml',
                                'globalMarketingFooterPlainText'
                                );

        protected static $specialAttributesResolver = array (
                                'modelUrl'                          => 'resolveModelUrlByModel',
                                'baseUrl'                           => 'resolveBaseUrl',
                                'applicationName'                   => 'resolveApplicationName',
                                'currentYear'                       => 'resolveCurrentYear',
                                'lastYear'                          => 'resolveLastYear',
                                'ownersAvatarSmall'                 => 'resolveOwnersAvatarSmall',
                                'ownersAvatarMedium'                => 'resolveOwnersAvatarMedium',
                                'ownersAvatarLarge'                 => 'resolveOwnersAvatarLarge',
                                'ownersEmailSignature'              => 'resolveOwnersEmailSignature',
                                'globalMarketingFooterHtml'         => 'resolveGlobalMarketingFooterHtml',
                                'globalMarketingFooterPlainText'    => 'resolveGlobalMarketingFooterPlainText',
                                'unsubscribeUrl'                    => 'resolveUnsubscribeUrl',
                                'manageSubscriptionsUrl'            => 'resolveManageSubscriptionsUrl',
                                );

        public static function isSpecialMergeTag($attributeName, $timeQualifier)
        {
            return (empty($timeQualifier) && array_key_exists($attributeName, static::$specialAttributesResolver));
        }

        public static function resolve($attributeName, $model = null, $params = array())
        {
            $methodName                         = static::$specialAttributesResolver[$attributeName];
            // we send $model to all, those which need it use it, other get it as optional param.
            $resolvedSpecialMergeTagContent     = static::$methodName($model, $params);
            if (in_array($attributeName, static::$containsNestedMergeTags))
            {
                static::resolveContentForNestedMergeTags($resolvedSpecialMergeTagContent, $model, $params);
            }
            return $resolvedSpecialMergeTagContent;
        }

        // individual resolvers
        protected static function resolveModelUrlByModel($model)
        {
            $modelClassName     = get_class($model);
            $moduleClassName    = $modelClassName::getModuleClassName();
            $moduleId           = $moduleClassName::getDirectoryName();
            if (null != $stateAdapterClassName = $moduleClassName::getStateMetadataAdapterClassName())
            {
                $resolvedModuleClassName = $stateAdapterClassName::getModuleClassNameByModel($model);
                $moduleId                = $resolvedModuleClassName::getDirectoryName();
            }
            return Yii::app()->createAbsoluteUrl('/' . $moduleId . '/default/details/', array('id' => $model->id));
        }

        protected static function resolveBaseUrl()
        {
            return Yii::app()->getBaseUrl(true);
        }

        protected static function resolveApplicationName()
        {
            return ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
        }

        protected static function resolveCurrentYear()
        {
            return date('Y');
        }

        protected static function resolveLastYear()
        {
            return static::resolveCurrentYear() - 1 ;
        }

        /**
         * @param $model
         */
        protected static function resolveOwnersAvatarSmall($model)
        {
            return static::resolveOwnersAvatar($model, 32);
        }

        /**
         * @param $model
         */
        protected static function resolveOwnersAvatarMedium($model)
        {
            return static::resolveOwnersAvatar($model, 64);
        }

        /**
         * @param $model
         * @return mixed
         */
        protected static function resolveOwnersAvatarLarge($model)
        {
            return static::resolveOwnersAvatar($model, 128);
        }

        protected static function resolveOwnersAvatar($model, $size)
        {
            if ($model instanceof OwnedSecurableItem && $model->owner->id > 0)
            {
                return $model->owner->getAvatarImage($size, true);
            }
        }

        /**
         * Will only grab first available email signature for user if available
         * @param $model
         */
        protected static function resolveOwnersEmailSignature($model, $params = array())
        {
            if ($model instanceof OwnedSecurableItem && $model->owner->id > 0)
            {
                if ($model->owner->emailSignatures->count() > 0)
                {
                    $isHtmlContent  = ArrayUtil::getArrayValue($params, 'isHtmlContent', true);
                    if ($isHtmlContent)
                    {
                        return $model->owner->emailSignatures[0]->htmlContent;
                    }
                    else
                    {
                        return $model->owner->emailSignatures[0]->textContent;
                    }
                }
            }
        }

        protected static function resolveGlobalMarketingFooterHtml()
        {
            return GlobalMarketingFooterUtil::getContentByType(true, true);
        }

        protected static function resolveGlobalMarketingFooterPlainText()
        {
            return GlobalMarketingFooterUtil::getContentByType(false, true);
        }

        protected static function resolveUnsubscribeUrl($model, $params = array())
        {
            $content    = static::resolveGlobalMarketingFooterUrl('resolveUnsubscribeUrlByArray', $params);
            return $content;
        }

        protected static function resolveManageSubscriptionsUrl($model, $params = array())
        {
            $content    = static::resolveGlobalMarketingFooterUrl('resolveManageSubscriptionsUrlByArray', $params);
            return $content;
        }

        protected static function resolveGlobalMarketingFooterUrl($method, $params = array())
        {
            try
            {
                $content = GlobalMarketingFooterUtil::$method($params);
                return $content;
            }
            catch (NotSupportedException $e)
            {
                return MergeTagsToModelAttributesAdapter::PROPERTY_NOT_FOUND;
            }
        }

        protected static function resolveContentForNestedMergeTags(& $resolvedSpecialMergeTagContent, $model = null, $params = array())
        {
            $language               = null;
            $type                   = EmailTemplate::TYPE_WORKFLOW;
            $invalidTags            = array();
            $language               = null;
            $errorOnFirstMissing    = false;
            if ($model instanceof Contact)
            {
                $type   = EmailTemplate::TYPE_CONTACT;
            }
            $util                           = MergeTagsUtilFactory::make($type, $language, $resolvedSpecialMergeTagContent);
            $resolvedContent                = $util->resolveMergeTags($model, $invalidTags, $language, $errorOnFirstMissing, $params);
            if ($resolvedContent !== false)
            {
                $resolvedSpecialMergeTagContent = $resolvedContent;
            }
        }
    }
?>