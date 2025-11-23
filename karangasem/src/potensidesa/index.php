<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Potensi Desa Karangasem</title>
    <link rel="icon" href="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png" type="image/png">
    <link rel="stylesheet" href="potensidesa.css?v=<?php echo time(); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Stack+Sans+Headline:wght@200..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />

    <script src="../script.js"></script>
    
    </head>
<body>

    <?php include 'navbar.php'; ?>
    
    <div class="container-potensi">
      <div class="section-1-potensi">
        <video autoplay muted loop playsinline class="potensi-bg-video">
          <source src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/bg-potensidesa.mp4" type="video/mp4">
        </video>
        <span class="text-title-potensi text-center">Potensi di Desa Karangasem</span>
      </div>

      <div class="section-2-potensi">
          <p style="text-align:center; padding: 2rem;">Memuat data potensi...</p>
      </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="potensidesa.js?v=<?php echo time(); ?>"></script>
</body>
</html>