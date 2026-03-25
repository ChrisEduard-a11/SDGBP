<?php
$file = __DIR__ . '/header.php';
$content = file_get_contents($file);

// 1. Simplify Custom CSS block entirely to mimic SB Admin standard
$css_replace_pattern = '/<style>.*?<\/style>/s';
$clean_css = '<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap");

    :root {
        --bs-dark: #212529;
        --bs-light: #f8f9fa;
        --sidebar-bg: #212529;
    }
    body {
        font-family: "Inter", system-ui, -apple-system, sans-serif !important;
        background-color: #f8f9fa;
    }
    [data-theme="dark"] body {
        background-color: #121212;
        color: #e0e0e0;
    }

    /* Topnav Styling SB Admin */
    .sb-topnav {
        padding-left: 0;
        height: 56px;
        z-index: 1039;
    }
    .sb-topnav .navbar-brand {
        width: 225px;
        padding-left: 1rem;
        padding-right: 1rem;
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
    }
    .sb-topnav.navbar-dark {
        background-color: #212529 !important;
    }
    [data-theme="dark"] .sb-topnav.navbar-dark {
        background-color: #0f172a !important;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    
    /* Sidenav Styling SB Admin */
    #layoutSidenav_nav {
        width: 225px;
        transform: translateX(-225px);
        transition: transform 0.15s ease-in-out;
        z-index: 1038;
    }
    .sb-sidenav-toggled #layoutSidenav_nav {
        transform: translateX(0);
    }
    .sb-sidenav {
        display: flex;
        flex-direction: column;
        height: 100%;
        flex-wrap: nowrap;
    }
    .sb-sidenav-dark {
        background-color: #212529;
        color: rgba(255, 255, 255, 0.5);
    }
    [data-theme="dark"] .sb-sidenav-dark {
        background-color: #0f172a;
        border-right: 1px solid rgba(255,255,255,0.05);
    }
    .sb-sidenav-dark .sb-sidenav-menu .nav-link {
        color: rgba(255, 255, 255, 0.5);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        text-decoration: none;
        font-size: 0.95rem;
    }
    .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover {
        color: #fff;
    }
    .sb-sidenav-dark .sb-sidenav-menu .nav-link .sb-nav-link-icon {
        color: rgba(255, 255, 255, 0.3);
        margin-right: 0.75rem;
        width: 1.5rem;
        text-align: center;
    }
    .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover .sb-nav-link-icon {
        color: #fff;
    }
    .sb-sidenav-dark .sb-sidenav-menu-heading {
        padding: 1.75rem 1rem 0.75rem;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.25);
    }
    .sb-sidenav-dark .sb-sidenav-footer {
        background-color: rgba(0, 0, 0, 0.2);
        padding: 0.75rem 1rem;
    }
    
    /* Content Layout */
    #layoutSidenav_content {
        min-width: 0;
        flex-grow: 1;
        min-height: calc(100vh - 56px);
        margin-left: -225px;
        transition: margin 0.15s ease-in-out;
    }
    .sb-sidenav-toggled #layoutSidenav_content {
        margin-left: 0;
    }
    @media (min-width: 992px) {
        #layoutSidenav_nav { transform: translateX(0); }
        #layoutSidenav_content { margin-left: 0; }
        .sb-sidenav-toggled #layoutSidenav_nav { transform: translateX(-225px); }
        .sb-sidenav-toggled #layoutSidenav_content { margin-left: -225px; }
    }
    
    /* Utility Dark Mode Fixes */
    [data-theme="dark"] .card, [data-theme="dark"] .bg-white, [data-theme="dark"] .modal-content {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: rgba(255,255,255,0.05) !important;
    }
    [data-theme="dark"] .table { color: #f8fafc !important; }
    
    /* Notification Drops */
    .dropdown-menu { font-size: 0.9rem; }
</style>';

$content = preg_replace($css_replace_pattern, $clean_css, $content);

// 2. Fix HTML structure
// Topnav
$content = preg_replace('/<nav class="sb-topnav fixed left-0 right-0 z-\[1040\] top-0 flex items-center h-\[70px\] px-4 md:px-6 bg-white dark:bg-\[#0f172a\] border-b border-slate-200 dark:border-slate-800 shadow-\[0_2px_10px_rgba\(0,0,0,0\.02\)\] transition-colors duration-300">/',
'<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">', $content);

$content = preg_replace('/<a class="flex items-center gap-3 text-decoration-none mr-2 md:mr-6"[^>]+>.*?<img[^>]+>.*?<span[^>]+>SDGBP<\/span>.*?<\/a>/s',
'<a class="navbar-brand ps-3 d-flex align-items-center gap-2" href="javascript:void(0);" onclick="navigateTo(\'inicio.php\')">
    <img src="<?php echo $_SESSION[\'foto\']; ?>" alt="Logo" class="rounded" style="width: 30px; height: 30px; object-fit: cover;">
    <span>SDGBP</span>
</a>', $content);

$content = preg_replace('/<button class="flex items-center justify-center w-10 h-10 rounded-full text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-slate-200 dark:focus:ring-slate-700 outline-none" id="sidebarToggle" href="#!">.*?<i class="fas fa-bars text-lg"><\/i>.*?<\/button>/s',
'<button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>', $content);

$content = preg_replace('/<ul class="flex items-center gap-1 mb-0 pl-0 list-none ml-auto lg:ml-0">/s',
'<ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4 flex-row align-items-center gap-2">', $content);

$content = preg_replace('/<div class="hidden lg:flex items-center ml-auto mr-6 text-slate-500 dark:text-slate-400 text-sm font-medium">/s',
'<div class="d-none d-lg-flex align-items-center ms-auto me-3 text-white-50 small">', $content);

$content = preg_replace('/<button id="btn-dark-mode"[^>]+>.*?<i class="fas fa-moon text-lg"><\/i>.*?<\/button>/s',
'<button id="btn-dark-mode" onclick="toggleDarkMode()" class="btn btn-link text-white-50 p-1"><i class="fas fa-moon"></i></button>', $content);

$content = preg_replace('/<a class="flex items-center justify-center w-10 h-10 rounded-full text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors dropdown-toggle focus:outline-none cursor-pointer" id="navbarDropdownNotif"/s',
'<a class="nav-link dropdown-toggle position-relative" id="navbarDropdownNotif"', $content);

$content = preg_replace('/<a class="flex items-center gap-2 cursor-pointer p-1 pr-3 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700 dropdown-toggle" id="navbarDropdown"[^>]+>.*?<img[^>]+>.*?<div[^>]+>.*?<span[^>]+><\?php echo \$nombre_usuario; \?><\/span>.*?<span[^>]+><\?php echo \$tipo_usuario; \?><\/span>.*?<\/div>.*?<i[^>]+><\/i>.*?<\/a>/s',
'<a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <img src="<?php echo $_SESSION[\'foto\']; ?>" alt="Avatar" class="rounded-circle" style="width:30px;height:30px;object-fit:cover;">
    <span class="d-none d-md-inline small"><?php echo $nombre_usuario; ?></span>
</a>', $content);

$content = preg_replace('/<div id="layoutSidenav_nav" class="transition-all duration-300 relative z-\[1038\]">/s',
'<div id="layoutSidenav_nav">', $content);

$content = preg_replace('/<nav class="sb-sidenav accordion flex flex-col h-\[calc\(100vh-80px\)\].*?rounded-2xl md:rounded-\[1\.5rem\] overflow-hidden"[^>]+>/s',
'<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">', $content);

// Regex explicitly fixed here! Notice the \/ escaping instead of / directly
$content = preg_replace('/class="([^>]*?)nav-link !flex items-center px-3 py-[23] rounded-xl text-slate-6[0-9]0 dark:text-orange-500 hover:text-orange-[0-9]{3} hover:bg-orange-50 dark:hover:bg-slate-800(?:[\/0-9]*) transition-all font-\[600\] group cursor-pointer text-\[15px\]"/s', 'class="$1nav-link"', $content);
$content = preg_replace('/class="([^>]*?)nav-link !flex items-center px-3 py-2 rounded-lg text-\[14px\] font-medium text-slate-500 dark:text-orange-500 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"/s', 'class="$1nav-link"', $content);
$content = preg_replace('/class="text-\[10px\] font-extrabold text-slate-400 dark:text-orange-500 uppercase tracking-widest px-3 pt[-a-z0-9 ]* pb-2(?: mt-2)?"/s', 'class="sb-sidenav-menu-heading"', $content);

$content = preg_replace('/<div class="w-8 flex justify-center text-slate-400 dark:text-orange-500 group-hover:text-orange-400 group-hover:scale-110 transition-transform">/s', '<div class="sb-nav-link-icon">', $content);
$content = preg_replace('/<div class="sb-sidenav-collapse-arrow text-slate-400 dark:text-orange-500 group-hover:text-orange-400 transition-transform duration-300">/s', '<div class="sb-sidenav-collapse-arrow">', $content);
$content = preg_replace('/<i class="(fas [^>]+) w-5 text-\[11px\] text-slate-400 dark:text-orange-500"><\/i>/s', '<div class="sb-nav-link-icon"><i class="$1"></i></div>', $content);

$content = preg_replace('/<div class="sb-sidenav-footer rounded-b-2xl md:rounded-b-\[1\.5rem\] bg-slate-50\/50 dark:bg-slate-900\/50 px-4 py-4 border-t border-slate-100\/50 dark:border-white\/5 flex flex-col mt-auto shadow-\[0_-10px_15px_-3px_rgba\(0,0,0,0\.02\)\] backdrop-blur-lg">/s', '<div class="sb-sidenav-footer">', $content);
$content = preg_replace('/<div class="text-\[10px\] font-bold text-slate-400 dark:text-orange-500 uppercase tracking-widest mb-1">/s', '<div class="small">', $content);
$content = preg_replace('/<span class="font-extrabold text-slate-800 dark:text-orange-500 text-sm tracking-wide">/s', '<span>', $content);

if ($content === null || strlen($content) < 1000) {
    echo "Error processing file! preg_last_error: " . preg_last_error();
    exit(1);
}
file_put_contents($file, $content);
echo "Success";
?>
