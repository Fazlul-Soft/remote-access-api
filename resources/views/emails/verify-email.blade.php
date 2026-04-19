<!DOCTYPE html>
<html>
<head>
    <style>
        .button {
            background-color: #673AB7;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h2>Welcome to {{ $projectName }}!</h2>
    <p>Hi {{ $user->displayName ?? 'User' }},</p>
    <p>Please click the button below to verify your email address and activate your account.</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" class="button">Verify Email Address</a>
    </div>

    <p>If the button doesn't work, copy and paste this link into your browser:</p>
    <p>{{ $url }}</p>

    <p>Thank you,<br>The {{ $projectName }} Team</p>
</body>
</html>
