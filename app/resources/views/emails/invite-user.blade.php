<head>
    <style type="text/css">
        .MsgHeaderTable .Object {
            cursor: pointer;
            color: #369;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .MsgHeaderTable .Object-hover {
            cursor: pointer;
            color: #369;
            text-decoration: underline;
            white-space: nowrap;
        }

        .MsgBody {
            background-color: #fdfdfd;
            -moz-user-select: element;
            -ms-user-select: element;
        }

        .MsgBody-text {
            color: #333;
            font-family: monospace;
            word-wrap: break-word;
        }

        .MsgBody-text,
        .MsgBody-html {
            padding: 10px;
        }

        div.MsgBody,
        div.MsgBody * {
            font-size: 1.18rem;
        }

        body.MsgBody {
            font-size: 1.18rem;
        }

        .MsgBody .SignatureText {
            color: gray;
        }

        .MsgBody .QuotedText0 {
            color: purple;
        }

        .MsgBody .QuotedText1 {
            color: green;
        }

        .MsgBody .QuotedText2 {
            color: red;
        }

        .user_font_modern {
            font-family: "Roboto", "Helvetica Neue", Helvetica, Arial, "Liberation Sans", sans-serif;
        }

        .user_font_classic {
            font-family: Tahoma, Arial, sans-serif;
        }

        .user_font_wide {
            font-family: Verdana, sans-serif;
        }

        .user_font_system {
            font-family: "Roboto", "Segoe UI", "Lucida Sans", sans-serif;
        }

        .user_font_size_small {
            font-size: 11px;
        }

        .user_font_size_normal {
            font-size: 12px;
        }

        .user_font_size_large {
            font-size: 14px;
        }

        .user_font_size_larger {
            font-size: 16px;
        }

        .MsgBody .Object {
            color: #369;
            text-decoration: none;
            cursor: pointer;
        }

        .MsgBody .Object-hover {
            color: #369;
            text-decoration: underline;
        }

        .MsgBody .Object-active {
            color: darkgreen;
            text-decoration: underline;
        }

        .MsgBody .FakeAnchor,
        .MsgBody a:link,
        .MsgBody a:visited {
            color: #369;
            text-decoration: none;
            cursor: pointer;
        }

        .MsgBody a:hover {
            color: #369;
            text-decoration: underline;
        }

        .MsgBody a:active {
            color: darkgreen;
            text-decoration: underline;
        }

        .MsgBody .POObject {
            color: blue;
        }

        .MsgBody .POObjectApproved {
            color: green;
        }

        .MsgBody .POObjectRejected {
            color: red;
        }

        .MsgBody .zimbraHide {
            display: none;
        }

        .MsgBody-html pre,
        .MsgBody-html pre * {
            white-space: pre-wrap;
            word-wrap: break-word !important;
        }

        .MsgBody-html tt,
        .MsgBody-html tt * {
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word !important;
        }

        .MsgBody .ZmSearchResult {
            background-color: #FFFEC4;
        }
    </style>
</head>

<body
    style="margin: 0px; padding: 36px 0px; background: rgb(242, 242, 242); text-align: left; height: auto; width: 883px; position: absolute;"
    class="MsgBody MsgBody-html">
    <div>
        <div>
            <title></title>

            <div
                style="margin: 12px auto; max-width: 800px; color: rgb(34, 34, 34); background: rgb(255, 255, 255); font: 16px / 1.6 &quot;open sans&quot;, &quot;arial&quot;, &quot;helvetica&quot;, sans-serif;">

                <p style="margin: 0px; padding: 18px 0px;"><img style="width: 100%;"
                        alt="RouteYou - Ontdek &amp; plan: de mooiste routes"
                        dfsrc="https://media.routeyou.com/email/header/nl-2x.png"
                        src="https://media.routeyou.com/email/header/nl-2x.png" saveddisplaymode=""></p>

                <div
                    style="margin: 0px 5%; padding: 36px 0px 0px; text-align: left; border-top: 2px solid rgb(242, 242, 242);">
                    <p style="margin: 0px 0px 48px;">
                        @if ($user)
                            Hallo {{ $user->first_name }},
                        @else
                            Hallo,
                        @endif
                    </p>

                    <p>Onderstaand evenement lijkt ons iets voor jou.</p>

                    <h1
                        style="margin: 0px 0px 16px; font: 24px / 1.4 &quot;ubuntu&quot;, &quot;arial&quot;, &quot;helvetica&quot;, sans-serif;">
                        {{ $event->title }}</h1>

                    <p style="margin: 0px 0px 12px; text-align: center;"><img style="max-width: 100%;" alt=""
                            dfsrc="{{ $event->image }}" src="{{ $event->image }}" saveddisplaymode=""></p>
                    <p
                        style="margin: 0px 0px 12px; color: rgb(100, 76, 42); font: 13px / 1.4 &quot;open sans&quot;, &quot;arial&quot;, &quot;helvetica&quot;, sans-serif; text-align: right;">
                        Bron: <span class="Object" role="link" id="OBJ_PREFIX_DWT283_com_zimbra_url"><span
                                class="Object" role="link" id="OBJ_PREFIX_DWT291_com_zimbra_url"><a
                                    style="color: rgb(66, 205, 167);"
                                    href="{{ env('FRONTEND_BASE_URL') }}/{{ $event->slug }}"
                                    target="_blank"
                                    rel="nofollow noopener noreferrer">{{ $event->title }}</a></span></span></p>


                    <p>{{ $event->description }}</p>

                    <p><span class="Object" role="link" id="OBJ_PREFIX_DWT285_com_zimbra_url"><span class="Object"
                                role="link" id="OBJ_PREFIX_DWT293_com_zimbra_url"><a
                                    style="text-align: center; min-width: 64px; display: inline-block; padding: 16px 36px; margin: 0px; border: 1px solid rgb(66, 205, 167); border-radius: 100px; background: rgb(66, 205, 167); color: rgb(255, 255, 255); line-height: normal; text-decoration: none; font-weight: 500;"
                                    href="{{ env('FRONTEND_BASE_URL') }}/{{ $event->slug }}"
                                    target="_blank" rel="nofollow noopener noreferrer">Nu inschrijven</a></span></span>
                    </p>

                    <p
                        style="margin: 32px 0px 48px; padding: 32px 5%; background: rgb(242, 242, 242); font: 16px / 1.6 &quot;open sans&quot;, &quot;arial&quot;, &quot;helvetica&quot;, sans-serif;">
                        Adres: {{ $event->street }} {{ $event->house_number }}, {{ $event->zip }}
                        {{ $event->city }}
                        <br>
                        Datum: {{ \Carbon\Carbon::parse($event->start)->isoFormat('DD/MM/YYYY - HH:mm') }}
                        <br>
                        Prijs: {{ $event->price }} euro
                    </p>

                    <p style="text-align: right;"><span class="Object" role="link"
                            id="OBJ_PREFIX_DWT286_com_zimbra_url"><span class="Object" role="link"
                                id="OBJ_PREFIX_DWT294_com_zimbra_url"><a style="color: rgb(66, 205, 167);"
                                    href="https://www.routeyou.com/news/search?utm_source=Notification&amp;utm_medium=E-mail&amp;utm_campaign=default_2023-12-01"
                                    target="_blank" rel="nofollow noopener noreferrer">Bekijk alle
                                    nieuwsberichten</a></span></span></p>
                    <p
                        style="margin: 48px 0px 18px; padding: 24px 0px 18px; text-align: center; border-top: 2px solid rgb(242, 242, 242); border-bottom: 2px solid rgb(242, 242, 242); font: 14px / 1.4 &quot;open sans&quot;, &quot;arial&quot;, &quot;helvetica&quot;, sans-serif;">
                        <span style="display: inline-block; margin: 0px 0px 8px; white-space: nowrap;"><img
                                alt="" style="margin: 0px 8px 0px 0px; vertical-align: middle;" width="26"
                                height="26" dfsrc="https://media.routeyou.com/email/usps/routes.png"
                                src="https://media.routeyou.com/email/usps/routes.png" saveddisplaymode="">7.500.000
                            routes</span><span
                            style="display: inline-block; margin: 0px 60px 8px; white-space: nowrap;"><img
                                alt="" style="margin: 0px 8px 0px 0px; vertical-align: middle;" width="26"
                                height="26" dfsrc="https://media.routeyou.com/email/usps/users.png"
                                src="https://media.routeyou.com/email/usps/users.png" saveddisplaymode="">15.000.000
                            gebruikers</span><span
                            style="display: inline-block; margin: 0px 0px 8px; white-space: nowrap;"><img alt=""
                                style="margin: 0px 8px 0px 0px; vertical-align: middle;" width="26" height="26"
                                dfsrc="https://media.routeyou.com/email/usps/pois.png"
                                src="https://media.routeyou.com/email/usps/pois.png" saveddisplaymode="">3.500.000
                            bezienswaardigheden</span>
                    </p>

                    <p><img style="display: block; margin: 36px auto;" alt="RouteYou" width="144" height="55"
                            dfsrc="https://media.routeyou.com/email/logo.gif"
                            src="https://media.routeyou.com/email/logo.gif" saveddisplaymode="block"></p>

                    <p style="font-size: 12px; margin: 12px auto; max-width: 500px; text-align: center;">Wens je dit
                        soort e-mails niet langer te ontvangen dan kan je je <span class="Object" role="link"
                            id="OBJ_PREFIX_DWT287_com_zimbra_url"><span class="Object" role="link"
                                id="OBJ_PREFIX_DWT295_com_zimbra_url"><a style="color: rgb(66, 205, 167);"
                                    href="https://www.routeyou.com/user/unsubscribe?utm_source=Notification&amp;utm_medium=E-mail&amp;utm_campaign=default_2023-12-01&amp;c=qp0RitDF8nRM8WvUShIvrtk3WznrujS3nnBtBdwM3a0rGypVpL9HCCQSY8tWV-pYmS0F-F8Qh_CP8p3SVo1Ol_FeOev9HKOFt20RbKrrzxOLfWXBY-Noz_KCcx7b8q_BnA1W_83jPj4x1ImytXseT1Gk7CwPE7RibsffQCirJbshSmCr8VCTUxPsarwlhbOHmZihCHaob08il9qZ-gB0gQ"
                                    target="_blank" rel="nofollow noopener noreferrer">hier
                                    uitschrijven</a></span></span></p>

                    <p style="font-size: 12px; margin: 12px auto; max-width: 512px; text-align: center;">© 2024
                        RouteYou</p>

                    <p style="text-align: center;"><span class="Object" role="link"
                            id="OBJ_PREFIX_DWT288_com_zimbra_url"><span class="Object" role="link"
                                id="OBJ_PREFIX_DWT296_com_zimbra_url"><a
                                    style="display: inline-block; margin: 0px 3px;"
                                    href="http://www.facebook.com/pages/RouteYou/52807892247" target="_blank"
                                    rel="nofollow noopener noreferrer"><img alt=""
                                        dfsrc="https://media.routeyou.com/email/social/facebook.png"
                                        src="https://media.routeyou.com/email/social/facebook.png" saveddisplaymode=""
                                        style=""></a></span></span><span class="Object" role="link"
                            id="OBJ_PREFIX_DWT289_com_zimbra_url"><span class="Object" role="link"
                                id="OBJ_PREFIX_DWT297_com_zimbra_url"><a
                                    style="display: inline-block; margin: 0px 3px;"
                                    href="https://youtube.com/@routeyou" target="_blank"
                                    rel="nofollow noopener noreferrer"><img alt=""
                                        dfsrc="https://media.routeyou.com/email/social/youtube.png"
                                        src="https://media.routeyou.com/email/social/youtube.png" saveddisplaymode=""
                                        style=""></a></span></span><span class="Object" role="link"
                            id="OBJ_PREFIX_DWT290_com_zimbra_url"><span class="Object" role="link"
                                id="OBJ_PREFIX_DWT298_com_zimbra_url"><a
                                    style="display: inline-block; margin: 0px 3px;"
                                    href="https://www.instagram.com/routeyou_at_insta/" target="_blank"
                                    rel="nofollow noopener noreferrer"><img alt=""
                                        dfsrc="https://media.routeyou.com/email/social/instagram.png"
                                        src="https://media.routeyou.com/email/social/instagram.png"
                                        saveddisplaymode="" style=""></a></span></span></p>

                </div>

            </div>



        </div>
    </div>
</body>
