<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Article Submitted</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            background: #4f72f0;
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .content {
            margin-top: 20px;
        }
        .content p {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }
        .highlight {
            font-weight: bold;
            color: #4f72f0;
        }
        .button {
            margin-top: 30px;
            display: inline-block;
            background: #4f72f0;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            margin-top: 40px;
            font-size: 14px;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Article Submission</h2>
        </div>
        <div class="content">
            <p>Dear <strong class="highlight">{{ $faculty_name }}</strong> Coordinator,</p>

            <p>
                A new article has been submitted by student <strong>{{ $student_name }}</strong>
                ({{ $student_email }}).
            </p>

            <p>Please review and process the article submission at your earliest convenience.</p>

            <a href="{{ url('/coordinator/articles') }}" class="button">Review Submission</a>
        </div>
        <div class="footer">
            <p>Thank you,<br>ContributeX University Magazine</p>
            <p>© 2025 ContributeX. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
