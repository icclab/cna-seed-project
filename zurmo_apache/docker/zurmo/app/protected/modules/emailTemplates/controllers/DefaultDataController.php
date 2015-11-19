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

    class EmailTemplatesDefaultDataController extends ZurmoModuleController
    {
        protected $templateNameToIconMapping    = array(
                'Blank'                         => 'icon-template-0',
                '1 Column'                      => 'icon-template-5',
                '2 Columns'                     => 'icon-template-2',
                '2 Columns with strong right'   => 'icon-template-3',
                '3 Columns'                     => 'icon-template-4',
                '3 Columns with Hero'           => 'icon-template-1',
                'Kitchen Sink'                  => 'icon-elements'
        );

        public function actionLoad()
        {
            $defaultDataMaker   = new EmailTemplatesDefaultDataMaker();
            $loaded             = $defaultDataMaker->make();
            if ($loaded)
            {
                echo "Default data loaded";
            }
            else
            {
                echo "Unable to load default data";
            }
        }

        public function actionGenerate($start, $last)
        {
            $functionNames          = array();
            $functionDefinitions    = null;
            for ($i = $start; $i <= $last; $i++)
            {
                $this->resolveMakeTemplateFunctionDefinitionById($i, $functionDefinitions, $functionNames);
            }
            $content    = $this->resolveMakeFunction($functionNames);
            $content    .= $functionDefinitions;
            $content    = CHtml::encode($content);
            $content    = "<pre>${content}</pre>";
            echo $content;
            Yii::app()->end(0, false);
        }

        protected function resolveMakeTemplateFunctionDefinitionById($id, & $functionDefinitions, array & $functionNames)
        {
            $name                           = null;
            $builttype                      = null;
            $serializeddata                 = null;
            $emailTemplate                  =  ZurmoRedBean::getRow('select name, builttype, serializeddata from emailtemplate where id =' . $id);
            if (empty($emailTemplate))
            {
                throw new NotFoundException("Unable to load model for id: ${id}");
            }
            extract($emailTemplate);
            if ($builttype != EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE)
            {
                throw new NotSupportedException("id: {$id} is not a builder template");
            }
            $unserializedData               = CJSON::decode($serializeddata);
            if (json_last_error() != JSON_ERROR_NONE || empty($unserializedData))
            {
                throw new NotSupportedException("JSON could not be translated");
            }
            $unserializedData['baseTemplateId'] = '';
            $unserializedData['icon']           = ArrayUtil::getArrayValue($this->templateNameToIconMapping, $name);
            $unserializedData                   = var_export($unserializedData, true);
            $functionName                       = $this->resolveFunctionNameFromTemplateName($name);
            $functionNames[]                    = $functionName;
            $functionDefinitions                .= "

protected function $functionName()
{
    \$name              = '$name';
    \$unserializedData  = $unserializedData;
    \$this->makeBuilderPredefinedEmailTemplate(\$name, \$unserializedData);
}";
        }

        protected function resolveMakeFunction($functionNames)
        {
            $content    = null;
            foreach ($functionNames as $functionName)
            {
                $content .= PHP_EOL ."\t\$this->${functionName}();";
            }
            $content    = PHP_EOL . "
public function make()
{ $content
\treturn true;
}";
            return $content;
        }

        protected function resolveFunctionNameFromTemplateName($name)
        {
            preg_match("~^(\d+)~", $name, $columnCount);    // Not Coding Standard
            if (isset($columnCount[1]))
            {
                $columnCount    = NumberToWordsUtil::convert($columnCount[1]);
                $name           = strtolower($columnCount . substr($name, strlen($columnCount[1])));
            }
            $name           = 'make ' . strtolower($name);
            $name           = preg_replace('/[^a-z ]/', '', $name);
            $name           = StringUtil::camelize($name, false, ' ');
            return $name;
        }
    }
?>