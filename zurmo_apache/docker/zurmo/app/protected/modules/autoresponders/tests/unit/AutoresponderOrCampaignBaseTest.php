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
    class AutoresponderOrCampaignBaseTest extends ZurmoBaseTest
    {
        protected function processDueItem(OwnedModel & $item)
        {
            $util       = $this->getItemUtil();
            $this->setupUtil($util);
            $processed = $util::processDueItem($item);
            if (!$processed)
            {
                throw new NotSupportedException("Unable to process Item");
            }
            // we clear cache just to be sure. We dont need this if we aren't using any non-ORM stuff such as raw
            // queries but still.
            $id     = $item->id;
            $class  = get_class($item);
            $item->forgetAll();
            $item   = $class::getById($id);
            return $processed;
        }

        protected function setupUtil($util)
        {
            $util::$folder                  = $this->resolveEmailFolder();
            $util::$returnPath              = $this->resolveReturnPathHeaderValue();
            $util::$ownerModelRelationName  = $this->resolveItemOwnerModelRelationName();
            $util::$itemTableName           = $this->getItemTableName();
            $util::$emailMessageForeignKey  = $this->getEmailMessageForeignKeyName();
            $util::$itemClass               = $this->getItemClassName();
        }

        protected function getClassName()
        {
            $className  = get_class($this);
            $className  = substr($className, 0, strpos($className, 'Item'));
            return $className;
        }

        protected function getItemClassName()
        {
            $className  = $this->getClassName() . 'Item';
            return $className;
        }

        protected function getItemUtil()
        {
            $utilClassName  = $this->getItemClassName() . 'sUtil';
            return $utilClassName;
        }

        protected function resolveEmailBoxName()
        {
            $itemClassName  = $this->getClassName();
            $box            = EmailBox::CAMPAIGNS_NAME;
            if ($itemClassName == "Autoresponder")
            {
                $box = EmailBox::AUTORESPONDERS_NAME;
            }
            return $box;
        }

        protected function getEmailMessageForeignKeyName()
        {
            $itemClassName  = $this->getItemClassName();
            return RedBeanModel::getForeignKeyName($itemClassName, 'emailMessage');
        }

        protected function getItemTableName()
        {
            $itemClassName  = $this->getItemClassName();
            return $itemClassName::getTableName();
        }

        protected function resolveItemOwnerModelRelationName()
        {
            $className      = $this->getClassName();
            $relationName   = strtolower($className);
            return $relationName;
        }

        protected function resolveReturnPathHeaderValue()
        {
            $returnPath = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceReturnPath');
            return $returnPath;
        }

        protected function resolveEmailFolder()
        {
            $boxName            = $this->resolveEmailBoxName();
            $box                = EmailBox::resolveAndGetByName($boxName);
            $folder             = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            return $folder;
        }
    }
?>