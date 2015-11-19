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
     * UpdateSchemaCommand allows the schema to be updated.  This is useful if you are developing
     * and make changes to metadata that affects the database schema.
     */
    class LessCompilerCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            // get theme colors to populate into help
            $colorNames = implode(', ', array_keys(Yii::app()->themeManager->getThemeColorNamesAndColors()));
            return <<<EOD
    USAGE
      zurmoc lessCompiler [whatToCompile] [whatToCompile] ...

    DESCRIPTION
      This command create css files based on less files. This job should probably be done as cronjob, maybe once per day.

    PARAMETERS
     * no required params

    Optional Parameters:
     * whatToCompile: which css files to build. Options are (all, base, or a specific theme: $colorNames). Default is all. Supports multiple themes or options.
EOD;
    }

        /**
         * Execute the action.
         * @param array $args
         * @return int|void
         */
        public function run($args)
        {
            set_time_limit('900');
            if (isset($args[0]))
            {
                foreach ($args as $arg)
                {
                    switch($arg)
                    {
                        case 'all':
                            Yii::app()->lessCompiler->compile();
                            break;
                        case 'base':
                            Yii::app()->lessCompiler->compileBaseFiles();
                            break;
                        default:
                            $themeNamesAndColors = Yii::app()->themeManager->getThemeColorNamesAndColors();
                            if (array_key_exists($arg, $themeNamesAndColors))
                            {
                                Yii::app()->lessCompiler->compileColorDependentLessFile($arg);
                            }
                            else
                            {
                                $this->usageError('Invalid theme name entered: ' + $arg);
                            }
                            break;
                    }
                }
            }
            else
            {
                Yii::app()->lessCompiler->compile();
            }
        }
    }
?>