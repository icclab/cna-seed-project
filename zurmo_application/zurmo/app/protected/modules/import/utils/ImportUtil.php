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
     * Helper class for working with import.
     */
    class ImportUtil
    {
        /**
         * Given a data provider, call getData and for each row, attempt to import the data.
         * @param ImportDataProvider $dataProvider
         * @param ImportRules $importRules
         * @param $mappingData
         * @param ImportResultsUtil $importResultsUtil
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         * @param ImportMessageLogger $messageLogger
         */
        public static function importByDataProvider(ImportDataProvider $dataProvider,
                                                    ImportRules $importRules,
                                                    $mappingData,
                                                    ImportResultsUtil $importResultsUtil,
                                                    ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions,
                                                    ImportMessageLogger $messageLogger)
        {
            $data = $dataProvider->getData(true);
            foreach ($data as $rowBean)
            {
                assert('$rowBean->id != null');
                $importRowDataResultsUtil = new ImportRowDataResultsUtil((int)$rowBean->id);
                //todo: eventually handle security exceptions in a more graceful way instead of relying on a try/catch
                //but explicity checking for security rights/permissions.
                try
                {
                    static::importByImportRulesRowData($importRules, $rowBean, $mappingData,
                                                       $importRowDataResultsUtil, $explicitReadWriteModelPermissions);
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $importRowDataResultsUtil->addMessage(Zurmo::t('ImportModule', 'You do not have permission to create/update this record and/or its related record.'));
                    $importRowDataResultsUtil->setStatusToError();
                }
                $importResultsUtil->addRowDataResults($importRowDataResultsUtil);
                $messageLogger->countAfterRowImported();
            }
        }

        /**
         * Given a row of data, resolve each value of the row for import and either create or update an existing model.
         * @param object $importRules
         * @param array $rowData
         * @param array $mappingData
         * @param object $importRowDataResultsUtil
         */
        public static function importByImportRulesRowData(ImportRules $importRules,
                                                          $rowBean,
                                                          $mappingData,
                                                          ImportRowDataResultsUtil $importRowDataResultsUtil,
                                                          ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            assert('$rowBean instanceof RedBean_OODBBean');
            assert('is_array($mappingData)');
            $makeNewModel              = true;
            $modelClassName            = $importRules->getModelClassName();
            $externalSystemId          = null;
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $afterSaveActionsData      = array();
            $attributeValueData        = array();
            $skippedColumns            = array();

            $model = new $modelClassName();
            $model->setScenario('importModel');

            //Process the 'id' column first if available.
            if (false !== $idColumnName = static::getMappedColumnNameByMappingData('id', $mappingData))
            {
                $columnMappingData = $mappingData[$idColumnName];
                $attributeValueData     = static::getAttributeValueData($importRules,
                                                                        $idColumnName,
                                                                        $columnMappingData,
                                                                        $rowBean,
                                                                        $importSanitizeResultsUtil);
                assert('count($attributeValueData) == 0 || count($attributeValueData) == 1');
                if (isset($attributeValueData['id']) && $attributeValueData['id'] != null)
                {
                    $model        = $modelClassName::getById($attributeValueData['id']);
                }
                elseif (isset($attributeValueData[ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME]) &&
                        $attributeValueData[ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME] != null)
                {
                    $externalSystemId = $attributeValueData
                                        [ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME];
                }
                $skippedColumns[] = $idColumnName;
            }
            //Dedupe fields processing
            if (static::processDedupeAttributesToCheckForSkipIfRequired($importRules,
                                                                               $model,
                                                                               $mappingData,
                                                                               $rowBean,
                                                                               $importRowDataResultsUtil,
                                                                               $importSanitizeResultsUtil,
                                                                               $skippedColumns) !== true)
            {
                if (null != $matchedModel = $importSanitizeResultsUtil->getMatchedModel())
                {
                    $model = $matchedModel;
                    $makeNewModel = false;
                }
                //Process the dedupe fields
                foreach ($importRules->getDedupeAttributes() as $dedupeField)
                {
                    $sourceColumnName = static::getMappedColumnNameByMappingData($dedupeField, $mappingData);
                    if (false !== $sourceColumnName)
                    {
                        $columnMappingData = $mappingData[$sourceColumnName];
                        static::processImportInformationForAttributeDataAndPopulateModel($importRules,
                                                                                         $sourceColumnName,
                                                                                         $columnMappingData,
                                                                                         $rowBean,
                                                                                         $importSanitizeResultsUtil,
                                                                                         $model,
                                                                                         $afterSaveActionsData);
                    }
                }
            }
            else
            {
                //as the row should be skipped
                return;
            }
            //Process the rest of the mapped colummns. ignoring owner.
            foreach ($mappingData as $columnName => $columnMappingData)
            {
                if ($columnMappingData['attributeIndexOrDerivedType'] != null &&
                    $columnMappingData['attributeIndexOrDerivedType'] != 'owner' &&
                    in_array($columnName, $skippedColumns) === false)
                {
                    static::processImportInformationForAttributeDataAndPopulateModel($importRules,
                                                                                     $columnName,
                                                                                     $columnMappingData,
                                                                                     $rowBean,
                                                                                     $importSanitizeResultsUtil,
                                                                                     $model,
                                                                                     $afterSaveActionsData);
                }
            }

            //Process the owner column if present
            foreach ($mappingData as $columnName => $columnMappingData)
            {
                if ($columnMappingData['attributeIndexOrDerivedType'] != null &&
                    $columnMappingData['attributeIndexOrDerivedType'] == 'owner' &&
                    in_array($columnName, $skippedColumns) === false)
                {
                    static::processImportInformationForAttributeDataAndPopulateModel($importRules,
                                                                                     $columnName,
                                                                                     $columnMappingData,
                                                                                     $rowBean,
                                                                                     $importSanitizeResultsUtil,
                                                                                     $model,
                                                                                     $afterSaveActionsData);
                }
            }
            $validated = $model->validate();
            if ($validated && $importSanitizeResultsUtil->shouldSaveModel())
            {
                $saved = $model->save();
                if ($saved)
                {
                    static::processAfterSaveActions($afterSaveActionsData, $model);
                    if ($externalSystemId!= null)
                    {
                        ExternalSystemIdUtil::updateByModel($model, $externalSystemId);
                    }
                    $importRowDataResultsUtil->addMessage(Zurmo::t('ImportModule', '{modelLabel} saved correctly: {linkToModel}',
                                array('{modelLabel}'  => $model->getModelLabelByTypeAndLanguage('Singular'),
                                      '{linkToModel}' => static::resolveLinkMessageToModel($model))));
                    $importRowDataResultsUtil->addMessages($importSanitizeResultsUtil->getRelatedModelMessages());
                    if ($makeNewModel)
                    {
                        if ($model instanceof SecurableItem)
                        {
                            $model->setScenario('doNotSetModifiedDateTimeOrUser');
                            try
                            {
                                $resolved = ExplicitReadWriteModelPermissionsUtil::
                                            resolveExplicitReadWriteModelPermissions(
                                                $model,
                                                $explicitReadWriteModelPermissions);
                                                $importRowDataResultsUtil->setStatusToCreated();
                                if (!$resolved)
                                {
                                    $importRowDataResultsUtil->addMessage('The record saved, but there was a problem '.
                                    'setting the security permissions. It will at least be viewable by the owner.');
                                    $importRowDataResultsUtil->setStatusToError();
                                }
                            }
                            catch (AccessDeniedSecurityException $e)
                            {
                                $importRowDataResultsUtil->addMessage('The record saved, but you do not have permissions '.
                                'to set the security the way you did. The record will only be viewable by the owner.');
                                $importRowDataResultsUtil->setStatusToError();
                            }
                            $model->setScenario('importModel');
                        }
                        else
                        {
                            $importRowDataResultsUtil->setStatusToCreated();
                        }
                    }
                    else
                    {
                        $importRowDataResultsUtil->setStatusToUpdated();
                    }
                }
                else
                {
                    $importRowDataResultsUtil->addMessage('The record failed to save. Reason unknown.');
                    $importRowDataResultsUtil->setStatusToError();
                }
            }
            else
            {
                if (!$importSanitizeResultsUtil->shouldSaveModel())
                {
                    $importRowDataResultsUtil->addMessages($importSanitizeResultsUtil->getMessages());
                }
                $messages = RedBeanModelErrorsToMessagesUtil::makeMessagesByModel($model);
                if (count($messages) > 0)
                {
                    $importRowDataResultsUtil->addMessages($messages);
                }
                $importRowDataResultsUtil->setStatusToError();
            }
        }

        /**
         * Public for testing only
         * @param RedBeanModel $model
         * @return string
         */
        public static function resolveLinkMessageToModel(RedBeanModel $model)
        {
            $moduleClassName   = $model::getModuleClassName();
            $stateMetadataAdapterClassName = $moduleClassName::getStateMetadataAdapterClassName();
            if ($stateMetadataAdapterClassName != null)
            {
                //todo: eventually refactor this to be more broad in handling, but for now we want the scope of this to be narrow
                if ($model instanceof OwnedSecurableItem)
                {
                    $model->setTreatCurrentUserAsOwnerForPermissions(true);
                }
                $moduleClassName = $stateMetadataAdapterClassName::getModuleClassNameByModel($model);
                $model->setTreatCurrentUserAsOwnerForPermissions(false);
            }
            $moduleId   = $moduleClassName::getDirectoryName();
            $urlToModel = Yii::app()->createUrl('/' . $moduleId . '/default/details', array('id' => $model->id));
            return ZurmoHtml::link(strval($model), $urlToModel, array('class' => 'simple-link', 'target' => 'blank'));
        }

        protected static function sanitizeValueAndPopulateModel(ImportRules $importRules,
                                                                RedBeanModel $model,
                                                                $columnMappingData,
                                                                $attributeValueData,
                                                                & $afterSaveActionsData)
        {
            assert('is_array($afterSaveActionsData)');
            assert('$columnMappingData["type"] == "importColumn" ||
            $columnMappingData["type"] == "extraColumn"');

            $modelClassName       = get_class($model);
            $attributeImportRules = static::getAttributeImportRules($importRules, $columnMappingData);
            if ($attributeImportRules instanceof NonDerivedAttributeImportRules &&
               $attributeImportRules->getModelClassName() != $modelClassName)
            {
                static::resolveModelForAttributeIndexWithMultipleNonDerivedAttributes($model,
                                                                                      $attributeImportRules,
                                                                                      $attributeValueData,
                                                                                      $columnMappingData);
            }
            elseif ($attributeImportRules instanceof ModelDerivedAttributeImportRules)
            {
                static::resolveModelForModelDerivedAttribute( $model,
                                                              $importRules::getType(),
                                                              $attributeImportRules,
                                                              $attributeValueData);
            }
            elseif ($attributeImportRules instanceof AfterSaveActionDerivedAttributeImportRules)
            {
                static::resolveAfterSaveActionDerivedAttributeImportRules(  $afterSaveActionsData,
                                                                            $attributeImportRules,
                                                                            $attributeValueData);
            }
            elseif ($attributeImportRules instanceof AfterSaveActionNonDerivedAttributeImportRules)
            {
                static::resolveAfterSaveActionNonDerivedAttributeImportRules($afterSaveActionsData,
                                                                             $attributeImportRules,
                                                                             $attributeValueData);
            }
            else
            {
                static::
                resolveModelForAttributeIndexWithSingleAttributeOrDerivedAttribute($model,
                                                                                   $attributeValueData);
            }
        }

        protected static function processAfterSaveActions($afterSaveActionsData, RedBeanModel $model)
        {
            assert('is_array($afterSaveActionsData)');
            foreach ($afterSaveActionsData as $attributeImportRuleClassNameAndAttributeValueData)
            {
                assert('count($attributeImportRuleClassNameAndAttributeValueData) == 2');
                $attributeImportRulesClassName = $attributeImportRuleClassNameAndAttributeValueData[0];
                $attributeValueData            = $attributeImportRuleClassNameAndAttributeValueData[1];
                $attributeImportRulesClassName::processAfterSaveAction($model, $attributeValueData);
            }
        }

        /**
         * Get mapped column name by mapping data
         * @param string $modelColumnName
         * @param array $mappingData
         * @return boolean
         * @throws NotSupportedException
         */
        protected static function getMappedColumnNameByMappingData($modelColumnName, $mappingData)
        {
            assert('is_array($mappingData)');
            assert('is_string($modelColumnName)');
            $mappedColumnName = null;
            $valueFound   = false;
            foreach ($mappingData as $columnName => $columnMappingData)
            {
                if ($columnMappingData['attributeIndexOrDerivedType'] == $modelColumnName)
                {
                    if ($valueFound || $columnMappingData['type'] != 'importColumn')
                    {
                        throw new NotSupportedException();
                    }
                    $mappedColumnName = $columnName;
                    $valueFound   = true;
                }
            }
            if ($mappedColumnName != null)
            {
                return $mappedColumnName;
            }
            return false;
        }

        protected static function resolveModelForAttributeIndexWithMultipleNonDerivedAttributes(
                                  RedBeanModel $model,
                                  $attributeImportRules,
                                  $attributeValueData,
                                  $columnMappingData)
        {
            assert('is_array($columnMappingData)');
            $attributeName          = AttributeImportRulesFactory::
                                      getAttributeNameFromAttributeNameByAttributeIndexOrDerivedType(
                                      $columnMappingData['attributeIndexOrDerivedType']);
            $relationModelClassName = $attributeImportRules->getModelClassName();
            if ($model->$attributeName == null)
            {
                $model->$attributeName = new $relationModelClassName();
            }
            elseif (!$model->$attributeName instanceof $relationModelClassName)
            {
                throw new NotSupportedException();
            }
            foreach ($attributeValueData as $relationAttributeName => $value)
            {
                assert('$model->$attributeName->isAttribute($relationAttributeName)');
                static::resolveReadOnlyAndSetValueToAttribute($model->$attributeName, $relationAttributeName, $value);
            }
        }

        protected static function resolveModelForAttributeIndexWithSingleAttributeOrDerivedAttribute(
                                  RedBeanModel $model,
                                  $attributeValueData)
        {
            foreach ($attributeValueData as $attributeName => $value)
            {
                if ( $model->$attributeName instanceof RedBeanManyToManyRelatedModels)
                {
                    static::resolveValueThatIsManyModelsRelationToAttribute($model, $attributeName, $value);
                }
                elseif ($model->isAttribute($attributeName))
                {
                    static::resolveReadOnlyAndSetValueToAttribute($model, $attributeName, $value);
                }
            }
        }

        /**
         * Some derivedAttributeImportRules require the sanitized values to be processed after the model is saved. An example
         * is the user status which is a derived attribute requiring processing after the user has been saved. This
         * method gets the sanitized value and adds it along with the attributeImportRules class name to an array
         * by reference.  After the model is saved, this array is referenced and each attribute import rule is processed.
         * @see AfterSaveActionDerivedAttributeImportRules
         * @param array $afterSaveActionsData
         * @param AttributeImportRules $attributeImportRules
         * @param mixed $valueReadyToSanitize
         * @param array $columnMappingData
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         */
        protected static function resolveAfterSaveActionDerivedAttributeImportRules(
                                  & $afterSaveActionsData,
                                  DerivedAttributeImportRules $attributeImportRules,
                                  $attributeValueData)
        {
            assert('is_array($afterSaveActionsData)');
            assert('$attributeImportRules instanceof AfterSaveActionDerivedAttributeImportRules');
            if ($attributeValueData != null)
            {
                $afterSaveActionsData[] = array(get_class($attributeImportRules), $attributeValueData);
            }
        }

        /**
         * Some attributeImportRules require the sanitized values to be processed after the model is saved. An example
         * is the user status which is a derived attribute requiring processing after the user has been saved. This
         * method gets the sanitized value and adds it along with the attributeImportRules class name to an array
         * by reference.  After the model is saved, this array is referenced and each attribute import rule is processed.
         * @see AfterSaveActionNonDerivedAttributeImportRules
         * @param array $afterSaveActionsData
         * @param AttributeImportRules $attributeImportRules
         * @param mixed $valueReadyToSanitize
         * @param array $columnMappingData
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         */
        protected static function resolveAfterSaveActionNonDerivedAttributeImportRules(
                                  & $afterSaveActionsData,
                                  NonDerivedAttributeImportRules $attributeImportRules,
                                  $attributeValueData)
        {
            assert('is_array($afterSaveActionsData)');
            assert('$attributeImportRules instanceof AfterSaveActionNonDerivedAttributeImportRules');
            if ($attributeValueData != null)
            {
                $afterSaveActionsData[] = array(get_class($attributeImportRules), $attributeValueData);
            }
        }

        protected static function resolveModelForModelDerivedAttribute(
                                  RedBeanModel $model,
                                  $importRulesType,
                                  AttributeImportRules $attributeImportRules,
                                  $attributeValueData)
        {
            assert('is_string($importRulesType)');
            assert('$attributeImportRules instanceof ModelDerivedAttributeImportRules');
            assert('count($attributeValueData) == 1');
            assert('$attributeImportRules::getDerivedAttributeName() != null');
            $derivedAttributeName = $attributeImportRules::getDerivedAttributeName();
            if ($attributeValueData[$derivedAttributeName] != null)
            {
                $importRulesClassName = ImportRulesUtil::getImportRulesClassNameByType($importRulesType);
                $actualAttributeName  = $importRulesClassName::getActualModelAttributeNameForDerivedAttribute();
                $actualModel          = $attributeValueData[$derivedAttributeName];
                if (!$model->$actualAttributeName->contains($actualModel))
                {
                    $model->$actualAttributeName->add($actualModel);
                }
            }
        }

        protected static function resolveReadOnlyAndSetValueToAttribute(RedBeanModel $model, $attributeName, $value)
        {
            assert('is_string($attributeName)');
            if (!$model->isAttributeReadOnly($attributeName) || ($model->isAttributeReadOnly($attributeName) && // Not Coding Standard
                $model->isAllowedToSetReadOnlyAttribute($attributeName)))
            {
                $model->$attributeName = $value;
            }
        }

        protected static function resolveValueThatIsManyModelsRelationToAttribute($model, $attributeName, $value)
        {
            assert('is_string($attributeName)');
            assert('$model->$attributeName instanceof RedBeanManyToManyRelatedModels');
            assert('$value == null || $value instanceof RedBeanModel');
            if ($value != null && !$model->$attributeName->contains($value))
            {
                $model->$attributeName->add($value);
            }
        }

        protected static function resolveValueToSanitizeByValueAndColumnType($value, $columnType)
        {
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            if ($columnType == 'importColumn')
            {
                return static::resolveValueToSanitize($value);
            }
            else
            {
                return null;
            }
        }

        protected static function resolveValueToSanitize($value)
        {
            if ($value == '' || $value == null)
            {
               return null;
            }
            else
            {
                return trim($value);
            }
        }

        /**
         * Method to run import from command line. Use @ImportCommand.
         * @param array $args
         */
        public static function runFromImportCommand($args)
        {
            assert('is_array($args)');
            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);

            if (isset($args[3]))
            {
                set_time_limit($args[3]);
                $messageStreamer->add(Zurmo::t('JobsManagerModule', 'Script will run at most for {seconds} seconds.',
                                      array('{seconds}' => $args[3])));
            }
            else
            {
                set_time_limit('1200');
                $messageStreamer->add(Zurmo::t('JobsManagerModule', 'Script will run at most for {seconds} seconds.',
                                      array('{seconds}' => '1200')));
            }
            if (isset($args[0]))
            {
                $importName = $args[0];
                $messageStreamer->add(Zurmo::t('ImportModule', 'Starting import for process: {processName}',
                                      array('{processName}' => $importName)));
            }
            else
            {
                $importName = null;
                $messageStreamer->add(Zurmo::t('ImportModule', 'Starting import. Looking for processes.'));
            }

            $messageLogger = new ImportMessageLogger($messageStreamer);
            if (isset($args[2]))
            {
                $messageLogger->setMessageOutputInterval((int)$args[2]);
            }
            $importName = null;
            if (isset($args[1]))
            {
                $importName = $args[1];
            }
            Yii::app()->custom->runImportsForImportCommand($messageLogger, $importName);
            $messageStreamer->add(Zurmo::t('ImportModule', 'Ending import.'));
        }

        /**
         * Get Penultimate ModelClassName By Import Rules
         * @param type $importRules
         * @return type
         */
        public static function getPenultimateModelClassNameByImportRules(ImportRules $importRules)
        {
            $importRulesClassName  = $importRules::getType() . 'ImportRules';
            return $importRulesClassName::getModelClassName();
        }

        /**
         * Gets attribute value data
         * @param AttributeImportRules $attributeImportRules
         * @param string $columnName
         * @param array $columnMappingData
         * @param string $valueReadyToSanitize
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         */
        protected static function getAttributeValueData(ImportRules $importRules,
                                                 $columnName,
                                                 $columnMappingData,
                                                 $rowBean,
                                                 ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            $attributeImportRules = static::getAttributeImportRules($importRules, $columnMappingData);
            $valueReadyToSanitize = static::
                                    resolveValueToSanitizeByValueAndColumnType($rowBean->$columnName,
                                                                               $columnMappingData['type']);

            return $attributeImportRules->resolveValueForImport($valueReadyToSanitize, $columnName,
                                                                $columnMappingData,
                                                                $importSanitizeResultsUtil);
        }

        /**
         * Get attribute import rules
         * @param ImportRules $importRules
         * @param array $columnMappingData
         * @return object containing attribute import rules
         * @throws NotSupportedException
         */
        protected static function getAttributeImportRules(ImportRules $importRules, $columnMappingData)
        {
            $attributeImportRules =  AttributeImportRulesFactory::
                                        makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                            $importRules::getType(),
                                            $columnMappingData['attributeIndexOrDerivedType'],
                                            self::getPenultimateModelClassNameByImportRules($importRules));
            if ($attributeImportRules->getModelClassName() == null)
            {
                throw new NotSupportedException();
            }
            return $attributeImportRules;
        }

        /**
         * Process import information, get attribute value data and populates the model
         * @param ImportRules $importRules
         * @param string $columnName
         * @param array $columnMappingData
         * @param RedBean_OODBBean $rowBean
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         * @param RedBeanModel $model
         * @param array $afterSaveActionsData
         */
        protected static function processImportInformationForAttributeDataAndPopulateModel(ImportRules $importRules,
                                                                                    $columnName,
                                                                                    $columnMappingData,
                                                                                    $rowBean,
                                                                                    ImportSanitizeResultsUtil $importSanitizeResultsUtil,
                                                                                    RedBeanModel $model,
                                                                                    & $afterSaveActionsData)
        {
            assert('$rowBean instanceof RedBean_OODBBean');
            $attributeValueData = static::getAttributeValueData($importRules,
                                                                $columnName,
                                                                $columnMappingData,
                                                                $rowBean,
                                                                $importSanitizeResultsUtil);
            static::sanitizeValueAndPopulateModel($importRules,
                                                  $model,
                                                  $columnMappingData,
                                                  $attributeValueData,
                                                  $afterSaveActionsData);
        }

        /**
         * Process mapped columns to check for matched record by column
         * @param ImportRules $importRules
         * @param RedBeanModel $model
         * @param array $mappingData
         * @param RedBean_OODBBean $rowBean
         * @param ImportSanitizeResultsUtil $importSanitizeResultsUtil
         * @param array $skippedColumns
         */
        protected static function processDedupeAttributesToCheckForSkipIfRequired(ImportRules $importRules,
                                                                                RedBeanModel $model,
                                                                                $mappingData,
                                                                                $rowBean,
                                                                                ImportRowDataResultsUtil $importRowDataResultsUtil,
                                                                                ImportSanitizeResultsUtil $importSanitizeResultsUtil,
                                                                                & $skippedColumns)
        {
            assert('$rowBean instanceof RedBean_OODBBean');
            $isSkipped = false;
            //Process the dedupe fields
            foreach ($importRules->getDedupeAttributes() as $dedupeField)
            {
                $sourceColumnName = static::getMappedColumnNameByMappingData($dedupeField, $mappingData);
                if (false !== $sourceColumnName)
                {
                    $columnMappingData      = $mappingData[$sourceColumnName];
                    $attributeValueData     = static::getAttributeValueData($importRules,
                                                                        $sourceColumnName,
                                                                        $columnMappingData,
                                                                        $rowBean,
                                                                        $importSanitizeResultsUtil);
                    if (!$importSanitizeResultsUtil->shouldSaveModel())
                    {
                        $importRowDataResultsUtil->addMessages($importSanitizeResultsUtil->getMessages());
                        $importRowDataResultsUtil->setStatusToError();
                        $isSkipped = true;
                    }
                    $skippedColumns[] = $sourceColumnName;
                }
            }
            return $isSkipped;
        }
    }
?>