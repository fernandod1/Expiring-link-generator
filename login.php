<?php
session_start();
if ( ! empty( $_POST ) ) {
    if ( isset( $_POST['username'] ) && isset( $_POST['password'] ) ) {
        include('config.php');
    	if ( ($_POST['username']==$USERNAME) &&  ($_POST['password']==$PASSWORD) ) {
            $_SESSION['user_id'] = "pepe";
            header("Location: index.php");
    	}
    }
}
?>
<html>
    <head>
        <title>Counter's Control Panel - Login</title>
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"> 
    </head>
<body>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="container">
    <br><br>
    <h1>Log in:</h1><br>
    <form action="" method="post">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <hr>
</div> 
</body>
</html>