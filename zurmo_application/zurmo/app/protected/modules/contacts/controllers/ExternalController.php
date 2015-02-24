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

    class ContactsExternalController extends ZurmoModuleController
    {
        const CAPTCHA_VERIFY_URL  = 'http://www.google.com/recaptcha/api/verify';

        public function filters()
        {
            return array();
        }

        public function beforeAction($action)
        {
            Yii::app()->user->userModel = BaseControlUserConfigUtil::getUserToRunAs();
            return parent::beforeAction($action);
        }

        public function actionSourceFiles($id)
        {
            $formContentUrl          = Yii::app()->createAbsoluteUrl('contacts/external/form/', array('id' => $id));
            $renderFormFileUrl       = Yii::app()->getAssetManager()->getPublishedUrl(
                                       Yii::getPathOfAlias('application.core.views.assets') .
                                       DIRECTORY_SEPARATOR . 'renderExternalForm.js');
            if ($renderFormFileUrl === false || file_exists($renderFormFileUrl) === false)
            {
                $renderFormFileUrl   = Yii::app()->getAssetManager()->publish(
                                       Yii::getPathOfAlias('application.core.views.assets') .
                                       DIRECTORY_SEPARATOR . 'renderExternalForm.js');
            }
            $renderFormFileUrl      = Yii::app()->getRequest()->getHostInfo() . $renderFormFileUrl;
            $jsOutput               = "var formContentUrl = '" . $formContentUrl . "';";
            $jsOutput              .= "var externalFormScriptElement = document.createElement('script');
                                       externalFormScriptElement.src = '" . $renderFormFileUrl . "';
                                       document.getElementsByTagName('head')[0].appendChild(externalFormScriptElement);
                                       var captchaPostBackUrl = '" .
                                       Yii::app()->createAbsoluteUrl('contacts/external/validateCaptcha') . "';";
            $this->renderResponse($jsOutput);
        }

        public function actionForm($id)
        {
            $cs = Yii::app()->getClientScript();
            $cs->setIsolationMode();
            $contactWebForm = static::getModelAndCatchNotFoundAndDisplayError('ContactWebForm', intval($id));
            $metadata       = static::getMetadataByWebForm($contactWebForm);
            if ($contactWebForm->language !== null)
            {
                Yii::app()->language = $contactWebForm->language;
            }
            if (is_string($contactWebForm->submitButtonLabel) && !empty($contactWebForm->submitButtonLabel))
            {
                $metadata['global']['toolbar']['elements'][0]['label'] = $contactWebForm->submitButtonLabel;
            }
            $customDisplayLabels                     = ContactWebFormsUtil::getCustomDisplayLabels($contactWebForm);
            $customRequiredFields                    = ContactWebFormsUtil::getCustomRequiredFields($contactWebForm);
            $contactWebFormModelForm                 = new ContactWebFormsModelForm(new Contact());
            $contactWebFormModelForm->state          = $contactWebForm->defaultState;
            $contactWebFormModelForm->owner          = $contactWebForm->defaultOwner;
            $contactWebFormModelForm->googleWebTrackingId = Yii::app()->getRequest()->getPost(
                                                            ContactExternalEditAndDetailsView::GOOGLE_WEB_TRACKING_ID_FIELD);
            $contactWebFormModelForm->setCustomDisplayLabels($customDisplayLabels);
            $contactWebFormModelForm->setCustomRequiredFields($customRequiredFields);
            $contactWebFormModelForm->resolveRequiredValidatorsForModel($customRequiredFields);
            $postVariableName                        = get_class($contactWebFormModelForm);
            if (isset($_POST[$postVariableName]))
            {
                ContactWebFormsUtil::resolveHiddenAttributesForContactModel($postVariableName, $contactWebForm);
                $_POST[$postVariableName] = PostUtil::sanitizePostByDesignerTypeForSavingModel(
                                            $contactWebFormModelForm->getModel(), $_POST[$postVariableName]);
                $contactWebFormModelForm->setAttributes($_POST[$postVariableName]);
                $this->attemptToValidate($contactWebForm, $contactWebFormModelForm);
                $this->attemptToSaveModelFromPost($contactWebFormModelForm, null, false);
                if (isset($contactWebFormModelForm->getModel()->id) && intval($contactWebFormModelForm->getModel()->id) > 0)
                {
                    $this->resolveContactWebFormEntry($contactWebForm, $contactWebFormModelForm);
                    $controllerUtil = static::getZurmoControllerUtil();
                    $controllerUtil::setContactModelPermissionsByContactWebForm($contactWebFormModelForm->getModel(),
                                                                                $contactWebForm);
                    $responseData                        = array();
                    $responseData['redirectUrl']         = $contactWebForm->redirectUrl;
                    $this->renderResponse(CJSON::encode($responseData));
                }
            }
            else
            {
                $containedView                           = new ContactExternalEditAndDetailsView('Edit',
                                                               $this->getId(),
                                                               $this->getModule()->getId(),
                                                               $this->attemptToSaveModelFromPost($contactWebFormModelForm,
                                                                                                null, false),
                                                               $metadata);
                $view = new ContactWebFormsExternalPageView(ZurmoExternalViewUtil::
                                                            makeExternalViewForCurrentUser($containedView));
                $cs->registerScript('catchGoogleWebTrackingId', "
                                    $(document).ready(function()
                                    {
                                        $('html').addClass('zurmo-embedded-form-active');
                                        if (typeof ga !== 'undefined')
                                        {
                                            ga(function(tracker)
                                            {
                                                var googleWebTrackingId = tracker.get('clientId');
                                                $('#" . ContactExternalEditAndDetailsView::GOOGLE_WEB_TRACKING_ID_FIELD .
                                                "').val(googleWebTrackingId);
                                            });
                                        }
                                    });");
                $excludeStyles                           = $contactWebForm->excludeStyles;
                $rawXHtml                                = $view->render();
                $rawXHtml                                = ZurmoExternalViewUtil::resolveAndCombineScripts($rawXHtml);
                $combinedHtml                            = array();
                $combinedHtml['head']                    = ZurmoExternalViewUtil::resolveHeadTag($rawXHtml, $excludeStyles);
                $combinedHtml['body']                    = ZurmoExternalViewUtil::resolveHtmlAndScriptInBody($rawXHtml);
                if (isset($contactWebForm->enableCaptcha) && $contactWebForm->enableCaptcha == true)
                {
                    $combinedHtml['enableCaptcha']       = true;
                }
                else
                {
                    $combinedHtml['enableCaptcha']       = false;
                }
                $response = 'renderFormCallback('. CJSON::encode($combinedHtml) . ');';
                $this->renderResponse($response);
            }
        }

        protected function attemptToValidate(ContactWebForm $contactWebForm,
                                             ContactWebFormsModelForm $contactWebFormModelForm)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'edit-form')
            {
                if (isset($contactWebForm->enableCaptcha) && $contactWebForm->enableCaptcha == true)
                {
                    if (!empty($_POST['captchaHash']) && $_POST['captchaHash'] == md5('ContactWebFormModelForm' .
                                                                                       ZURMO_TOKEN))
                    {
                        $this->validateContactWebFormModelForm($contactWebForm, $contactWebFormModelForm);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
                else
                {
                    $this->validateContactWebFormModelForm($contactWebForm, $contactWebFormModelForm);
                }
            }
        }

        protected function validateContactWebFormModelForm(ContactWebForm $contactWebForm,
                                                           ContactWebFormsModelForm $contactWebFormModelForm)
        {
            $this->resolveContactWebFormEntry($contactWebForm, $contactWebFormModelForm);
            if ($contactWebFormModelForm->validate())
            {
                $response = CJSON::encode(array());
            }
            else
            {
                $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($contactWebFormModelForm);
                $response = CJSON::encode($errorData);
            }
            $this->renderResponse($response);
        }

        protected function resolveContactWebFormEntry(ContactWebForm $contactWebForm,
                                                      ContactWebFormsModelForm $contactWebFormModelForm)
        {
            $postVariableName                    = get_class($contactWebFormModelForm);
            $contactFormAttributes               = $_POST[$postVariableName];
            $contactFormAttributes               = ContactWebFormsUtil::resolveHiddenAttributesForContactWebFormEntryModel(
                                                   $contactFormAttributes, $contactWebForm);
            $contactFormAttributes['owner']      = $contactWebForm->defaultOwner->id;
            $contactFormAttributes['state']      = $contactWebForm->defaultState->id;
            if ($contactWebFormModelForm->validate())
            {
                $contactWebFormEntryStatus       = ContactWebFormEntry::STATUS_SUCCESS;
                $contactWebFormEntryMessage      = ContactWebFormEntry::STATUS_SUCCESS_MESSAGE;
            }
            else
            {
                $contactWebFormEntryStatus       = ContactWebFormEntry::STATUS_ERROR;
                $contactWebFormEntryMessage      = ContactWebFormEntry::STATUS_ERROR_MESSAGE;
            }
            if (isset($contactWebFormModelForm->getModel()->id) && intval($contactWebFormModelForm->getModel()->id) > 0)
            {
                $contactWebFormEntryContact      = $contactWebFormModelForm->getModel();
            }
            else
            {
                $contactWebFormEntryContact      = null;
            }
            $hashIndex                           = Yii::app()->getRequest()->getPost(
                                                   ContactWebFormEntry::HASH_INDEX_HIDDEN_FIELD);
            $contactWebFormEntry                 = ContactWebFormEntry::getByHashIndex($hashIndex);
            if ($contactWebFormEntry === null)
            {
                $contactWebFormEntry             = new ContactWebFormEntry();
            }
            $contactWebFormEntry->serializedData = serialize($contactFormAttributes);
            $contactWebFormEntry->status         = $contactWebFormEntryStatus;
            $contactWebFormEntry->message        = $contactWebFormEntryMessage;
            $contactWebFormEntry->contactWebForm = $contactWebForm;
            $contactWebFormEntry->contact        = $contactWebFormEntryContact;
            $contactWebFormEntry->hashIndex      = $hashIndex;
            $contactWebFormEntry->save();
        }

        protected function renderResponse($responseContent)
        {
            header('Access-Control-Allow-Origin: *');
            header("content-type: application/json");
            echo $responseContent;
            Yii::app()->end(0, false);
        }

        public static function getMetadataByWebForm(ContactWebForm $contactWebForm)
        {
            assert('$contactWebForm instanceof ContactWebForm');
            $contactWebFormAttributes = array_keys(ContactWebFormsUtil::getPlacedAttributes($contactWebForm));
            $contactWebFormAttributes = ContactWebFormsUtil::excludeHiddenAttributes($contactWebForm,
                                                                                     $contactWebFormAttributes);
            $viewClassName            = 'ContactExternalEditAndDetailsView';
            $moduleClassName          = 'ContactsModule';
            $modelClassName           = $moduleClassName::getPrimaryModelName();
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRules            = new EditAndDetailsViewDesignerRules();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName,
                                                                                           $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter  = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter($attributeCollection,
                                        $designerRules,
                                        $editableMetadata);
            $layoutMetadataAdapter    = new LayoutMetadataAdapter(
                                            $viewClassName,
                                            $moduleClassName,
                                            $editableMetadata,
                                            $designerRules,
                                            $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                                            $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes());
            $metadata                 = $layoutMetadataAdapter->resolveMetadataFromSelectedListAttributes(
                                                                $viewClassName, $contactWebFormAttributes);
            foreach ($metadata['global']['panels'][0]['rows'] as $index => $data)
            {
                if ($data['cells'][0]['elements'][0]['type'] == 'EmailAddressInformation')
                {
                    $metadata['global']['panels'][0]['rows'][$index]['cells'][0]['elements'][0]['hideOptOut'] = true;
                }
                if ($data['cells'][0]['elements'][0]['type'] == 'TagCloud')
                {
                    $metadata['global']['panels'][0]['rows'][$index]['cells'][0]['elements'][0]['type'] =
                    'MultiSelectDropDown';
                }
            }
            return $metadata;
        }

        public function actionValidateCaptcha()
        {
            if (isset($_POST['recaptcha_response_field']))
            {
                $data               = array();
                $data['privatekey'] = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'reCaptchaPrivateKey');
                $data['remoteip']   = $_SERVER['REMOTE_ADDR'];
                $data['challenge']  = $_POST['recaptcha_challenge_field'];
                $data['response']   = $_POST['recaptcha_response_field'];
                $fields = '';
                foreach ($data as $key => $value)
                {
                    $fields .= $key . '=' . $value . '&';
                }
                rtrim($fields, '&');
                $post     = curl_init();
                curl_setopt($post, CURLOPT_URL, static::CAPTCHA_VERIFY_URL);
                curl_setopt($post, CURLOPT_POST, count($data));
                curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
                $output   = curl_exec($post);
                curl_close($post);
                $response = array();
                if (strpos($output, 'true') !== false && strpos($output, 'success') !== false)
                {
                    $response['status']                 = 'success';
                    $response['captchaHash']            = md5('ContactWebFormModelForm' . ZURMO_TOKEN);
                }
                else
                {
                    $response['status']                 = 'invalid';
                    $response['captchaHash']            = null;
                }
                $this->renderResponse(CJSON::encode($response));
            }
        }

        protected static function getZurmoControllerUtil()
        {
            return new ContactWebFormsZurmoControllerUtil();
        }
    }
?>