<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - The Plaza</title>
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

    .register-container {
      background: rgba(255, 255, 255, 0.1);
      padding: 40px;
      border-radius: 16px;
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 450px;
      text-align: center;
    }

    .register-container h2 {
      margin-bottom: 24px;
      font-size: 28px;
      font-weight: 700;
    }

    .register-container input {
      width: 100%;
      padding: 14px;
      margin: 10px 0;
      border: none;
      border-radius: 8px;
      font-size: 16px;
    }

    .register-container button {
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

    .register-container button:hover {
      background-color: #444;
    }

    .register-container .login {
      margin-top: 16px;
      font-size: 14px;
    }

    .register-container .login a {
      color: #fff;
      font-weight: bold;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Create Your Account</h2>
    <form action="/register" method="post">
      <input type="text" name="fullname" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="password" name="confirm_password" placeholder="Confirm Password" required />
      <button type="submit">Register</button>
    </form>
    <div class="login">
      Already have an account? <a href="/login">Login</a>
    </div>
  </div>
</body>
</html>
