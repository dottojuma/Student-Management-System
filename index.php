<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyumbani - Student Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .welcome-container {
            text-align: center;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        p { color: #7f8c8d; font-size: 16px; margin-bottom: 30px; }
        .btn-login {
            background-color: #3498db;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            font-size: 18px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .btn-login:hover { background-color: #2980b9; }
    </style>
</head>
<body>

<div class="welcome-container">
    <h1>Student Management System</h1>
    <p>Karibu kwenye mfumo mkuu wa usimamizi wa taarifa za wanafunzi. Tafadhali ingia ili kuendelea na usimamizi.</p>
    <a href="login.php" class="btn-login">Ingia Mfomoni (Login)</a>
</div>

</body>
</html>