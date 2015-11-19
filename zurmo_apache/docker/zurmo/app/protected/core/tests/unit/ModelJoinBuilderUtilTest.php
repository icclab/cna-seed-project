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

    class ModelJoinBuilderUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            static::buildRelationModels();
        }

        public static function getDependentTestModelClassNames()
        {
            return array('TestModelJoinHasManyAndHasOneBelongsToSide', 'TestModelJoinHasManySide',
                'TestModelJoinHasOneSide', 'TestModelJoinManyManySide', 'TestModelJoinManyManySideTwo');
        }

        protected function validJoinHelper($modelClassName, $attributeName, $relatedAttributeName)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => $attributeName,
                    'relatedAttributeName' => $relatedAttributeName,
                    'operatorType'         => OperatorRules::TYPE_IS_NOT_NULL,
                    'value'                => null
                )
            );
            $searchAttributeData['structure'] = '1';

            $searchAttributeDataAndClassNames   = array(
                array($modelClassName => $searchAttributeData)
            );

            $sql = RedBeanModelsDataProvider::makeUnionSql( $searchAttributeDataAndClassNames,
                null,
                true);

            $result = ZurmoRedBean::GetAll($sql);

            $this->assertTrue(is_array($result));
        }

        public function testGettingValidJoinForHasManyBelongsTo()
        {
            $model                  = 'TestModelJoinHasManyAndHasOneBelongsToSide';
            $attributeName          = 'testModelJoinHasManySide';
            $relatedAttributeName   = 'hasManyField';

            $this->validJoinHelper($model, $attributeName, $relatedAttributeName);
        }

        public function testGettingValidJoinForHasOneBelongsTo()
        {
            $model                  = 'TestModelJoinHasManyAndHasOneBelongsToSide';
            $attributeName          = 'testModelJoinHasOneSide';
            $relatedAttributeName   = 'hasOneField';

            $this->validJoinHelper($model, $attributeName, $relatedAttributeName);
        }

        public function testGettingValidJoinForHasOne()
        {
            $model                  = 'TestModelJoinHasOneSide';
            $attributeName          = 'testModelJoinHasManyAndHasOneBelongsToSide';
            $relatedAttributeName   = 'hasManyAndHasOneField';

            $this->validJoinHelper($model, $attributeName, $relatedAttributeName);
        }

        public function testGettingValidJoinForHasMany()
        {
            $model                  = 'TestModelJoinHasManySide';
            $attributeName          = 'testHasMany';
            $relatedAttributeName   = 'hasManyAndHasOneField';

            $this->validJoinHelper($model, $attributeName, $relatedAttributeName);
        }

        public function testGettingValidJoinForManyMany()
        {
            $model                  = 'TestModelJoinManyManySide';
            $attributeName          = 'testModelJoinManyManySideTwos';
            $relatedAttributeName   = 'manyManyTwoField';

            $this->validJoinHelper($model, $attributeName, $relatedAttributeName);
        }

        protected static function buildRelationModels()
        {
            $modelForListing1                                   = new TestModelJoinHasManyAndHasOneBelongsToSide();
            $modelForListing1->name                             = "ModelForListing1";
            $modelForListing1->hasManyAndHasOneField            = "belongsTo1";
            $modelForListing2                                   = new TestModelJoinHasManyAndHasOneBelongsToSide();
            $modelForListing2->name                             = "ModelForListing2";
            $modelForListing2->hasManyAndHasOneField            = "belongsTo2";
            $modelHasManyOfListItem                             = new TestModelJoinHasManySide();
            $modelHasManyOfListItem->name                       = "Has List Items 1 and 2";
            $modelHasManyOfListItem->hasManyField               = "hasMany";
            $modelHasOneOfListItem                              = new TestModelJoinHasOneSide();
            $modelHasOneOfListItem->name                        = "Has List Item 1";
            $modelHasOneOfListItem->hasOneField                 = "hasOne";
            $modelManyManyItem1                                 = new TestModelJoinManyManySide();
            $modelManyManyItem1->name                           = "Many Many 1-1";
            $modelManyManyItem1->manyManyField                  = "hasMany1";
            $modelManyManyItem2                                 = new TestModelJoinManyManySide();
            $modelManyManyItem2->name                           = "Many Many 1-2";
            $modelManyManyItem2->manyManyField                  = "hasMany1";
            $modelManyManySideTwoItem1                          = new TestModelJoinManyManySideTwo();
            $modelManyManySideTwoItem1->name                    = "Many Many 2-1";
            $modelManyManySideTwoItem1->manyManyTwoField        = "hasMany2";
            $modelManyManySideTwoItem2                          = new TestModelJoinManyManySideTwo();
            $modelManyManySideTwoItem2->name                    = "Many Many 2-2";
            $modelManyManySideTwoItem2->manyManyTwoField        = "hasMany2";

            $modelHasManyOfListItem->testHasMany->add($modelForListing1);
            $modelHasManyOfListItem->testHasMany->add($modelForListing2);
            $modelHasOneOfListItem->testModelJoinHasManyAndHasOneBelongsToSide = $modelForListing1;
            $modelManyManyItem1->testModelJoinManyManySideTwos->add($modelManyManySideTwoItem1);
            $modelManyManyItem1->testModelJoinManyManySideTwos->add($modelManyManySideTwoItem2);
            $modelManyManySideTwoItem1->testModelJoinManyManySides->add($modelManyManyItem1);
            $modelManyManySideTwoItem1->testModelJoinManyManySides->add($modelManyManyItem2);

            $saved = $modelHasManyOfListItem->save();
            assert('$saved');

            $saved = $modelHasOneOfListItem->save();
            assert('$saved');

            $saved = $modelManyManyItem1->save();
            assert('$saved');

            $saved = $modelManyManySideTwoItem1->save();
            assert('$saved');
        }
    }
?>