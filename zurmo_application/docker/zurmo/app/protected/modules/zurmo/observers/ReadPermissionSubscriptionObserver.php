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
     * Helps manage observation events on various classes.  Inspects modules for their primary model and detects
     * if hasReadPermissionsSubscriptionOptimization is present.
     * If it is then it will attempt to add queue jobs on various events to support
     * ReadPermissionsSubscriptionOptimization
     */
    class ReadPermissionSubscriptionObserver extends CComponent
    {
        /**
         * Change it to false if we don't want to observe for events in some special case
         * @var bool
         */
        public $enabled = true;

        /**
         * Initialize the observer if enabled.  Attaches events to model classes as needed.
         */
        public function init()
        {
            if ($this->enabled)
            {
                $observedModels = array();
                $modules = Module::getModuleObjects();
                foreach ($modules as $module)
                {
                    try
                    {
                        $modelClassName = $module->getPrimaryModelName();
                        if ($modelClassName != null &&
                            is_subclass_of($modelClassName, 'OwnedSecurableItem') &&
                            $modelClassName::hasReadPermissionsSubscriptionOptimization() === true &&
                                !in_array($modelClassName, $observedModels))
                        {
                            $observedModels[]           = $modelClassName;
                            $this->attachEventsByModelClassName($modelClassName);
                        }
                    }
                    catch (NotSupportedException $e)
                    {
                    }
                }
            }
        }

        /**
         * Given a model class name attach readPermissionsSubscriptionOptimization events to that class.
         * Every model will then invoke the readPermissionsSubscriptionOptimization event.
         * @param string $modelClassName
         */
        public function attachEventsByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            $modelClassName::model()->attachEventHandler('onAfterOwnerChangeAfterSave',
                                        array($this, 'readPermissionSubscriptionOnAfterOwnerChangeAfterSave'));
            $modelClassName::model()->attachEventHandler('onAfterSave',
                                        array($this, 'readPermissionSubscriptionOnAfterSave'));
            $modelClassName::model()->attachEventHandler('onAfterDelete',
                                        array($this, 'readPermissionSubscriptionOnAfterDelete'));
        }

        /**
         * Given a event, perform the onOwnerChange logic for a model ($event->sender)
         * @param CEvent $event
         * @return bool
         */
        public function readPermissionSubscriptionOnAfterOwnerChangeAfterSave(CEvent $event)
        {
            if ($this->enabled)
            {
                if ($event->sender->id > 0)
                {
                    if (get_class($event->sender) == 'Account')
                    {
                        ReadPermissionsSubscriptionUtil::updateAccountReadSubscriptionTableBasedOnBuildTable($event->sender->id);
                    }
                    else
                    {
                        ReadPermissionsSubscriptionUtil::changeOwnerOfModelInReadSubscriptionTableByModelIdAndModelClassNameAndUser(
                            $event->sender->id,
                            get_class($event->sender),
                            $event->sender->owner
                        );
                    }
                }
            }
            return true;
        }

        /**
         * @param CEvent $event
         * @return bool
         */
        public function readPermissionSubscriptionOnAfterSave(CEvent $event)
        {
            if ($this->enabled)
            {
                if ($event->sender->getIsNewModel())
                {
                    if (get_class($event->sender) == 'Account')
                    {
                        ReadPermissionsSubscriptionUtil::updateAccountReadSubscriptionTableBasedOnBuildTable($event->sender->id);
                    }
                    else
                    {
                        ReadPermissionsSubscriptionUtil::addModelToReadSubscriptionTableByModelIdAndModelClassNameAndUser(
                            $event->sender->id,
                            get_class($event->sender),
                            $event->sender->owner
                        );
                    }
                }
                elseif (!$event->sender->getIsNewModel() && get_class($event->sender) == 'Account' &&
                        $event->sender->arePermissionsChanged())
                {
                    // When read permissions for account are changed, for example when group can access account
                    ReadPermissionsSubscriptionUtil::updateAccountReadSubscriptionTableBasedOnBuildTable($event->sender->id);
                }
            }
            return true;
        }

        /**
         * @param CEvent $event
         * @return bool
         */
        public function readPermissionSubscriptionOnAfterDelete(CEvent $event)
        {
            if ($this->enabled)
            {
                if (get_class($event->sender) == 'Account')
                {
                    ReadPermissionsSubscriptionUtil::updateAccountReadSubscriptionTableBasedOnBuildTable($event->sender->id);
                }
                else
                {
                    ReadPermissionsSubscriptionUtil::deleteModelFromReadSubscriptionTableByModelIdAndModelClassNameAndUser(
                        $event->sender->id,
                        get_class($event->sender),
                        $event->sender->owner
                    );
                }
            }
            return true;
        }
    }
?>