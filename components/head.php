<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_GET['lang'] ?? 'it', ENT_QUOTES, 'UTF-8'); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Love My Style - Gestionale</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>


    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/b5e6e507d9.js" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Bootbox -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" integrity="sha512-oVbWSv2O4y1UzvExJMHaHcaib4wsBMS5tEP3/YkMP6GmkwRJAa79Jwsv+Y/w7w2Vb/98/Xhvck10LyJweB8Jsw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="/inc/bootbox.locales.js"></script>
    <script>
        bootbox.setDefaults({
            locale: "it",
            size: "sm"
        });
    </script>

    <!-- BarcodeJS -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/barcodes/JsBarcode.code128.min.js"></script>

    <!-- Select2JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="/inc/select2.it.js"></script>

    <!-- Toaster -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-toaster/5.1.0/css/bootstrap-toaster.min.css" integrity="sha512-613efYxCWhUklTCFNFaiPW4q6XXoogGNsn5WZoa0bwOBlVM02TJ/JH7o7SgWBnJIQgz1MMnmhNEcAVGb/JDefw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   
   
    <!-- Datatables -->
<link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.1/b-3.2.3/b-html5-3.2.3/b-print-3.2.3/date-1.5.5/r-3.0.4/sc-2.4.3/sb-1.8.2/sp-2.3.3/sr-1.4.1/datatables.min.css" rel="stylesheet" integrity="sha384-mXxDhEZpXJ1YrR8nGHywsjUQu9LLOhuGlHKxwqotoR/EYKqsOx/I/TCwOilSJ15k" crossorigin="anonymous">
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js" integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js" integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.1/b-3.2.3/b-html5-3.2.3/b-print-3.2.3/date-1.5.5/r-3.0.4/sc-2.4.3/sb-1.8.2/sp-2.3.3/sr-1.4.1/datatables.min.js" integrity="sha384-9ydJn/veoHOUtkA39WebwtVwiM8Vl3NJt7LAXxhQYylZvdH/fZF0/AQW6RBXvPr+" crossorigin="anonymous"></script>

<script>
      $.fn.dataTable.ext.type.order['date-eu-pre'] = function (dateStr) {
      if (!dateStr) return 0;
      var parts = dateStr.split('/');
      return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
  };
  $.fn.dataTable.ext.type.order['date-eu-asc'] = function (a, b) {
      return a - b;
  };
  $.fn.dataTable.ext.type.order['date-eu-desc'] = function (a, b) {
      return b - a;
  };
</script>


<!-- Chart.js -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" integrity="sha512-CQBWl4fJHWbryGE+Pc7UAxWMUMNMWzWxF4SQo9CgkJIN1kx6djDQZjh3Y8SZ1d+6I+1zze6Z7kHXO7q3UyZAWw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<!-- Flatpickr -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/it.js"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/de.js"></script>



    <link rel="stylesheet" href="inc/style.css">
    <link rel="stylesheet" href="inc/quiche_display.css">
    <script src="/inc/util.js"></script>
    <script src="/inc/postcode.js"></script>

    <!-- Favicon -->

    <link rel="icon" type="image/png" href="/assets/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Love My Style" />
    <link rel="manifest" href="/assets/favicon/site.webmanifest" />

    <!-- Metatags -->

    <!-- Primary Meta Tags -->
    <meta name="title" content="Love My Style" />
    <meta name="description" content="Boutique" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://metatags.io/" />
    <meta property="og:title" content="Love My Style" />
    <meta property="og:description" content="Boutique" />
    <meta property="og:image" content="/assets/logo/metadata_base.png" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://metatags.io/" />
    <meta property="twitter:title" content="Love My Style" />
    <meta property="twitter:description" content="Boutique" />
    <meta property="twitter:image" content="/assets/logo/metadata_base.png" />

    <!-- Meta Tags Generated with https://metatags.io -->
</head>

<body>