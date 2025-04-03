<html>

<head>
    <meta charset="utf-8">
    <title>{{config('constants.options.SITE_NAME')}}</title>

</head>
<style>
    body {
        background: #000;
    }

    #word {
        color: green;
        /*color: #8002f5;*/
        font-family: Monaco, monospace;
        font-size: 24px;
        width: 100%;
        text-align: center;
        position: absolute;
        top: 45%;
        left: 0;
        animation: 120ms infinite normal word;
    }

    span {
        animation: 1.5s infinite normal imleç;
    }

    ::-moz-selection {
        background: #7021d2;
        color: #fff;
    }

    ::selection {
        background: #7021d2;
        color: #fff;
    }

    @keyframes word {
        0% {
            opacity: 0;
            left: 0;
        }

        40%,
        80% {
            opacity: 1;
            left: -2px;
        }
    }

    @keyframes cursor {
        0% {
            opacity: 0;
            left: 0;
        }

        40% {
            opacity: 0;
            left: -2px;
        }

        80% {
            opacity: 1;
            left: -2px;
        }
    }
</style>

<body>

    <body oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
        <div id="word">█ █ █ <span style="color:black">█ █ █ █ █ █ █ █ █ █ </span>31%
            <br>&gt;
            Hello Visitor
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;<br>&gt;
            We're Coming Soon <span id="cursor">█</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
    </body>

</html>