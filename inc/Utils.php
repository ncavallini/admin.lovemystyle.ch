<?php
class Utils
{
    public static function print_error(string $message, bool $needs_bootstrap = false)
    {

        if ($needs_bootstrap) {
            echo <<<EOD
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
                EOD;
        }

        echo <<<EOD
                <div class="alert alert-danger" role="alert">
                    $message
                </div>
                EOD;
    }

    public static function redirect(string $destination)
    {
        echo <<<EOD
                <script>
                    window.location.href = "$destination";
                </script>
            EOD;
    }
}
?>