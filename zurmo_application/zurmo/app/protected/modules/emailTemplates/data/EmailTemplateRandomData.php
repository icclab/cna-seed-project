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
     * Note related array of random seed data parts.
     */
    // Begin Not Coding Standard
    return array(
        'modelClassName'        => array(
            'Contact',
            'Meeting',
            'Account',
            'Note',
            'Task',
            'Opportunity',
            'Contact',
        ),
        'name'                  => array(
            'Happy Birthday',
            'Discount',
            'Downtime Alert',
            'Sales decrease',
            'Missions alert',
            'Inbox Update',
            'Introducing a New Application',
        ),
        'subject'               => array(
            'Happy Birthday',
            'Special Offer, 10% discount',
            'Planned Downtime',
            'Q4 Sales decrease',
            'Upcoming Missions',
            'New Inbox Module is live',
            'Lets explore the app',
        ),
        'language'              => array(
            'en',
            'es',
            'it',
            'fr',
            'de',
            'en',
        ),
        'textContent'           => array(
            'Zurmo\'s source code is hosted on bitbucket while we use mercurial for version control.',
            'Our goal with Zurmo is to provide an easy-to-use, easy-to-customize CRM application that can be ' .
                'adapted to any business use case. We have taken special care to think through many different use' .
                ' cases and have designed a system that we believe provides a high degree of flexibility and a wide' .
                ' range of out-of-the-box use cases. Zurmo is capable of supporting your complex business processes, ' .
                'yet very simple to use.',
        ),
        'htmlContent'           => array(
            '<html><head></head><body><p>Zurmo\'s source code is hosted on bitbucket while we use ' .
                '<img src="http://www.selenic.com/hg-logo/droplets-50.png" alt="mercurial" /> for ' .
                'version control.</body></html>',
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html>
                    <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
                    <title>Exploring Zurmo</title>

                    <!-- Facebook sharing information tags -->
                    <meta property="og:title" content="Exploring Zurmo" />
                </head>
                <body style="width:100% !important; min-height:1000px; color:#333333; background:#e0dbcf; font-family:Arial,Helvetica,sans-serif; font-size:13px; line-height:1.4;"
                alink="#9d470a" link="#9d470a" bgcolor="#e0dbcf" text="#333333" yahoo="fix">
                    <style type="text/css">
                        /* EMBEDDED CSS
                           Android Mail does not support "class" declarations outside of a media query so use inline CSS as a rule.
                           More info: Http://www.emailonacid.com/blog/the_android_mail_app_and_css_class_declarations/ */

                        /****** EMAIL CLIENT BUG FIXES - BEST NOT TO CHANGE THESE ********/

                        /* Forces Hotmail to display emails at full width. */
                        .ExternalClass {width:100%;}

                        /* Forces Hotmail to display normal line spacing. */
                        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}

                        /* Prevents Webkit and Windows Mobile platforms from changing default font sizes. */
                        body {-webkit-text-size-adjust:none; -ms-text-size-adjust:none;}

                        /* Resets all body margins and padding to "0" for good measure. */
                        body {margin:0; padding:0;}

                        /* Resolves webkit padding issue. */
                        table {border-spacing:0;}

                        /* Resolves the Outlook 2007, 2010, and Gmail td padding issue. */
                        table td {border-collapse:collapse;}

                        /****** END BUG FIXES ********/

                        /****** RESETTING DEFAULTS, IT IS BEST TO OVERWRITE THESE STYLES INLINE ********/

                        /* This sets a clean slate for all clients EXCEPT Gmail.
                           From there it forces you to do all of your spacing inline during the development process.
                           Be sure to stick to margins because paragraph padding is not supported by Outlook 2007/2010.
                           Remember: Hotmail does not support "margin" nor the "margin-top" properties.
                           Stick to "margin-bottom", "margin-left", "margin-right" in order to control spacing.
                           It also wise to set the inline top-margin to "0" for consistancy in Gmail for every inline instance
                           of a paragraph tag. */
                        p {margin:0; padding:0; margin-bottom:0;}

                        /* This CSS will overwrite Hotmails default CSS and make your headings appear consistant with Gmail.
                           From there, you can override with inline CSS if needed. */
                        h1, h2, h3, h4, h5, h6 {color:#333333; line-height:100%;}

                        /****** END RESETTING DEFAULTS ********/

                        /****** EDITABLE STYLES - FOR YOUR TEMPLATE ********/

                        /* The "body" is defined here for Yahoo Beta because it does not support your body tag. Instead, it will
                           create a wrapper div around your email and that div will inherit your embedded body styles.
                           The "#body_style" is defined for AOL because it does not support your embedded body definition nor
                           your body tag, we will use this class in our wrapper div. */
                        body, #body_style {width:100% !important; min-height:1000px; color:#333333; background:#e0dbcf; font-family:Arial,Helvetica,sans-serif; font-size:13px; line-height:1.4;}

                        /* This is the embedded CSS link color for Gmail. This will overwrite Hotmail and Yahoo Beta is
                           embedded link colors and make it consistent with Gmail. Also use this rule on inline CSS. */
                        a         {color:#114eb1; text-decoration:none;}

                        /* There is no way to set these inline so you have the option of adding pseudo class definitions here.
                           They would not work for Gmail or older Lotus Notes but it is a nice addition for all other clients. */
                        a:link    {color:#114eb1; text-decoration:none;}
                        a:visited {color:#183082; text-decoration:none;}
                        a:focus   {color:#0066ff !important;}
                        a:hover   {color:#0066ff !important;}

                        /* A nice and clean way to target phone numbers you want clickable and avoid a mobile phone from
                           linking other numbers that look like, but are not phone numbers. Use these two blocks of code to
                           "unstyle" any numbers that may be linked. The second block gives you a class ".mobile_link" to apply
                           with a span tag to the numbers you would like linked and styled.
                           More info: http://www.campaignmonitor.com/blog/post/3571/using-phone-numbers-in-html-email/ */
                        a[href^="tel"], a[href^="sms"] {text-decoration:none; color:#333333; pointer-events:none; cursor:default;}
                        .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {text-decoration:default; color:#6e5c4f !important; pointer-events:auto; cursor:default;}

                        /****** MEDIA QUERIES ********/
                        /* You must use attribute selectors in your media queries to prevent Yahoo from rendering these styles.
                           We added a yahoo attribute in the body tag to complete this fix.
                           More info: http://www.emailonacid.com/blog/details/C13/stop_yahoo_mail_from_rendering_your_media_queries */

                        /* Target mobile devices. */
                        /* @media only screen and (max-device-width: 639px) { */
                        @media only screen and (max-width: 639px)
                        {
                            /* Hide elements at smaller screen sizes (!important needed to override inline CSS). */
                            body[yahoo] .hide {display:none !important;}

                            /* Adjust table widths at smaller screen sizes. */
                            body[yahoo] .table {width:320px !important;}
                            body[yahoo] .innertable {width:280px !important;}

                            /* Resize hero image at smaller screen sizes. */
                            body[yahoo] .heroimage {width:280px !important; height:100px !important;}

                            /* Resize page shadow at smaller screen sizes. */
                            body[yahoo] .shadow {width:280px !important; height:4px !important;}

                            /* Collapse footer columns. */
                            body[yahoo] .footer-left   {width:320px !important;}
                            body[yahoo] .footer-right {width:320px !important;}
                            body[yahoo] .footer-right img {float:left !important; margin:0 1em 0 0 !important;}
                        }

                        /* Target tablet devices. */
                        /* @media only screen and (min-device-width: 640px) and (max-device-width: 1024px) { */
                        @media only screen and (min-width: 640px) and (max-width: 1024px)
                        {
                        }

                        /*** END EDITABLE STYLES ***/

                        /****** TEMPORARY - THESE SHOULD BE MOVED INLINE AT END OF YOUR DEVELOPMENT PROCESS ********/

                        h1 {font-size:26px; line-height:1.2; font-weight:normal; margin-top:0; margin-bottom:0;}

                        p {margin-top:0; margin-bottom:0;}

                        img {display:block; border:none; outline:none; text-decoration:none;}

                        /* Remove spacing around Outlook 07, 10 tables */
                        table {border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;}

                        /*** END TEMPORARY ***/
                    </style>
                <!-- You may adjust each of the values above for your template as needed.

                We have included the style attribute for Gmail because it does not support embedded CSS and it will convert this body tag to
                a div. Since it gets converted to a div, the other body attributes like bgcolor are ignored.

                We included body attributes (alink, link, bgcolor and text) for Lotus Notes 6.5 and 7, as these clients do not offer much
                support for embedded nor inline CSS.

                The "min-height" attribute is set for Gmail and AOL since they will be converting this body tag to a div and we want our
                background color to reach the bottom of the page.

                The yahoo attribute is added if you are using media queries for mobile devices (see media queries above) -->

                <!-- PAGE WRAPPER -->
                    <div id="body_style">

                        <!-- Wrapper/Container Table: Use a wrapper table to control the width and the background color consistently of your email. Use this approach instead of setting attributes on the body tag. -->
                        <table cellpadding="0" cellspacing="0" border="0" align="center" style="width:100% !important; margin:0; padding:0;">
                            <tr bgcolor="#f0f0f0">
                                <td>

                                    <!-- Tables are the most common way to format your email consistently. Set your table widths inside cells and in most cases reset cellpadding, cellspacing, and border to zero. Use nested tables as a way to space effectively in your message. -->
                                    <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="table">

                                        <!-- HEADER -->
                                        <tr>
                                            <td>
                                                <!-- set a value for bgcolor -->
                                                <table bgcolor="#ffffff" text="#cccccc" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <!-- header left: logo and link to homepage -->
                                                        <td width="320" valign="top">
                                                            <!-- set an image for header left - must be 320px width (height can be variable) -->
                                                            <a href="#" target="_blank"><img src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/ZurmoLogo.png') . '" width="320" height="64" border="0" alt="Header (left)" /></a>
                                                        </td>
                                                        <!-- /header left -->
                                                        <!-- header right: hidden in mobile version -->
                                                        <td width="280" valign="top" class="hide">
                                                        </td>
                                                        <!-- /header right -->
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- /HEADER -->

                                        <!-- CONTENT -->
                                        <!-- set a value for bgcolor -->
                                        <tr bgcolor="#ffffff">
                                            <td style="padding-top:20px;">

                                                <!-- hero article -->
                                                <table style="margin-bottom:1em;" width="560" cellpadding="0" cellspacing="0" border="0" align="center" class="innertable">
                                                    <tr>
                                                        <!-- hero article textarea -->
                                                        <td>
                                                            <table bgcolor="#ffffff" width="100%" cellpadding="10" cellspacing="0" border="0">
                                                                <tr>
                                                                    <td>
                                                                        <!-- hero article heading text -->
                                                                        <h1 style="color:#666666; margin-bottom:.5em;">Hi [[TITLE]]. [[FIRST^NAME]],</h1>
                                                                        <!-- /hero article heading text -->
                                                                        <!-- hero article paragraph text -->
                                                                        <p style="margin-top:0; margin-bottom:0;">Our goal with Zurmo is to provide an easy-to-use, easy-to-customize CRM application that can be adapted to any business use case. We have taken special care to think through many different use cases and have designed a system that we believe provides a high degree of flexibility and a wide range of out-of-the-box use cases. Zurmo is capable of supporting your complex business processes, yet very simple to use.</p>
                                                                        <!-- /hero article paragraph text -->
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <!-- /hero article textarea -->
                                                    </tr>
                                                    <!-- hero article main image: must be 560px x 186px -->
                                                    <tr>
                                                        <td>
                                                            <img src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/grass.jpg') . '" width="560" height="280" border="0" alt="" class="heroimage" />
                                                        </td>
                                                    </tr>
                                                    <!-- /hero article main image -->
                                                </table>
                                                <!-- /hero article -->

                                                <!-- standard article (left text) -->
                                                <table style="margin-bottom: 1em;" width="560" cellpadding="0" cellspacing="0" border="0" align="center" class="innertable">
                                                    <tr valign="top">
                                                        <!-- standard article textarea -->
                                                        <td>
                                                            <table width="270" cellpadding="0" cellspacing="0" border="0">
                                                                <tr valign="top">
                                                                    <!-- standard article heading text -->
                                                                    <td>
                                                                        <h1 style="color:#666666;"><a style="color:#114eb1;" href="#" target="_blank">Sales and Marketing Friendly</a></h1>
                                                                    </td>
                                                                    <!-- /standard article heading text -->
                                                                </tr>
                                                            </table>
                                                            <!-- standard article paragraph text -->
                                                            <table width="100%" cellpadding="10" cellspacing="0" border="0">
                                                                <tr valign="top">
                                                                    <td>
                                                                        <p style="margin-top:0; margin-bottom:0;">Zurmo CRM empowers Sales and Marketing Professionals to work together throughout the entire sales and marketing processes. Users are provided with everything they need in order to find, nurture, qualify, and convert Leads into Contacts associated with Accounts. Meetings, Tasks, Notes, and Attachments - everything required to stay on track - are all conveniently located in one place. Opportunities are managed seamlessly from the negotiation stage to close/win.</p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <!-- /standard article paragraph text -->
                                                        </td>
                                                        <!-- /standard article textarea -->
                                                        <!-- standard article main image: hidden in mobile version - must be 280px width (height can be variable) -->
                                                        <td class="hide">
                                                            <img style="margin-bottom:1em;" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/demo1.jpg') . '" width="260" height="260" border="0" alt="" />
                                                        </td>
                                                        <!-- /standard article main image -->
                                                    </tr>
                                                </table>
                                                <!-- /standard article (left text) -->

                                                <!-- standard article (right text) -->
                                                <table width="560" cellpadding="0" cellspacing="0" border="0" align="center" class="innertable">
                                                    <tr valign="top">
                                                        <!-- standard article main image: hidden in mobile version - must be 280px width (height can be variable) -->
                                                        <td class="hide">
                                                            <img style="margin-bottom:1em;" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/demo2.jpg') . '" width="260" height="260" border="0" alt="" />
                                                        </td>
                                                        <!-- /standard article main image -->
                                                        <!-- standard article textarea -->
                                                        <td>
                                                            <table width="270" cellpadding="0" cellspacing="0" border="0">
                                                                <tr valign="top">
                                                                    <!-- standard article heading text -->
                                                                    <td>
                                                                        <h1 style="color:#666666;"><a style="color:#114eb1;" href="#" target="_blank">User Friendly</a></h1>
                                                                    </td>
                                                                    <!-- /standard article heading text -->
                                                                </tr>
                                                            </table>
                                                            <!-- standard article paragraph text -->
                                                            <table width="100%" cellpadding="10" cellspacing="0" border="0">
                                                                <tr valign="top">
                                                                    <td>
                                                                        <p style="margin-top:0; margin-bottom:0;">Every effort has been made to design Zurmo for the user. Zurmo is the first CRM solution with gamification at its core. Game concepts like points, leveling, badges, and leaderboards keep users engaged - and somewhat addicted to following CRM best practices.</p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <!-- /standard article paragraph text -->
                                                        </td>
                                                        <!-- /standard article textarea -->
                                                    </tr>
                                                </table>
                                                <!-- /standard article (right text) -->

                                            </td>
                                        </tr>
                                        <!-- /CONTENT -->

                                        <!-- FOOTER -->
                                        <tr>
                                            <td>
                                                <table bgcolor="#cccccc" text="#ffffff" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td>
                                                            <table align="left" bgcolor="#cccccc" text="#333333" width="280" cellpadding="10" cellspacing="0" border="0" class="footer-left">
                                                                <tr>
                                                                    <td style="color:#333333;">
                                                                             <p> '. GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag() . ' <br/> ' . GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag() . '</p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table align="right" bgcolor="#cccccc" text="#333333" width="280" cellpadding="10" cellspacing="0" border="0" class="footer-right">
                                                                <tr>
                                                                    <td style="color:#333333;">
                                                                        <p>
                                                                            <a href="#" target="_blank"><img style="margin-left:1em;" align="right" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/facebook.gif') . '" width="32" height="32" border="0" alt="Facebook" /></a>
                                                                            <a href="#" target="_blank"><img style="margin-left:1em;" align="right" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/googleplus.gif') . '" width="32" height="32" border="0" alt="Google+" /></a>
                                                                            <a href="#" target="_blank"><img style="margin-left:1em;" align="right" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/linkedin.gif') . '" width="32" height="32" border="0" alt="LinkedIn" /></a>
                                                                            <a href="#" target="_blank"><img style="margin-left:1em;" align="right" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/twitter.gif') . '" width="32" height="32" border="0" alt="Twitter" /></a>
                                                                            <a href="#" target="_blank"><img style="margin-left:1em;" align="right" src="' . PlaceholderImageUtil::resolveUrlForThemeFile('/default/images/demo/email-template-demo-one/youtube.gif') . '" width="32" height="32" border="0" alt="YouTube" /></a>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- /FOOTER -->

                                        <!-- SUBFOOTER -->
                                        <tr>
                                            <td>
                                                <table width="100%" cellpadding="10" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="top" style="font-size:11px;">
                                                            &copy;[[CURRENT^YEAR]] <a href="[[BASE^URL]]">[[APPLICATION^NAME]]</a>. All rights reserved. This email was sent to [[PRIMARY^EMAIL]].
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- /SUBFOOTER -->

                                    </table>
                                </td>
                            </tr>
                        </table>
                        <!-- End of wrapper table -->
                    </div>

                </body>
                </html>'
        ),
    );
    // End Not Coding Standard
?>