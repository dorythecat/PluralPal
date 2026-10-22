<html lang="en">
<body>
<?php
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$username = "";
$error = "";
switch ($method) {
    case 'GET':
        break;
    case 'POST':
        if (isset($_POST['username'])) {
            $username = trim($_POST["username"]);

            if (strlen($username) < 3) {
                $error = "Your username cannot be less than 3 characters.";
                break;
            }

            if (strlen($username) > 64) {
                $error = "Your username cannot be longer than 64 characters.";
                break;
            };
        } else {
            $error = "Username is required";
            break;
        }

        if (isset($_POST['email'])) {
            $email = trim($_POST["email"]);

            if (strlen($email) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email.";
                break;
            }

            // Normalize email
            $email_split = explode("@", $email);
            $local = explode("+", $email_split[0])[0];
            if ($email_split[1] === "gmail.com" || $email_split[1] === "googlemail.com") {
                $local = str_replace(".", "", $local);
            }
            $email = $local . "@" . $email_split[1];
        } else {
            $error = "Email is required";
            break;
        }

        if (isset($_POST['password'])) {
            $password = trim($_POST["password"]);

            if (strlen($password) < 8) {
                $error = "Your password must be longer than 8 characters.";
                break;
            }

            if (strlen($password) > 64) {
                $error = "Your password cannot be longer than 64 characters.";
                break;
            }

            // Check that the password is somewhat safe
            if ($password === $username ||
                strpbrk($password, "@_-+^*:;/\\%$#|?¿¡!()[]{}^\"çºª·=`.,¬½~€ŋđðßæſ¶ŧ←↓→øþłĸħ«»¢„“”µ•<>ñÑΩ§¢®Ŧ¥↑ıØÞÆẞÐªŊĦ&Ł©‚‘’£$⅛⅜⅝™±") === false ||
                strpbrk($password, "0123456789") === false ||
                strpbrk($password, "abcdefghijklmnoprsqtuvwxyz") === false ||
                strpbrk($password, "ABCDEFGHIJKLMNOPQRSTUVWXYZ") === false) {
                $error = "Your password is not strong enough!";
                break;
            }

            // Check if this password appears on https://haveibeenpwned.com's database
            $password_sha1 = sha1($password);
            $url = "https://api.pwnedpasswords.com/range/" . substr($password_sha1, 0, 5);
            $context = stream_context_create([
                "http" => [
                    "method" => "GET",
                    "header" => "Add-Padding: true\r\n"
                ]
            ]);
            $res = file_get_contents($url, false, $context);
            $pos = stripos($res, substr($password_sha1, 5));
            if ($pos !== false) {
                $times_pwned = substr($res, $pos + 36);
                $times_pwned = substr($times_pwned, 0, strpos($times_pwned, "\n"));
                $error = "That password is unsafe, as it has been found in $times_pwned data breaches. (Check out https://haveibeenpwned.com/Passwords for more information)";
                break;
            }
        } else {
            $error = "Password is required";
            break;
        }

        if (isset($_POST['confirm_password'])) {
            if (trim($_POST["confirm_password"]) !== $password) {
                $error = "Password confirmation does not match";
                break;
            }
        } else {
            $error = "Password confirmation is required";
            break;
        }

        $res = $db_conn->query("SELECT * FROM users WHERE username = '$username'");
        if ($res->num_rows > 0) {
            $error = "This username is already in use.";
            if (openssl_password_verify('argon2id', $password, $res->fetch_assoc()["password_hash"])) { echo "YAY"; } else { echo "NAY"; }
            break;
        }

        $res = $db_conn->query("SELECT * FROM users WHERE email = '$email'");
        if ($res->num_rows > 0) {
            $error = "This email is already in use.";
            break;
        }

        $password_hash = openssl_password_hash('argon2id', $password);

        $db_conn->query("INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$password_hash')");

        echo "Registered successfully!";

        break;
    default:
        die("Unsupported HTTP method!");
}
?>

<h1>Register</h1>
<form method="POST" accept-charset="UTF-8">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username"><br>
    <label for="email">E-Mail:</label>
    <input type="email" id="email" name="email"><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password"><br>
    <label for="confirm_password">Confirm Password:</label>
    <input type="password" id="confirm_password" name="confirm_password"><br>
    <input type="submit" value="Submit">
</form>

<p><?php echo $error; ?></p>

</body>
</html>
