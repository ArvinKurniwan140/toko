<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - The Plaza</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

    body {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(135deg, #b86dd3, #cc73cc);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #fff;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.1);
      padding: 40px;
      border-radius: 16px;
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .login-container h2 {
      margin-bottom: 24px;
      font-size: 28px;
      font-weight: 700;
    }

    .login-container input {
      width: 100%;
      padding: 14px;
      margin: 10px 0;
      border: none;
      border-radius: 8px;
      font-size: 16px;
    }

    .login-container button {
      width: 100%;
      padding: 14px;
      margin-top: 20px;
      border: none;
      border-radius: 8px;
      background-color: #2d2d2d;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .login-container button:hover {
      background-color: #444;
    }

    .login-container .signup {
      margin-top: 16px;
      font-size: 14px;
    }

    .login-container .signup a {
      color: #fff;
      font-weight: bold;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login to The Plaza</h2>
    <form action="/login" method="post">
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>
    <div class="signup">
      Don't have an account? <a href="/regis">Sign up</a>
    </div>
  </div>
</body>
</html>
