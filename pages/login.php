<h1>Login</h1>


<?php
    if(isset($_GET['error'])) {
        Utils::print_error("Impossibile effettuare l'accesso. Verificare i dati immessi e riprovare."); 
    }
?>

<form action="actions/auth/login.php" method="POST">
    <input type="text" class="form-control" required name="username" placeholder="Username">
    <br>
    <input type="password" class="form-control" required name="password" placeholder="Password">
    <br>
    <button type="submit" class="btn btn-outline-primary">Accedi</button>
    <br><p>&nbsp;</p>
    <a href="index.php?page=forgot-password">Password dimenticata?</a>

</form>