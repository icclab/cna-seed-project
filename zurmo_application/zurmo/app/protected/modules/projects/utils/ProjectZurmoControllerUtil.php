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
     * Class helps support adding/removing relations accounts, contacts and opportunities while saving a project from a post.
     */
    class ProjectZurmoControllerUtil extends ModelHasFilesAndRelatedItemsZurmoControllerUtil
    {
        protected $projectAccounts;

        /**
         * @param object $model
         * @param attay $explicitReadWriteModelPermissions
         */
        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
            assert('$model instanceof Project');
            $postData = PostUtil::getData();
            if (isset($postData['ProjectAccountsForm']))
            {
                $this->projectAccounts = self::resolveProjectManyManyAccountsFromPost($model,
                                                               $postData['ProjectAccountsForm']);

                $this->projectContacts = self::resolveProjectManyManyContactsFromPost($model,
                                                               $postData['ProjectContactsForm']);

                $this->projectOpportunities = self::resolveProjectManyManyOpportunitiesFromPost($model,
                                                               $postData['ProjectOpportunitiesForm']);
            }
        }

        /**
         * Resolves the accounts sent via post request
         * @param Project $project
         * @param array $postData
         * @return array containing accounts
         */
        public static function resolveProjectManyManyAccountsFromPost(
                                    Project $project, $postData)
        {
            assert('$project instanceof Project');
            $newAccount = array();
            if (isset($postData['accountIds']) && strlen($postData['accountIds']) > 0)
            {
                $accountIds = explode(",", $postData['accountIds']);  // Not Coding Standard
                foreach ($accountIds as $accountId)
                {
                    $newAccount[$accountId] = Account::getById((int)$accountId);
                }
                if ($project->accounts->count() > 0)
                {
                    $project->accounts->removeAll();
                }
                //Now add missing accounts
                foreach ($newAccount as $account)
                {
                    $project->accounts->add($account);
                }
            }
            else
            {
                //remove all accounts
                $project->accounts->removeAll();
            }
            return $newAccount;
        }

        /**
         * Resolves the contacts sent via post request
         * @param Project $project
         * @param array $postData
         * @return array containing contacts
         */
        public static function resolveProjectManyManyContactsFromPost(
                                    Project $project, $postData)
        {
            assert('$project instanceof Project');
            $newContact = array();
            if (isset($postData['contactIds']) && strlen($postData['contactIds']) > 0)
            {
                $contactIds = explode(",", $postData['contactIds']);  // Not Coding Standard
                foreach ($contactIds as $contactId)
                {
                    $newContact[$contactId] = Contact::getById((int)$contactId);
                }
                if ($project->contacts->count() > 0)
                {
                    $project->contacts->removeAll();
                }
                //Now add missing contacts
                foreach ($newContact as $contact)
                {
                    $project->contacts->add($contact);
                }
            }
            else
            {
                //remove all contacts
                $project->contacts->removeAll();
            }
            return $newContact;
        }

        /**
         * Resolves the opportunities sent via post request
         * @param Project $project
         * @param array $postData
         * @return array containing opportunities
         */
        public static function resolveProjectManyManyOpportunitiesFromPost(
                                    Project $project, $postData)
        {
            assert('$project instanceof Project');
            $newOpportunity = array();
            if (isset($postData['opportunityIds']) && strlen($postData['opportunityIds']) > 0)
            {
                $opportunityIds = explode(",", $postData['opportunityIds']);  // Not Coding Standard
                foreach ($opportunityIds as $opportunityId)
                {
                    $newOpportunity[$opportunityId] = Opportunity::getById((int)$opportunityId);
                }
                if ($project->opportunities->count() > 0)
                {
                    $project->opportunities->removeAll();
                }
                //Now add missing contacts
                foreach ($newOpportunity as $opportunity)
                {
                    $project->opportunities->add($opportunity);
                }
            }
            else
            {
                //remove all opportunities
                $project->opportunities->removeAll();
            }
            return $newOpportunity;
        }

        /**
         * Get latest activity feed list view
         * @return ListView
         */
        public static function getProjectsLatestActivityFeedView($controller, $pageSize)
        {
            $project            = new Project(false);
            $searchForm         = new ProjectsSearchForm($project);
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                array(),
                'ProjectAuditEvent',
                'RedBeanModelDataProvider',
                'dateTime',
                true,
                $pageSize
            );
            $listView           = new ProjectsFeedListView(
                                   $controller->id,
                                   $controller->getModule()->getId(),
                                   get_class($searchForm->getModel()),
                                   $dataProvider,
                                   GetUtil::resolveSelectedIdsFromGet(),
                                   null,
                                   array(),
                                   $searchForm->getListAttributesSelector(),
                                   $searchForm->getKanbanBoard());
            return $listView;
        }
    }
?>