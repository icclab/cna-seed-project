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

    class EmailTemplatesDefaultDataMaker extends EmailTemplatesBaseDefaultDataMaker
    {
        public function make()
        {
            $this->makeImages();
            $this->makeBlank();
            $this->makeOneColumn();
            $this->makeTwoColumns();
            $this->makeTwoColumnsWithStrongRight();
            $this->makeThreeColumns();
            $this->makeThreeColumnsWithHero();
            return true;
        }

        protected function makeBlank()
        {
            $name              = 'Blank';
            $unserializedData  = array (
                'baseTemplateId' => '',
                'icon' => 'icon-template-0',
                'dom' =>
                    array (
                        'canvas1' =>
                            array (
                                'content' =>
                                    array (
                                        'builderrowelement_1393965668_53163a6448794' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965668_53163a644866d' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1',
                                                            ),
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                    ),
                                'properties' =>
                                    array (
                                        'frontend' =>
                                            array (
                                                'inlineStyles' =>
                                                    array (
                                                        'background-color' => '#ffffff',
                                                        'color' => '#545454',
                                                    ),
                                            ),
                                    ),
                                'class' => 'BuilderCanvasElement',
                            ),
                    ),
            );
            $this->makeBuilderPredefinedEmailTemplate($name, $unserializedData);
        }

        protected function makeOneColumn()
        {
            $name              = '1 Column';
            $unserializedData  = array (
                'baseTemplateId' => '',
                'icon' => 'icon-template-5',
                'dom' =>
                    array (
                        'canvas1' =>
                            array (
                                'content' =>
                                    array (
                                        'builderheaderimagetextelement_1393965594_53163a1a0eb53' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965594_53163a1a0ef48' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1393965594_53163a1a0ee52' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x50'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1393965594_53163a1a145cc' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderheadertextelement_1393965594_53163a1a14515' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Acme Inc. Newsletter',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#ffffff',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'right',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderHeaderTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1:2',
                                                                'header' => '1',
                                                            ),
                                                        'frontend' =>
                                                            array (
                                                                'inlineStyles' =>
                                                                    array (
                                                                        'background-color' => '#282a76',
                                                                    ),
                                                            ),
                                                    ),
                                                'class' => 'BuilderHeaderImageTextElement',
                                            ),
                                        'builderrowelement_1393965668_53163a6448794' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965668_53163a644866d' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertitleelement_1393965668_53163a6447762' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Hello there William S...',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#666666',
                                                                                                        'font-size' => '24',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'center',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderimageelement_1393970522_53164d5a3787a' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['580x180'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'builderexpanderelement_1393970557_53164d7d2881e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'height' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderExpanderElement',
                                                                            ),
                                                                        'buildertextelement_1393965781_53163ad53b77c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '
<p>
    Orsino, the <i>Duke of Illyria</i>, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: "If music be the food of love, play on." It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that <b>Olivia</b> plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.
</p>
',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderTextElement',
                                                                            ),
                                                                        'builderbuttonelement_1393965942_53163b76e666c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'text' => 'Call Me',
                                                                                                'sizeClass' => 'medium-button',
                                                                                                'align' => 'left',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'href' => 'http://localhost/Zurmo/app/index.php',
                                                                                                'target' => '_blank',
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#97c43d',
                                                                                                        'border-color' => '#7cb830',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderButtonElement',
                                                                            ),
                                                                        'builderdividerelement_1393965948_53163b7cb98ae' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'border-top-width' => '1',
                                                                                                        'border-top-style' => 'solid',
                                                                                                        'border-top-color' => '#cccccc',
                                                                                                    ),
                                                                                            ),
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'divider-padding' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderDividerElement',
                                                                            ),
                                                                        'buildersocialelement_1394060039_5317ab07cf03d' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'layout' => 'vertical',
                                                                                                'services' =>
                                                                                                    array (
                                                                                                        'Twitter' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.twitter.com/',
                                                                                                            ),
                                                                                                        'Facebook' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.facebook.com/',
                                                                                                            ),
                                                                                                        'GooglePlus' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://gplus.com',
                                                                                                            ),
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderSocialElement',
                                                                            ),
                                                                        'builderexpanderelement_1393970592_53164da0bd137' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'height' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderExpanderElement',
                                                                            ),
                                                                        'builderfooterelement_1393966090_53163c0ac51bd' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '[[GLOBAL^MARKETING^FOOTER^HTML]]',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#efefef',
                                                                                                        'font-size' => '10',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderFooterElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                    ),
                                'properties' =>
                                    array (
                                        'frontend' =>
                                            array (
                                                'inlineStyles' =>
                                                    array (
                                                        'background-color' => '#ffffff',
                                                        'color' => '#545454',
                                                    ),
                                            ),
                                    ),
                                'class' => 'BuilderCanvasElement',
                            ),
                    ),
            );
            $this->makeBuilderPredefinedEmailTemplate($name, $unserializedData);
        }

        protected function makeTwoColumns()
        {
            $name              = '2 Columns';
            $unserializedData  = array (
                'baseTemplateId' => '',
                'icon' => 'icon-template-2',
                'dom' =>
                    array (
                        'canvas1' =>
                            array (
                                'content' =>
                                    array (
                                        'builderheaderimagetextelement_1393965594_53163a1a0eb53' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965594_53163a1a0ef48' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1393965594_53163a1a0ee52' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x50'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1393965594_53163a1a145cc' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderheadertextelement_1393965594_53163a1a14515' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Acme Inc. Newsletter',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#ffffff',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'right',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderHeaderTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1:2',
                                                                'header' => '1',
                                                            ),
                                                        'frontend' =>
                                                            array (
                                                                'inlineStyles' =>
                                                                    array (
                                                                        'background-color' => '#282a76',
                                                                    ),
                                                            ),
                                                    ),
                                                'class' => 'BuilderHeaderImageTextElement',
                                            ),
                                        'builderrowelement_1394062546_5317b4d264a62' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062546_5317b4d26488b' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertitleelement_1394062546_5317b4d263942' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Hello there William S...',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h1',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#666666',
                                                                                                        'font-size' => '28',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'line-height' => '200',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1393965668_53163a6448794' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965668_53163a644866d' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertextelement_1393965781_53163ad53b77c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '
<p>
    Orsino, the <i>Duke of Illyria</i>, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: "If music be the food of love, play on." It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that <b>Olivia</b> plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.
</p>
',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderTextElement',
                                                                            ),
                                                                        'builderbuttonelement_1393965942_53163b76e666c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'text' => 'Contact Us Now',
                                                                                                'sizeClass' => 'medium-button',
                                                                                                'align' => 'left',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'href' => 'http://localhost/Zurmo/app/index.php',
                                                                                                'target' => '_blank',
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#97c43d',
                                                                                                        'border-color' => '#7cb830',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderButtonElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1394061698_5317b182c1f19' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertextelement_1394061967_5317b28fc8088' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '
<b>New Articles</b>
<ul>
    <li>Article Name about something</li>
    <li>10 ways to create email templates</li>
    <li>Great new marketing tools from Acme</li>
    <li>Best blog post of the year</li>
    <li>Meet our new chef</li>
</ul>
',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#f6f6f7',
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '16',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTextElement',
                                                                            ),
                                                                        'builderexpanderelement_1394062193_5317b37137abc' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'height' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderExpanderElement',
                                                                            ),
                                                                        'buildertitleelement_1394062361_5317b419e1c51' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Acme Elsewhere',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#6c1d1d',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'line-height' => '200',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'buildersocialelement_1394060039_5317ab07cf03d' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'layout' => 'vertical',
                                                                                                'services' =>
                                                                                                    array (
                                                                                                        'Twitter' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.twitter.com/',
                                                                                                            ),
                                                                                                        'Facebook' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.facebook.com/',
                                                                                                            ),
                                                                                                        'GooglePlus' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://gplus.com',
                                                                                                            ),
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderSocialElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '2',
                                                            ),
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062652_5317b53c906f9' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062652_5317b53c90615' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderdividerelement_1394062652_5317b53c901fc' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'border-top-width' => '1',
                                                                                                        'border-top-style' => 'dotted',
                                                                                                        'border-top-color' => '#efefef',
                                                                                                    ),
                                                                                            ),
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'divider-padding' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderDividerElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062641_5317b53112a36' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062641_5317b5311291a' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderfooterelement_1394062641_5317b5311226e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '[[GLOBAL^MARKETING^FOOTER^HTML]]',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'font-size' => '11',
                                                                                                        'background-color' => '#ebebeb',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderFooterElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                    ),
                                'properties' =>
                                    array (
                                        'frontend' =>
                                            array (
                                                'inlineStyles' =>
                                                    array (
                                                        'background-color' => '#ffffff',
                                                        'color' => '#545454',
                                                    ),
                                            ),
                                    ),
                                'class' => 'BuilderCanvasElement',
                            ),
                    ),
            );
            $this->makeBuilderPredefinedEmailTemplate($name, $unserializedData);
        }

        protected function makeTwoColumnsWithStrongRight()
        {
            $name              = '2 Columns with strong right';
            $unserializedData  = array (
                'baseTemplateId' => '',
                'icon' => 'icon-template-3',
                'dom' =>
                    array (
                        'canvas1' =>
                            array (
                                'content' =>
                                    array (
                                        'builderheaderimagetextelement_1393965594_53163a1a0eb53' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965594_53163a1a0ef48' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1393965594_53163a1a0ee52' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x50'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1393965594_53163a1a145cc' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderheadertextelement_1393965594_53163a1a14515' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Acme Inc. Newsletter',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#ffffff',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'right',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderHeaderTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1:2',
                                                                'header' => '1',
                                                            ),
                                                        'frontend' =>
                                                            array (
                                                                'inlineStyles' =>
                                                                    array (
                                                                        'background-color' => '#282a76',
                                                                    ),
                                                            ),
                                                    ),
                                                'class' => 'BuilderHeaderImageTextElement',
                                            ),
                                        'builderrowelement_1394062546_5317b4d264a62' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062546_5317b4d26488b' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertitleelement_1394062546_5317b4d263942' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Hello there William S...',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h1',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#666666',
                                                                                                        'font-size' => '28',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'line-height' => '200',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1393965668_53163a6448794' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965668_53163a644866d' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertextelement_1394061967_5317b28fc8088' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
// Begin Not Coding Standard
                                                                                        'text' => '
 <b>New Products</b>
<ul>
    <li><a href="#" target="_blank">AcmeMaster 10,000</a></li>
    <li><a href="#">ProAcme 5,000</a></li>
    <li><a href="#">AcmeMaster++</a></li>
    <li><a href="#" target="_blank">The Acme Beginner pro</a></li>
</ul>
',
// End Not Coding Standard
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#f6f6f7',
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '16',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTextElement',
                                                                            ),
                                                                        'buildertitleelement_1394062361_5317b419e1c51' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Follow Us!',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#6c1d1d',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'line-height' => '200',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'buildersocialelement_1394060039_5317ab07cf03d' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'layout' => 'vertical',
                                                                                                'services' =>
                                                                                                    array (
                                                                                                        'Twitter' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.twitter.com/',
                                                                                                            ),
                                                                                                        'Facebook' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.facebook.com/',
                                                                                                            ),
                                                                                                        'GooglePlus' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://gplus.com',
                                                                                                            ),
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderSocialElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1394061698_5317b182c1f19' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertextelement_1393965781_53163ad53b77c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '
<p>
    Orsino, the <i>Duke of Illyria</i>, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: "If music be the food of love, play on." It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that <b>Olivia</b> plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.
</p>
',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderTextElement',
                                                                            ),
                                                                        'builderbuttonelement_1393965942_53163b76e666c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'text' => 'Contact Us Now',
                                                                                                'sizeClass' => 'medium-button',
                                                                                                'align' => 'left',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'href' => 'http://localhost/Zurmo/app/index.php',
                                                                                                'target' => '_blank',
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#97c43d',
                                                                                                        'border-color' => '#7cb830',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderButtonElement',
                                                                            ),
                                                                        'builderexpanderelement_1394062193_5317b37137abc' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'height' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderExpanderElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1:2',
                                                            ),
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062652_5317b53c906f9' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062652_5317b53c90615' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderdividerelement_1394062652_5317b53c901fc' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'border-top-width' => '1',
                                                                                                        'border-top-style' => 'dotted',
                                                                                                        'border-top-color' => '#efefef',
                                                                                                    ),
                                                                                            ),
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'divider-padding' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderDividerElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062641_5317b53112a36' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062641_5317b5311291a' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderfooterelement_1394062641_5317b5311226e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '[[GLOBAL^MARKETING^FOOTER^HTML]]',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'font-size' => '11',
                                                                                                        'background-color' => '#ebebeb',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderFooterElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                    ),
                                'properties' =>
                                    array (
                                        'frontend' =>
                                            array (
                                                'inlineStyles' =>
                                                    array (
                                                        'background-color' => '#ffffff',
                                                        'color' => '#545454',
                                                    ),
                                            ),
                                    ),
                                'class' => 'BuilderCanvasElement',
                            ),
                    ),
            );
            $this->makeBuilderPredefinedEmailTemplate($name, $unserializedData);
        }

        protected function makeThreeColumns()
        {
            $name              = '3 Columns';
            $unserializedData  = array (
                'baseTemplateId' => '',
                'icon' => 'icon-template-4',
                'dom' =>
                    array (
                        'canvas1' =>
                            array (
                                'content' =>
                                    array (
                                        'builderheaderimagetextelement_1393965594_53163a1a0eb53' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965594_53163a1a0ef48' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1393965594_53163a1a0ee52' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x50'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1393965594_53163a1a145cc' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderheadertextelement_1393965594_53163a1a14515' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Acme Inc. Newsletter',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#ffffff',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'right',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderHeaderTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1:2',
                                                            ),
                                                        'frontend' =>
                                                            array (
                                                                'inlineStyles' =>
                                                                    array (
                                                                        'background-color' => '#282a76',
                                                                    ),
                                                            ),
                                                    ),
                                                'class' => 'BuilderHeaderImageTextElement',
                                            ),
                                        'builderrowelement_1394062546_5317b4d264a62' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062546_5317b4d26488b' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertitleelement_1394062546_5317b4d263942' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Latest entries on our database',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h1',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#666666',
                                                                                                        'font-size' => '28',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'line-height' => '200',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1393965668_53163a6448794' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965668_53163a644866d' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394063801_5317b9b9eedc5' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x200'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'buildertitleelement_1394063416_5317b838c6ce1' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Property at NYC',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h2',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '18',
                                                                                                        'font-family' => 'Georgia',
                                                                                                        'font-weight' => 'bold',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderplaintextelement_1394063772_5317b99cab31e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Orsino, the Duke of Illyria, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: "If music be the food of love, play on." It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that Olivia plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderPlainTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1394061698_5317b182c1f19' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394063806_5317b9be406a3' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x200'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'buildertitleelement_1394063420_5317b83cb81a3' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Chalet in Bs. As.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '18',
                                                                                                        'font-family' => 'Georgia',
                                                                                                        'font-weight' => 'bold',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderplaintextelement_1394063737_5317b979ce2a3' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Orsino, the Duke of Illyria, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: "If music be the food of love, play on." It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that Olivia plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderPlainTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1394063404_5317b82c72b5c' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394063809_5317b9c1da156' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x200'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'buildertitleelement_1394063425_5317b8410f24b' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Tiny Island',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '18',
                                                                                                        'font-family' => 'Georgia',
                                                                                                        'font-weight' => 'bold',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderplaintextelement_1394063741_5317b97d68d8d' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Orsino, the Duke of Illyria, is consumed by his passion for the melancholy Countess Olivia. His ostentatious musings on the nature of love begin with what has become one of Shakespeare\'s most famous lines: "If music be the food of love, play on." It is apparent that Orsino\'s love is hollow. He is a romantic dreamer, for whom the idea of being in love is most important. When Valentine gives him the terrible news that Olivia plans to seclude herself for seven years to mourn her deceased brother, Orsino seems unfazed, and hopes Olivia may one day be as bewitched by love (the one self king) as he. Fittingly, the scene ends with Orsino off to lay in a bed of flowers, where he can be alone with his love-thoughts. Later in the play it will be up to Viola to teach Orsino the true meaning of love.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderPlainTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '3',
                                                            ),
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062652_5317b53c906f9' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062652_5317b53c90615' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderbuttonelement_1394063832_5317b9d8a797c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'text' => 'Click for more details',
                                                                                                'sizeClass' => 'large-button',
                                                                                                'width' => '100%',
                                                                                                'align' => 'center',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'href' => 'http://google.com',
                                                                                                'target' => '_blank',
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'background-color' => '#8224e3',
                                                                                                        'color' => '#ffffff',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'center',
                                                                                                        'border-color' => '#8224e3',
                                                                                                        'border-width' => '1',
                                                                                                        'border-style' => 'solid',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderButtonElement',
                                                                            ),
                                                                        'builderdividerelement_1394062652_5317b53c901fc' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'border-top-width' => '1',
                                                                                                        'border-top-style' => 'dotted',
                                                                                                        'border-top-color' => '#efefef',
                                                                                                    ),
                                                                                            ),
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'divider-padding' => '10',
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderDividerElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062641_5317b53112a36' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062641_5317b5311291a' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderfooterelement_1394062641_5317b5311226e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '[[GLOBAL^MARKETING^FOOTER^HTML]]',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'font-size' => '11',
                                                                                                        'background-color' => '#ebebeb',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderFooterElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                    ),
                                'properties' =>
                                    array (
                                        'frontend' =>
                                            array (
                                                'inlineStyles' =>
                                                    array (
                                                        'background-color' => '#ffffff',
                                                        'color' => '#545454',
                                                    ),
                                            ),
                                    ),
                                'class' => 'BuilderCanvasElement',
                            ),
                    ),
            );
            $this->makeBuilderPredefinedEmailTemplate($name, $unserializedData);
        }

        protected function makeThreeColumnsWithHero()
        {
            $name              = '3 Columns with Hero';
            $unserializedData  = array (
                'baseTemplateId' => '',
                'icon' => 'icon-template-1',
                'dom' =>
                    array (
                        'canvas1' =>
                            array (
                                'content' =>
                                    array (
                                        'builderheaderimagetextelement_1393965594_53163a1a0eb53' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965594_53163a1a0ef48' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1393965594_53163a1a0ee52' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x50'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1393965594_53163a1a145cc' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderheadertextelement_1393965594_53163a1a14515' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Acme Real Estate',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#ffffff',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'text-align' => 'right',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderHeaderTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '1:2',
                                                                'header' => '1',
                                                                'border-negation' =>
                                                                    array (
                                                                        'border-top' => 'none',
                                                                        'border-right' => 'none',
                                                                        'border-bottom' => 'none',
                                                                        'border-left' => 'none',
                                                                    ),
                                                            ),
                                                        'frontend' =>
                                                            array (
                                                                'inlineStyles' =>
                                                                    array (
                                                                        'background-color' => '#282a76',
                                                                    ),
                                                            ),
                                                    ),
                                                'class' => 'BuilderHeaderImageTextElement',
                                            ),
                                        'builderrowelement_1394062546_5317b4d264a62' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062546_5317b4d26488b' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildertitleelement_1394062546_5317b4d263942' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'New on our Downtown NYC locations',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h1',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '28',
                                                                                                        'font-weight' => 'bold',
                                                                                                        'line-height' => '100',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394122137_53189d999cade' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394122137_53189d999c769' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394122137_53189d999b21b' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['googleMaps'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1393965668_53163a6448794' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1393965668_53163a644866d' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394063801_5317b9b9eedc5' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x200'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'buildertitleelement_1394063416_5317b838c6ce1' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Property at NYC',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h2',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '18',
                                                                                                        'font-family' => 'Georgia',
                                                                                                        'font-weight' => 'bold',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderplaintextelement_1394063772_5317b99cab31e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'With its welcoming fireplace, wood-paneled ceiling, limestone floor, and luminous
view into a stunning courtyard, The Sterling Mason lobby imparts the intimate warmth of home.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'border-negation' =>
                                                                                                    array (
                                                                                                        'border-top' => 'none',
                                                                                                        'border-right' => 'none',
                                                                                                        'border-bottom' => 'none',
                                                                                                        'border-left' => 'none',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderPlainTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1394061698_5317b182c1f19' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394063806_5317b9be406a3' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x200'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'buildertitleelement_1394063420_5317b83cb81a3' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Chalet in Bs. As.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '18',
                                                                                                        'font-family' => 'Georgia',
                                                                                                        'font-weight' => 'bold',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderplaintextelement_1394063737_5317b979ce2a3' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'With its welcoming fireplace, wood-paneled ceiling, limestone floor, and luminous
view into a stunning courtyard, The Sterling Mason lobby imparts the intimate warmth of home.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'border-negation' =>
                                                                                                    array (
                                                                                                        'border-top' => 'none',
                                                                                                        'border-right' => 'none',
                                                                                                        'border-bottom' => 'none',
                                                                                                        'border-left' => 'none',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderPlainTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                        'buildercolumnelement_1394063404_5317b82c72b5c' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'builderimageelement_1394063809_5317b9c1da156' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'image' => $this->importedImages['200x200'],
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                    ),
                                                                                'class' => 'BuilderImageElement',
                                                                            ),
                                                                        'buildertitleelement_1394063425_5317b8410f24b' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'Luminus Loft',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'headingLevel' => 'h3',
                                                                                            ),
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'color' => '#323232',
                                                                                                        'font-size' => '18',
                                                                                                        'font-family' => 'Georgia',
                                                                                                        'font-weight' => 'bold',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderTitleElement',
                                                                            ),
                                                                        'builderplaintextelement_1394063741_5317b97d68d8d' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => 'With its welcoming fireplace, wood-paneled ceiling, limestone floor, and luminous
view into a stunning courtyard, The Sterling Mason lobby imparts the intimate warmth of home.',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'border-negation' =>
                                                                                                    array (
                                                                                                        'border-top' => 'none',
                                                                                                        'border-right' => 'none',
                                                                                                        'border-bottom' => 'none',
                                                                                                        'border-left' => 'none',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderPlainTextElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                        'backend' =>
                                                            array (
                                                                'configuration' => '3',
                                                            ),
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                        'builderrowelement_1394062641_5317b53112a36' =>
                                            array (
                                                'content' =>
                                                    array (
                                                        'buildercolumnelement_1394062641_5317b5311291a' =>
                                                            array (
                                                                'content' =>
                                                                    array (
                                                                        'buildersocialelement_1394121396_53189ab49a77c' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'backend' =>
                                                                                            array (
                                                                                                'layout' => 'horizontal',
                                                                                                'services' =>
                                                                                                    array (
                                                                                                        'Facebook' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.facebook.com/',
                                                                                                            ),
                                                                                                        'GooglePlus' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://gplus.con',
                                                                                                            ),
                                                                                                        'Instagram' =>
                                                                                                            array (
                                                                                                                'enabled' => '1',
                                                                                                                'url' => 'http://www.instagram.com/',
                                                                                                            ),
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderSocialElement',
                                                                            ),
                                                                        'builderfooterelement_1394062641_5317b5311226e' =>
                                                                            array (
                                                                                'content' =>
                                                                                    array (
                                                                                        'text' => '[[GLOBAL^MARKETING^FOOTER^HTML]]',
                                                                                    ),
                                                                                'properties' =>
                                                                                    array (
                                                                                        'frontend' =>
                                                                                            array (
                                                                                                'inlineStyles' =>
                                                                                                    array (
                                                                                                        'font-size' => '11',
                                                                                                        'background-color' => '#ebebeb',
                                                                                                    ),
                                                                                            ),
                                                                                    ),
                                                                                'class' => 'BuilderFooterElement',
                                                                            ),
                                                                    ),
                                                                'properties' =>
                                                                    array (
                                                                    ),
                                                                'class' => 'BuilderColumnElement',
                                                            ),
                                                    ),
                                                'properties' =>
                                                    array (
                                                    ),
                                                'class' => 'BuilderRowElement',
                                            ),
                                    ),
                                'properties' =>
                                    array (
                                        'frontend' =>
                                            array (
                                                'inlineStyles' =>
                                                    array (
                                                        'background-color' => '#fefefe',
                                                        'color' => '#545454',
                                                        'border-color' => '#284b7d',
                                                        'border-width' => '10',
                                                        'border-style' => 'solid',
                                                    ),
                                            ),
                                        'backend' =>
                                            array (
                                                'border-negation' =>
                                                    array (
                                                        'border-top' => 'none',
                                                        'border-right' => 'none',
                                                        'border-bottom' => 'none',
                                                        'border-left' => 'none',
                                                    ),
                                            ),
                                    ),
                                'class' => 'BuilderCanvasElement',
                            ),
                    ),
            );
            $this->makeBuilderPredefinedEmailTemplate($name, $unserializedData);
        }
    }
?>