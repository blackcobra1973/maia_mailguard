<?php
    /*
     * $Id: rulestats.php,v 1.1.1.1.4.2 2004/09/07 17:34:55 jleaver Exp $
     *
     * MAIA MAILGUARD LICENSE v.1.0
     *
     * Copyright 2004 by Robert LeBlanc <rjl@renaissoft.com>
     *                   Motoharu Kubo  <mkubo@3ware.co.jp>
     *                   Oyaji          <oyaji@mail.aconus.com>
     * All rights reserved.
     *
     * Fri Jul 22 2016 YAMAMOTO Takashi <yamachan@piwikjapan.org>
     * - Checked for version 1.0.4
     * 
     * PREAMBLE
     *
     * This License is designed for users of Maia Mailguard
     * ("the Software") who wish to support the Maia Mailguard project by
     * leaving "Maia Mailguard" branding information in the HTML output
     * of the pages generated by the Software, and providing links back
     * to the Maia Mailguard home page.  Users who wish to remove this
     * branding information should contact the copyright owner to obtain
     * a Rebranding License.
     *
     * DEFINITION OF TERMS
     *
     * The "Software" refers to Maia Mailguard, including all of the
     * associated PHP, Perl, and SQL scripts, documentation files, graphic
     * icons and logo images.
     *
     * GRANT OF LICENSE
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions
     * are met:
     *
     * 1. Redistributions of source code must retain the above copyright
     *    notice, this list of conditions and the following disclaimer.
     *
     * 2. Redistributions in binary form must reproduce the above copyright
     *    notice, this list of conditions and the following disclaimer in the
     *    documentation and/or other materials provided with the distribution.
     *
     * 3. The end-user documentation included with the redistribution, if
     *    any, must include the following acknowledgment:
     *
     *    "This product includes software developed by Robert LeBlanc
     *    <rjl@renaissoft.com>."
     *
     *    Alternately, this acknowledgment may appear in the software itself,
     *    if and wherever such third-party acknowledgments normally appear.
     *
     * 4. At least one of the following branding conventions must be used:
     *
     *    a. The Maia Mailguard logo appears in the page-top banner of
     *       all HTML output pages in an unmodified form, and links
     *       directly to the Maia Mailguard home page; or
     *
     *    b. The "Powered by Maia Mailguard" graphic appears in the HTML
     *       output of all gateway pages that lead to this software,
     *       linking directly to the Maia Mailguard home page; or
     *
     *    c. A separate Rebranding License is obtained from the copyright
     *       owner, exempting the Licensee from 4(a) and 4(b), subject to
     *       the additional conditions laid out in that license document.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
     * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
     * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
     * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
     * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
     * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
     * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
     * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
     * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
     * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */

    // Page subtitle
    $lang['banner_subtitle'] =  "SpamAssassin ルール関連統計値";

    // Table headers
    $lang['header_rules1'] =  "SpamAssassin ルール";
    $lang['header_rules2'] =  "全ユーザ";
    $lang['header_rule_name'] =  "ルール";
    $lang['header_rule_description'] =  "説明";
    $lang['header_rule_score'] =  "スコア";
    $lang['header_rule_count'] =  "該当数";
    $lang['header_percent'] =  "パーセント";

    // Links
    $lang['link_system_stats'] =  "全ユーザを対象とした統計に戻る";

    // Text messages
    $lang['message_no_rules'] =  "SpamAssassin ルール関連統計値はまだ集計されていません";
    $lang['text_total'] =  "合計";
    $lang['text_rules'] =  "ルール";
?>