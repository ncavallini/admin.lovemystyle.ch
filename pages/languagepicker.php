<style>
    body {
        background-color: #c3d5ed;
        text-align: center;
    }

    .flag-container {
        display: flex;
        justify-content: center;
        gap: 40px; /* Adjust spacing between flags */
        margin-top: 40px;
    }

    .flag {
        cursor: pointer;
        transition: transform 0.2s;
    }

    .flag:hover {
        transform: scale(1.1);
    }
</style>

<h1>Scegli la tua lingua &mdash; Wähle deine Sprache aus &mdash; Choose your language</h1>
<div class="flag-container">
    <div class="flag" data-lang="it"><img src="https://flagsapi.com/IT/flat/64.png"></div>
    <div class="flag" data-lang="de"><img src="https://flagsapi.com/DE/flat/64.png"></div>
    <div class="flag" data-lang="en"><img src="https://flagsapi.com/GB/flat/64.png"></div>
</div>

<script>
    document.querySelectorAll('.flag').forEach(flag => {
        flag.addEventListener('click', () => {
            const lang = flag.getAttribute('data-lang');
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('lang', lang);
            const tablet = urlParams.get('tablet') || 0;
            const to = urlParams.get('to') || 'index.php';
            const redirectUrl = `index.php?lang=${lang}&tablet=${tablet}&page=${to}`;
            window.location.href = redirectUrl;
        });
    });
</script>
