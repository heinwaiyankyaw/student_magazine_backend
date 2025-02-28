<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Platform</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            background-color: rgb(79, 114, 240);
            color: white;
            padding: 15px 0;
            border-radius: 10px 10px 0 0;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .content p strong {
            color: #333;
        }
        .note {
            font-size: 14px;
            color: #d9534f;
            margin-top: 15px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
        .footer h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 20px;
            background-color:rgb(79, 114, 240);
            color: white;
            text-align: center;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to AnnualUniversity Magazine</h2>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>This is your login email: <strong>{{ $email }}</strong></p>
            <p>This is your login password: <strong>{{ $password }}</strong></p>

            <p class="note">Note: After logging in with the above email and password, please make sure to change your password to something more secure.</p>

            <a href="http://localhost:5174/auth/login" class="button">Login Account</a>
        </div>

        <div class="footer">
            <h3>Thank you, AnnualUniversity Magazine</h3>
            <p>© 2025 AnnualUniversity Magazine. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
