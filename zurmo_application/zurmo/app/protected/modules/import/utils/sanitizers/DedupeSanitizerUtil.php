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
     * Sanitizer for email duplicate in the records
     */
    abstract class DedupeSanitizerUtil extends SanitizerUtil
    {
        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            $this->checkIfRowToBeSkippedAndSetAnalysisMessages($rowBean->{$this->columnName});
        }

        /**
         * Given a value, attempt to convert the value to a db date format based on the format provided.  If the value
         * does not convert properly, meaning the value is not really in the format specified, then a
         * InvalidValueToSanitizeException will be thrown.
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         */
        public function sanitizeValue($value)
        {
            $this->checkIfRowToBeSkippedAndSetAnalysisMessages($value);
            return $value;
        }

        protected function assertMappingRuleDataIsValid()
        {
            if (isset($this->mappingRuleData["dedupeRule"]))
            {
                assert('is_array($this->mappingRuleData["dedupeRule"])');
            }
        }

        /**
         * Check if row to be skipped and set the analysis messages based on it
         * @param string $value
         */
        protected function checkIfRowToBeSkippedAndSetAnalysisMessages($value)
        {
            assert('$value === null || is_string($value)');
            if (isset($this->mappingRuleData["dedupeRule"]) &&
                $this->mappingRuleData["dedupeRule"]["value"] == ImportDedupeRulesRadioDropDownElement::SKIP_ROW_ON_MATCH_FOUND)
            {
                if ($value != null)
                {
                    $matchedModels  = $this->getMatchedModels($value, 1);
                    if (count($matchedModels) > 0)
                    {
                        $this->shouldSkipRow = true;
                        $label = Zurmo::t('ImportModule', 'The record will be skipped during import due to dedupe rule.');
                        $this->analysisMessages[] = $label;
                        if ($this->importSanitizeResultsUtil != null)
                        {
                            $this->importSanitizeResultsUtil->setModelShouldNotBeSaved();
                        }
                    }
                }
            }
            elseif (isset($this->mappingRuleData["dedupeRule"]) &&
                    $this->mappingRuleData["dedupeRule"]["value"] == ImportDedupeRulesRadioDropDownElement::UPDATE_ROW_ON_MATCH_FOUND
                    )
            {
                if ($value != null)
                {
                    $matchedModels  = $this->getMatchedModels($value, 1);
                    if (count($matchedModels) > 0)
                    {
                        $label = Zurmo::t('ImportModule',
                                          'A record with this value already exists and will be updated with the values of the imported record.');
                        $this->analysisMessages[] = $label;
                        if ($this->importSanitizeResultsUtil != null)
                        {
                            $this->importSanitizeResultsUtil->setMatchedModel($matchedModels[0]);
                        }
                    }
                }
            }
        }

        /**
         * Gets matched models
         * @param $value
         * @param int $pageSize
         * @return array
         */
        protected function getMatchedModels($value, $pageSize)
        {
            return array();
        }
    }
?>