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

    abstract class ZurmoCssInlineConverterUtil
    {
        public static function convertAndPrettifyEmailByModel(EmailTemplate $emailTemplate, $converter = null, $prettyPrint = false)
        {
            $htmlContent        = $emailTemplate->htmlContent;
            if (empty($htmlContent))
            {
                $htmlContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate, false);
            }
            $htmlContent        = static::convertAndPrettifyEmailByHtmlContent($htmlContent, $converter, $prettyPrint);
            return $htmlContent;
        }

        public static function convertAndPrettifyEmailByHtmlContent($htmlContent, $converter = null, $prettyPrint = false)
        {
            if (isset($converter))
            {
                $htmlContent    = static::convertHtmlContent($htmlContent, $converter);
            }
            if ($prettyPrint)
            {
                $htmlContent    = static::prettyPrint($htmlContent);
            }
            $htmlContent        = static::resolveHtmlContentForPostConverterChanges($htmlContent);
            return $htmlContent;
        }

        protected static function convertHtmlContent($htmlContent, $converter)
        {
            // we may add support for other converters in future.
            if ($converter == 'cssin')
            {
                return static::convertUsingCssIn($htmlContent);
            }
            else
            {
                throw new NotSupportedException('Invalid converter specified.');
            }
        }

        protected static function resolveHtmlContentForPostConverterChanges($htmlContent)
        {
            $htmlContent    = '<!-- zurmo css inline -->' . PHP_EOL . $htmlContent;
            return $htmlContent;
        }

        protected static function prettyPrint($htmlContent)
        {
            $document                       = new DOMDocument();
            $document->preserveWhiteSpace   = false;
            $document->formatOutput         = true;
            @$document->loadHTML($htmlContent);
            $prettyPrintedHtmlContent       = $document->saveHTML();
            return $prettyPrintedHtmlContent;
        }

        protected static function convertUsingCssIn($htmlContent)
        {
            $cssInUtil = new ZurmoCssInUtil();
            $cssInUtil->setMoveStyleBlocksToBody();
            $htmlContent = $cssInUtil->inlineCSS(null, $htmlContent);
            return $htmlContent;
        }
    }
?>