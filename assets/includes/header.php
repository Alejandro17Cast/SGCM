<?php
$tituloPagina = $tituloPagina ?? 'SGCM';
$baseUrl = '/SGCM';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($tituloPagina) ?> | SGCM</title>

    <script>
        (() => {
            const saved = localStorage.getItem('sgcm-theme');
            const preferred = matchMedia('(prefers-color-scheme: dark)').matches
                ? 'dark' : 'light';

            document.documentElement.dataset.theme = saved || preferred;
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- ESTILOS GENERALES -->
    <link rel="stylesheet" href="/SGCM/assets/css/styles.css">

    <!-- ESTRUCTURA GENERAL -->
    <link rel="stylesheet" href="/SGCM/assets/css/layout.css">

    <!-- DASHBOARD -->
    <link rel="stylesheet" href="/SGCM/assets/css/dashboard.css">

</head>

<body>

<div class="app-layout">