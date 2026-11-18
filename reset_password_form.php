<?php include 'header.php'; ?>

<div style="background-color: #222; padding: 10px; text-align: center; color: gold;">
    <h1>Reset Password</h1>
</div>

<div style="max-width: 400px; margin: 20px auto; padding: 20px; background-color: #111; border-radius: 10px;">
    <form style="margin-top: 20px;" action="reset_password.php" method="post">
        <div style="margin-bottom: 30px;">
            <label for="email" style="display: block; margin-bottom: 5px; color: gold;"><i class="fas fa-envelope"></i> Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" style="width: 100%; padding: 15px; border: 1px solid gold; border-radius: 5px; background-color: black; color: gold;" required>
        </div>
        
        <div style="margin-bottom: 30px;">
            <label for="password" style="display: block; margin-bottom: 5px; color: gold;"><i class="fas fa-lock"></i> New Password:</label>
            <div style="position: relative;">
                <input type="password" id="password" name="password" placeholder="Enter your new password" style="width: 100%; padding: 15px; border: 1px solid gold; border-radius: 5px; background-color: black; color: gold;" required>
                <i class="fas fa-eye toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: gold;" onclick="togglePassword('password')"></i>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <label for="confirm-password" style="display: block; margin-bottom: 5px; color: gold;"><i class="fas fa-lock"></i> Confirm Password:</label>
            <div style="position: relative;">
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your new password" style="width: 100%; padding: 15px; border: 1px solid gold; border-radius: 5px; background-color: black; color: gold;" required>
                <i class="fas fa-eye toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: gold;" onclick="togglePassword('confirm-password')"></i>
            </div>
        </div>

        <button type="submit" style="padding: 10px; border: none; border-radius: 5px; cursor: pointer; background-color: gold; color: black;">Reset Password</button>
    </form>
</div>

<div style="background-color: #222; padding: 10px; text-align: center; color: gold; position: fixed; bottom: 0; width: 100%;">
    <p>© 2023 SBA Awards. All rights reserved.</p>
</div>


<script>
    function togglePassword(id) {
        var passwordInput = document.getElementById(id);
        var icon = passwordInput.nextElementSibling;

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
