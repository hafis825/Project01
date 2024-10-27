<?php
session_start();
include('../config.php');

function loginUser($username, $password, $remember, $pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($remember) {
            setcookie('username', $username, time() + (30 * 24 * 60 * 60), "/");
            setcookie('password', $password, time() + (30 * 24 * 60 * 60), "/");
        }
        
        header('Location: /AdminDashboard/index.php');
        exit();
    } else {
        return "Invalid login credentials.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    $error = loginUser($username, $password, $remember, $pdo);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style-client.css">
</head>

<body class="d-flex align-items-center" style="height: 90vh; margin: 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <form action="login.php" method="POST">
                    <div class="container d-flex justify-content-center align-items-center">
                        <div class="card" style="width: 800px; padding: 1rem;">
                            <h4 class="text-center">LOGIN</h4>

                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" placeholder="Username" id="username" name="username" value="<?php echo htmlspecialchars($_COOKIE['username'] ?? ''); ?>" required>
                                <label for="username">Username</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" placeholder="Password" id="password" name="password" value="<?php echo htmlspecialchars($_COOKIE['password'] ?? ''); ?>" required>
                                <label for="password">Password</label>
                            </div>

                            <?php if (isset($error)): ?>
                                <p class="text-danger"><?php echo $error; ?></p>
                            <?php endif; ?>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>

                            <button type="submit" class="btn btn-success">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
