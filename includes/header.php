<?php
require_once __DIR__ . '/session.php';
checkAuth();
$currentUser = getCurrentUser();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?php echo $pageTitle ?? 'Sistema de Farmacia'; ?></title>
    <!-- CSS files -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet"/>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }
        .navbar-brand-image {
            height: 2rem;
        }
        .card-body-scrollable {
            overflow: auto;
            height: auto;
        }
        .low-stock {
            background-color: #fff3cd;
        }
        .expired {
            background-color: #f8d7da;
        }
        .near-expiry {
            background-color: #fff3cd;
        }
    </style>
    <?php if (isset($customCSS)) echo $customCSS; ?>
</head>
<body>
    <div class="page">
