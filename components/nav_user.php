<?php
if (isset($_GET['tablet'])) return;
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand special uppercase" href="index.php?page=home">Love My Style</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=sales_view">Vendite</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=products_view">Prodotti</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=brands_view">Brand</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=suppliers_view">Fornitori</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=customers_view">Clienti</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=giftcards_view">Carte Regalo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=discount-codes_view">Codici Sconto</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Sistema di cassa
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="index.php?page=pos_view">Stato sistema</a></li>
            <li><a class="dropdown-item" href="index.php?page=pos_closings">Chiusura di cassa</a></li>
            <li><a class="dropdown-item" href="index.php?page=admin_cash">Contenuto cassa</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Strumenti
          </a>
          <ul class="dropdown-menu">
          <li><a href="index.php?page=shippings_view" class="dropdown-item">Spedizioni</a></li>
                      <li><hr class='dropdown-divider'></li>
          <li><a class="dropdown-item" href="index.php?page=stats_view">Statistiche</a></li>
          <li><a class="dropdown-item" href="https://myportal.nexi.swiss" target="_blank">Nexi MyPortal (statistiche carta di credito)</a></li>
            <?php if(Auth::is_owner(true)): ?> <li><hr class='dropdown-divider'></li><?php endif ?>
            <?php if(Auth::is_owner(true)): ?> <li><a class="dropdown-item" href="https://portal.twint.ch/" target="_blank">TWINT Merchant Portal</a></li> <?php endif ?>
            <?php if(Auth::is_owner(true)): ?> <li><a class="dropdown-item" href="https://gioia.portal.gkb.ch/a" target="_blank">E-banking BCG</a></li> <?php endif ?>
            <li><hr class='dropdown-divider'></li>
            <li><a class="dropdown-item" href="https://ic2.globalblue.com" target="_blank">Global Blue (tax free)</a></li>
            <li><a href="https://www.eda.admin.ch/eda/it/dfae/rappresentanze-e-consigli-di-viaggio/schweizer-vertretungen-im-ausland.html" target="_blank" class="dropdown-item">Rappresentanze della Svizzera all'estero (Ambasciate e Consolati)</a></li>
            <li><hr class='dropdown-divider'></li>
            <li><a class="dropdown-item" href="https://unlayer.com" target="_blank">Unlayer Email Builder</a></li>
            <li><a class="dropdown-item" href="https://app.brevo.com/" target="_blank">Brevo (newsletter)</a></li>
            <li><hr class='dropdown-divider'></li>
            <li><a class="dropdown-item" href="https://my.arlo.com/" target="_blank">Telecamere</a></li>
            <?php if(Auth::is_owner(true)): ?> 
              <li><hr class='dropdown-divider'></li>
              <li><a class="dropdown-item" href="https://bizay.ch" target="_blank">Tipografia</a></li>

            <?php endif; ?>

          </ul>
        </li>


        <?php if (Auth::is_owner(true)): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Amministrazione
            </a>
            <ul class="dropdown-menu">
              <?php if (Auth::is_admin()): ?>
                <li><a class="dropdown-item" href="index.php?page=admin_logging">Log</a></li>
              <?php endif ?>
              <li><a class="dropdown-item" href="index.php?page=users_view">Utenti</a></li>
            </ul>
          </li>
        <?php endif ?>
        <li class="nav-item">
          <a class="nav-link" href="actions/auth/logout.php">Logout</a>
        </li>
      </ul>
      <form class="d-flex" role="search" action="index.php" method="get">
        <input type="hidden" name="page" value="search">
        <input name="q" class="form-control me-2" type="search" placeholder="Ricerca rapida" aria-label="Ricerca rapida">
        <button class="btn btn-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>

    </div>
  </div>
</nav>