<?php  
session_start();
error_reporting(0);
include("dbconnect.php");

// Redirect if user is already logged in
if (isset($_SESSION['User  '])) {
    echo '<script type="text/javascript">window.location.assign("schedule.php");</script>';
    exit();
}

// Function to generate a random OTP
function generateOTP($length = 6) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

// Handle form submission
if (isset($_POST['registerbtn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $phoneno = $_POST['phoneno'];

    // Check if the email already exists
    $query = "SELECT * FROM `users` WHERE `email` = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<script type='text/javascript'>alert('Email has been used! Please try another email!');</script>";
    } else {
        // Generate and store OTP
        $otp = generateOTP();
        $_SESSION['otp'] = $otp;

        // Store user data temporarily in session
        $_SESSION['temp_user'] = [
            'email' => $email,
            'name' => $name,
            'phoneno' => $phoneno,
            'password' => password_hash($password, PASSWORD_DEFAULT) // Hash the password
        ];

        // Email content
        $from = "sanjeevan2006@yahoo.com";
        $subject = "Verification Code from Registration";
        $htmlContent = "<p>Your verification One Time Password (OTP) is: <strong>$otp</strong></p>";
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";

        // Send the email
        if (mail($email, $subject, $htmlContent, $headers)) {
            echo "<script type='text/javascript'>alert('Registration successful! OTP sent to your email.');</script>";
        } else {
            echo "<script type='text/javascript'>alert('Failed to send OTP. Please try again.');</script>";
        }
    }
}

// Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];

    // Verify OTP
    if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']) {
        // Insert user data into the database
        $temp_user = $_SESSION['temp_user'];
        $query = "INSERT INTO `users`(`email`, `name`, `phoneno`, `password`) VALUES ('{$temp_user['email']}','{$temp_user['name']}','{$temp_user['phoneno']}','{$temp_user['password']}')";

        if (mysqli_query($conn, $query)) {
            echo "<script type='text/javascript'>alert('Registration successful!'); window.location.assign('login.php');</script>";
        } else {
            echo "<script type='text/javascript'>alert('Registration failed. Please try again.');</script>";
        }

        // Clear session data
        unset($_SESSION['otp']);
        unset($_SESSION['temp_user']);
    } else {
        echo "<script type='text/javascript'>alert('Invalid OTP. Please try again.');</script>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Registration</title>
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url("assets/bg1.jpg");
            background-size: cover;
            background-position: center;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto', sans-serif;
            padding: 20px;
            box-sizing: border-box;
        }

        #form_wrapper {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
            backdrop-filter: blur(15px);
            animation: fadeIn 1s ease-out;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }

        .input_field {
            width: 100%;
            height: 45px;
            border-radius: 25px;
            border: 1px solid #ccc;
            background-color: #f7f7f7;
            font-size: 16px;
            margin-bottom: 20px;
            padding: 0 20px;
            transition: 0.3s ease;
            box-sizing: border-box;
        }

        .input_field:focus {
            border-color: #0088ff;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0, 136, 255, 0.6);
            outline: none;
        }

        .loginbtn {
            width: 100%;
            height: 45px;
            border-radius: 25px;
            background-color: #0088ff;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .loginbtn:hover {
            background-color: #005fbb;
        }

        a {
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            display: inline-block;
            color: #0088ff;
        }

        a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <form method="post">
        <div id="form_wrapper">
            <h1>Registration</h1>
            
            <input placeholder="Email" type="email" name="email" id="email" class="input_field" required />
            <input placeholder="Username" type="text" name="name" id="name" class="input_field" required />
            <input placeholder="Phone No" type="text" name="phoneno" id="phoneno" class="input_field" 
                required pattern="^(070|071|072|074|075|076|077|078)\d{7}$" 
                title="Please enter a valid phone number" />
            <input placeholder="Password" type="password" name="password" id="password" class="input_field" required minlength="8" />
            
            <button class="loginbtn" type="submit" name="registerbtn">Register</button>
            
            <a href="login.php">Already have an account?</a>
        </div>
    </form>

    <form id="otpForm" method="post" style="display: none;">
        <div id="form_wrapper">
            <h1>OTP Verification</h1>
            <p>Email: <?php echo isset($_SESSION['temp_user']['email']) ? $_SESSION['temp_user']['email'] : ''; ?></p>
            <input type="text" id="otp" name="otp" placeholder="Enter your OTP" class="input_field" required>
            <button class="loginbtn" type="submit" name="verify_otp">Verify OTP</button>
            <div id="responseMessage" class="response-message"></div>
        </div>
    </form>

    <script>
        document.querySelector('form[method="post"]').onsubmit = function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('', {
                method: 'POST',
                body: formData
            }).then(response => response.text()).then (data => {
                if (data.includes('OTP sent to your email')) {
                    document.getElementById('otpForm').style.display = 'block';
                    document.querySelector('form[method="post"]').style.display = 'none';
                }
                alert(data);
            });
        };
    </script>
</body>
</html>