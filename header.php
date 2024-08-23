<!DOCTYPE html>
<html lang="en"> <!--begin::Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>ISW | CERT MANAGER </title><!--begin::Primary Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="ISW | CERT MANAGER">
    <meta name="author" content="Godstime Chisom Jude">
    <meta name="description" content="Dashboad for SSL cert management built with AdminLTE">
    <meta name="keywords" content=""><!--end::Primary Meta Tags--><!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous"><!--end::Fonts--><!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous"><!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous"><!--end::Third Party Plugin(Bootstrap Icons)--><!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="css/adminlte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>

</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary"> <!--begin::App Wrapper-->
    <div class="app-wrapper"> 
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"> <i class="bi bi-list"></i> </a> </li>
                    <li class="nav-item d-none d-md-block"> <a href="#" class="nav-link">Home</a> </li>
                    <li class="nav-item d-none d-md-block"> <a href="#" class="nav-link">Request New Cert</a> </li>
                </ul> <!--end::Start Navbar Links--> <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto"> <!--begin::Navbar Search-->
                    <li class="nav-item"> <a class="nav-link" data-widget="navbar-search" href="#" role="button"> <i class="bi bi-search"></i> </a> </li> <!--end::Navbar Search-->
                    
                    <li class="nav-item dropdown user-menu"> <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"> <img src="img/user2-160x160.jpg" class="user-image rounded-circle shadow" alt="User Image"> <span class="d-none d-md-inline">Admin</span> </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end"> <!--begin::User Image-->
                            <li class="user-header text-bg-primary"> <img src="img/user2-160x160.jpg" class="rounded-circle shadow" alt="User Image">
                                <p>
                                    Godstime Jude 
                                    <small>SRE APPS</small>
                                </p>
                            </li> <!--end::User Image--> <!--begin::Menu Body-->
                            <li class="user-body"> <!--begin::Row-->
                               
                            </li> <!--end::Menu Body--> <!--begin::Menu Footer-->
                            <li class="user-footer"> <a href="#" class="btn btn-default btn-flat">Profile</a> <a href="#" class="btn btn-default btn-flat float-end">Sign out</a> </li> <!--end::Menu Footer-->
                        </ul>
                    </li> <!--end::User Menu Dropdown-->
                </ul> <!--end::End Navbar Links-->
            </div> <!--end::Container-->
        </nav> <!--end::Header--> 
        
        <!--begin::Sidebar-->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme=""> <!--begin::Sidebar Brand-->
            <div class="sidebar-brand"> <!--begin::Brand Link--> <a href="index.php" class="brand-link"> <!--begin::Brand Image--> 
                <img src="img/iswlogo-removebg.png" alt="iswlogo" class="brand-image "> <!--end::Brand Image--> 
                <!--begin::Brand Text--> <span class="brand-text fw-light"></span> <!--end::Brand Text--> </a> <!--end::Brand Link--> </div> <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
            <div class="sidebar-wrapper">
                <nav class="mt-2"> <!--begin::Sidebar Menu-->
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                        <li class="nav-item menu-open"> <a href="#" class="nav-link"> <i class="nav-icon bi bi-house"></i>
                                <p>
                                    Home
                                </p>
                            </a>
                        </li>

                        <li class="nav-header">CERT MGT</li>

                        <li class="nav-item"> <a href="upload.php" class="nav-link"> <i class="nav-icon bi bi-upload"></i>
                            <p>Upload Cert</p>
                        </a> </li>
                        
                        <li class="nav-item"> <a href="#" class="nav-link"> <i class="nav-icon bi bi-stack"></i>
                            <p>View All Certs</p>
                        </a> </li>
                        <li class="nav-item"> <a href="#" class="nav-link"><i class="nav-icon bi bi-speedometer"></i>
                            <p>Cert Status <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a> 
                        <ul class="nav nav-treeview">
                            <li class="nav-item"> <a href="#" class="nav-link"> <i class="nav-icon bi bi-arrow-right-circle"></i>
                                    <p>Expiring Cert</p>
                                </a> </li>
                            <li class="nav-item"> <a href="#" class="nav-link"> <i class="nav-icon bi bi-arrow-right-circle"></i>
                                    <p>Active Cert</p>
                                </a> </li>

                                <li class="nav-item"> <a href="#" class="nav-link"> <i class="nav-icon bi bi-arrow-right-circle"></i>
                                    <p>Send Out Reminders</p>
                                </a> </li>
                        </ul>
                    
                        </li>
                       
                        <li class="nav-header">USER </li>
                        <li class="nav-item"> <a href="#" class="nav-link"> <i class="nav-icon bi bi-person-add"></i>
                            <p>Add New User</p>
                        </a> </li>
                        
                        <li class="nav-item"> <a href="#" class="nav-link"> <i class="nav-icon bi-box-arrow-right"></i>
                            <p>Logout</p>
                        </a> </li>

                       
                    </ul> <!--end::Sidebar Menu-->
                </nav>
            </div> <!--end::Sidebar Wrapper-->
        </aside> <!--end::Sidebar--> <!--begin::App Main-->


        <main class="app-main"> <!--begin::App Content Header-->
            <div class="app-content-header"> <!--begin::Container-->
                <div class="container-fluid"> <!--begin::Row-->
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">Welcome to Cert Manager </h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Dashboard
                                </li>
                            </ol>
                        </div>
                    </div> <!--end::Row-->
                </div> <!--end::Container-->
            </div>
            <div class="app-content"> <!--begin::Container-->
                <div class="container-fluid"> <!-- Info boxes -->
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box"> <span class="info-box-icon text-bg-primary shadow-sm"> <i class="bi bi-stack"></i> </span>
                                <div class="info-box-content"> <span class="info-box-text">All Certs</span> <span class="info-box-number">
                                        10
                                         </span> </div> <!-- /.info-box-content -->
                            </div> <!-- /.info-box -->
                        </div> <!-- /.col -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box"> <span class="info-box-icon text-bg-danger shadow-sm"> <i class="bi bi-download"></i> </span>
                                <div class="info-box-content"> <span class="info-box-text">Downloads</span> <span class="info-box-number">410</span> </div> <!-- /.info-box-content -->
                            </div> <!-- /.info-box -->
                        </div> <!-- /.col --> <!-- fix for small devices only --> <!-- <div class="clearfix hidden-md-up"></div> -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box"> <span class="info-box-icon text-bg-success shadow-sm"> <i class="bi bi-folder-plus"></i> </span>
                                <div class="info-box-content"> <span class="info-box-text">New</span> <span class="info-box-number">7</span> </div> <!-- /.info-box-content -->
                            </div> <!-- /.info-box -->
                        </div> <!-- /.col -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box"> <span class="info-box-icon text-bg-warning shadow-sm"> <i class="bi bi-exclamation-triangle"></i> </span>
                                <div class="info-box-content"> <span class="info-box-text">Expiring</span> <span class="info-box-number">2</span> </div> <!-- /.info-box-content -->
                            </div> <!-- /.info-box -->
                        </div> <!-- /.col -->
                    </div> <!-- /.row --> <!--begin::Row-->