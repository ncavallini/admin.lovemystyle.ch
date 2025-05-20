
<?php if($tablet = isset($_GET['tablet'])): ?>

<style>
    body {
        background-color: #c3d5ed;
    }
</style>

<?php endif ?>

<h1 class="text-center">Cliente registrato correttamente!</h1>

<p class="text-center text-success" style="font-size: 72pt;"><i class="fa-solid fa-circle-check"></i></p>
<p class="h3 special text-center">La tua registrazione è avvenuta con successo!</p>
<p class="h3 special text-center">Controlla la tua mail, dove sarà presente a breve la tua carta cliente.</p>
<p class="h2 special text-center">GRAZIE!</p>
<br>
<p class="h3 special text-center">Deine Registrierung war erfolgreich!</p>
<p class="h3 special text-center">Überprüfe deine E-Mail – deine Kundenkarte wird in Kürze dort verfügbar sein.</p>
<p class="h2 special text-center">DANKE!</p>
<br>
<p class="h3 special text-center">Your registration was successful!</p>
<p class="h3 special text-center">Check your email – your customer card will be available there shortly.</p>
<p class="h2 special text-center">THANK YOU!</p>

<script>
    setTimeout(() => {
        window.location.href = "/index.php?page=customers_add&tablet=1";
    }, 5000);
</script>