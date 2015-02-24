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
     * Display email message to to, cc, and bcc recipients
     */
    class EmailMessageAllRecipientTypesElement extends Element implements DerivedElementInterface
    {
        const CC_BCC_FIELD_ID   = 'cc-bcc-fields';

        protected $toElement;

        protected $ccElement;

        protected $bccElement;

        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            parent::__construct($model, $attribute, $form, $params);
            // setup toElement
            $toParams = CMap::mergeArray($params, array('recipientType' => EmailMessageRecipient::TYPE_TO));
            $this->toElement = new OutgoingEmailMessageRecipientBaseElement($model, $attribute, $form, $toParams);
            // setup ccElement
            $ccParams = CMap::mergeArray($params, array('recipientType' => EmailMessageRecipient::TYPE_CC));
            $this->ccElement = new OutgoingEmailMessageRecipientBaseElement($model, $attribute, $form, $ccParams);
            // setup bccElement
            $bccParams = CMap::mergeArray($params, array('recipientType' => EmailMessageRecipient::TYPE_BCC));
            $this->bccElement = new OutgoingEmailMessageRecipientBaseElement($model, $attribute, $form, $bccParams);
        }

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof CreateEmailMessageForm');
            $toContent      = $this->toElement->render();
            $ccBccContent   = $this->renderCcBccFieldsWithLink();
            $content        = $toContent . $ccBccContent;
            return $content;
        }

        protected function renderShowCcBccLink()
        {
            $showCcBccLink = ZurmoHtml::link('Cc/Bcc', '#',
                array('onclick' => "js:$('#" . static::CC_BCC_FIELD_ID . "').show();" .
                                    "$('#cc-bcc-fields-link').hide(); return false;",
                        'id' => 'cc-bcc-fields-link',
                        'class' => 'more-panels-link'));
            return $showCcBccLink;
        }

        protected function renderCcBccFieldsWithLink()
        {
            $showCcBccLink      = $this->renderShowCcBccLink();
            $ccContent          = $this->ccElement->render();
            $bccContent         = $this->bccElement->render();
            $ccBccFieldsContent = ZurmoHtml::tag('div', array('id' => static::CC_BCC_FIELD_ID,
                                                                'style'   => 'display: none;'),
                                                        $ccContent . $bccContent);
            $content            = $ccBccFieldsContent . $showCcBccLink;
            return $content;
        }

        protected function renderError()
        {
            return null; // we handle errors for recipient fields in themselves
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getDisplayName();
            }
            else
            {
                return $this->form->labelEx($this->model,
                                            $this->attribute,
                                            array('for' => $this->getEditableInputId(),
                                                  'label' => $this->getDisplayName()));
            }
        }

        public static function getDisplayName()
        {
            return Zurmo::t('EmailMessagesModule', 'Recipients');
        }

        public static function getModelAttributeNames()
        {
            return array();
        }
    }
?>