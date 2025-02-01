<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        /* General reset and styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        table {
            border-spacing: 0; /* Replaces cellspacing="0" */
            border-collapse: collapse; /* Ensures no gaps between table cells */
            width: 100%;
        }
        td {
            padding: 0; /* Replaces cellpadding="0" */
        }
        img {
            border: 0;
            display: block;
            max-width: 100%; /* Makes images responsive */
            height: auto; /* Maintains aspect ratio */
        }
        .container {
            background-color: #ffffff;
        }
        .header {
            padding: 20px;
            text-align: center;
            background-color: #007BFF;
            color: #ffffff;
        }
        .body {
            padding: 20px;
        }
        .footer {
            padding: 10px;
            text-align: center;
            background-color: #f4f4f4;
            font-size: 12px;
            color: #666666;
        }
        /* Responsive design */
        @media screen and (max-width: 600px) {
            .container {
                width: 100%;
            }
            .header, .body, .footer {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<table role="presentation" style="border-spacing: 0; border-collapse: collapse; padding: 0; margin: 0; width: 100%;">
    <tr>
        <td style="padding: 20px 0; text-align: center">
            <table role="presentation"  class="container" style="text-align: left; width: 100%; max-width: 600px; margin: 0 auto;">
                <tr>
                    <td class="header">
                        @include('emails.layouts.header')
                    </td>
                </tr>
                <tr>
                    <td class="body">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td class="footer">
                        @include('emails.layouts.footer')
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
