<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class EmailBounceUtilTest extends ZurmoBaseTest
    {
        protected static $textBody;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            // Begin Not Coding Standard
            static::$textBody    = <<<TXT
Delivered-To: shoaibi@bitesource.com
Received: by 10.49.61.161 with SMTP id q1csp63838qer;
Thu, 6 Jun 2013 10:16:54 -0700 (PDT)
X-Received: by 10.182.220.161 with SMTP id px1mr18728348obc.82.1370539014529;
Thu, 06 Jun 2013 10:16:54 -0700 (PDT)
Return-Path: <>
Received: from blu0-omc4-s25.blu0.hotmail.com (blu0-omc4-s25.blu0.hotmail.com. [65.55.111.164])
by mx.google.com with ESMTP id gp5si32927488obb.72.2013.06.06.10.16.54
for <shoaibi@bitesource.com>;
Thu, 06 Jun 2013 10:16:54 -0700 (PDT)
Received-SPF: pass (google.com: best guess record for domain of blu0-omc4-s25.blu0.hotmail.com designates 65.55.111.164 as permitted sender) client-ip=65.55.111.164;
Authentication-Results: mx.google.com;
spf=pass (google.com: best guess record for domain of blu0-omc4-s25.blu0.hotmail.com designates 65.55.111.164 as permitted sender) smtp.mail=
Received: from BAY0-MC1-F47.Bay0.hotmail.com ([65.55.111.137]) by blu0-omc4-s25.blu0.hotmail.com with Microsoft SMTPSVC(6.0.3790.4675);
Thu, 6 Jun 2013 10:16:54 -0700
From: postmaster@hotmail.com
To: shoaibi@bitesource.com
Date: Thu, 6 Jun 2013 10:16:51 -0700
MIME-Version: 1.0
Content-Type: multipart/report; report-type=delivery-status;
boundary="9B095B5ADSN=_01CE585A42E11DFD00CA0E30BAY0?MC1?F47.Bay"
X-DSNContext: 335a7efd - 4480 - 00000001 - 80040546
Message-ID: <sP2Bk87w40021a8ee@BAY0-MC1-F47.Bay0.hotmail.com>
Subject: Delivery Status Notification (Failure)
Return-Path: <>
X-OriginalArrivalTime: 06 Jun 2013 17:16:54.0590 (UTC) FILETIME=[A4660DE0:01CE62D9]

This is a MIME-formatted message.
Portions of this message may be unreadable without a MIME-capable mail program.

--9B095B5ADSN=_01CE585A42E11DFD00CA0E30BAY0?MC1?F47.Bay
Content-Type: text/plain; charset=unicode-1-1-utf-7

This is an automatically generated Delivery Status Notification.

Delivery to the following recipients failed.
noreply@live.com

--9B095B5ADSN=_01CE585A42E11DFD00CA0E30BAY0?MC1?F47.Bay
Content-Type: message/delivery-status

Reporting-MTA: dns;BAY0-MC1-F47.Bay0.hotmail.com
Received-From-MTA: dns;mail-qa0-f52.google.com
Arrival-Date: Thu, 6 Jun 2013 10:16:51 -0700

Final-Recipient: rfc822;noreply@live.com
Action: failed
Status: 5.5.0
Diagnostic-Code: smtp;550 Requested action not taken: mailbox unavailable (1998570073:3844:-2147467259)

--9B095B5ADSN=_01CE585A42E11DFD00CA0E30BAY0?MC1?F47.Bay
Content-Type: message/rfc822

Received: from mail-qa0-f52.google.com ([209.85.216.52]) by BAY0-MC1-F47.Bay0.hotmail.com with Microsoft SMTPSVC(6.0.3790.4900);
Thu, 6 Jun 2013 10:16:51 -0700
Received: by mail-qa0-f52.google.com with SMTP id bv4so464319qab.11
for <noreply@live.com>; Thu, 06 Jun 2013 10:16:51 -0700 (PDT)
X-Google-DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed;
d=google.com; s=20120113;
h=mime-version:sender:x-originating-ip:from:date:x-google-sender-auth
:message-id:subject:to:content-type:x-gm-message-state;
bh=2m+eb+RDLDCSBrp+GNlyBgkKXcRcSbXOq+td+E4B/vU=;
b=e7tOBRR+XxPUNKw/d5sfu1gJcGjnPI1gjNKK/08DKvZQmJXbvbY7rgQ4A5Z+lkFMNp
bxhQlINGUWkZW2+LKKI3W2EX8nXhgBykospUx6W/846jaM1xgKZ57jhmCsklDuARqL1/
DrtydznBtoBNHthwUjqfG+sohPtTyERiSLD8B1sWB3qqXrAh6VXupsbhoPbrjC3D57EB
Ga1gwqduHXx/KxrDOiy3Xts3N8dN8lYLD0CgEGJWV/qHvfV1A9z+gDyV3QfNn0Ncdbgl
2Y7vXIlTsHkNkHV9Uy75LqtQ3ZTjtgaB7nVo2NRMW9DdVYp9bVoQ2QJCvpwmPdanJqct
M8iQ==
X-Received: by 10.229.17.10 with SMTP id q10mr14191772qca.21.1370539011681;
Thu, 06 Jun 2013 10:16:51 -0700 (PDT)
MIME-Version: 1.0
Sender: shoaibi@bitesource.com
Received: by 10.49.61.161 with HTTP; Thu, 6 Jun 2013 10:16:36 -0700 (PDT)
X-Originating-IP: [182.188.197.11]
From: Shoaibi <shoaibi@dotgeek.me>
Date: Thu, 6 Jun 2013 22:16:36 +0500
X-Google-Sender-Auth: KOGyLSQUS92LYi12duzB4F5GulU
Message-ID: <CAPHoXex8qJCtO0gftfc5A5Fc71SnNN6P1C4PMNTk-iveT-p_Xw@mail.gmail.com>
Subject: bounce live
To: noreply@live.com
Content-Type: multipart/alternative; boundary=0015175cf92af15c9c04de7f7a48
X-Gm-Message-State: ALoCoQli+Q4i33Aqqp0RpAsEI8VLEVW3dnQQSMlMNMnIOq5PPSDv/+h73IEf/OU4Slm4LO4T9Z6F
Return-Path: shoaibi@bitesource.com
X-OriginalArrivalTime: 06 Jun 2013 17:16:51.0751 (UTC) FILETIME=[A2B4DB70:01CE62D9]
zurmoPersonId: 20
zurmoItemClass: AutoresponderItem
zurmoItemId: 10
X-AntiAbuse: This header was added to track abuse, please include it with any abuse report
X-AntiAbuse: Primary Hostname - host4.zurmo.com
X-AntiAbuse: Original Domain - bitesource.com
X-AntiAbuse: Originator/Caller UID/GID - [47 12] / [47 12]
X-AntiAbuse: Sender Address Domain - testmail.zurmo.com
X-Get-Message-Sender-Via: host4.zurmo.com: authenticated_id: dropbox_shoaibi@testmail.zurmo.com
X-Source:
X-Source-Args:
X-Source-Dir:

--0015175cf92af15c9c04de7f7a48
Content-Type: text/plain; charset=UTF-8

sfasdasdsadsadas

--0015175cf92af15c9c04de7f7a48
Content-Type: text/html; charset=UTF-8

<div dir="ltr">sfasdasdsadsadas</div>
--0015175cf92af15c9c04de7f7a48--
--9B095B5ADSN=_01CE585A42E11DFD00CA0E30BAY0?MC1?F47.Bay--
TXT;
            // End Not Coding Standard
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveCustomHeadersFromTextBodyReturnsFalseForInexistentTag()
        {
            $headerTags = array('X-Source-Dir', 'SomeMissingTag');
            $headers    = EmailBounceUtil::resolveCustomHeadersFromTextBody($headerTags, static::$textBody);
            $this->assertFalse($headers);
        }

        public function testResolveCustomHeadersFromTextBody()
        {
            $headerTags = array('From', 'zurmoItemId', 'zurmoPersonId', 'zurmoItemClass', 'X-Google-Sender-Auth');
            $headers    = EmailBounceUtil::resolveCustomHeadersFromTextBody($headerTags, static::$textBody);
            $this->assertNotEmpty($headers);
            $this->assertEquals('postmaster', $headers['From']);
            $this->assertEquals(10, $headers['zurmoItemId']);
            $this->assertEquals(20, $headers['zurmoPersonId']);
            $this->assertEquals('AutoresponderItem', $headers['zurmoItemClass']);
            $this->assertEquals('KOGyLSQUS92LYi12duzB4F5GulU', $headers['X-Google-Sender-Auth']);
        }
    }
?>