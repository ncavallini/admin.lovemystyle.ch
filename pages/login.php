<h1>Login</h1>

<form action="actions/auth/login.php" method="POST">
    <input type="text" class="form-control" required name="username" placeholder="Username">
    <br>
    <input type="password" class="form-control" required name="password" placeholder="Password">
    <br>
    <button type="submit" class="btn btn-outline-primary">Accedi</button>
    <br><p>&nbsp;</p>
    <a href="index.php?page=forgot_password">Password dimenticata?</a>

</form>