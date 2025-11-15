        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light d-print-none">
            <div class="container-xl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="dashboard.php">
                        <i class="ti ti-vaccine icon me-2" style="font-size: 32px; color: #206bc4;"></i>
                        Sistema de Farmacia
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last">
                    <!-- Notifications -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Notificaciones">
                            <span class="avatar avatar-sm" style="background-image: url(https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?>&background=206bc4&color=fff)"></span>
                            <div class="d-none d-xl-block ps-2">
                                <div><?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></div>
                                <div class="mt-1 small text-muted"><?php echo ucfirst($currentUser['rol']); ?></div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="profile.php" class="dropdown-item"><i class="ti ti-user icon me-2"></i>Perfil</a>
                            <a href="settings.php" class="dropdown-item"><i class="ti ti-settings icon me-2"></i>Configuración</a>
                            <div class="dropdown-divider"></div>
                            <a href="modules/auth/logout.php" class="dropdown-item"><i class="ti ti-logout icon me-2"></i>Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Horizontal Menu -->
        <div class="navbar-expand-md">
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="navbar navbar-light">
                    <div class="container-xl">
                        <ul class="navbar-nav">
                            <li class="nav-item <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                                <a class="nav-link" href="dashboard.php">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-dashboard icon"></i>
                                    </span>
                                    <span class="nav-link-title">Dashboard</span>
                                </a>
                            </li>

                            <li class="nav-item dropdown <?php echo in_array($currentPage, ['pos', 'sales']) ? 'active' : ''; ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-sales" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-shopping-cart icon"></i>
                                    </span>
                                    <span class="nav-link-title">Ventas</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="pos.php">
                                        <i class="ti ti-cash-register icon me-2"></i>Punto de Venta
                                    </a>
                                    <a class="dropdown-item" href="sales-list.php">
                                        <i class="ti ti-list icon me-2"></i>Historial de Ventas
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item dropdown <?php echo in_array($currentPage, ['inventory', 'medicines', 'categories', 'lots']) ? 'active' : ''; ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-inventory" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-package icon"></i>
                                    </span>
                                    <span class="nav-link-title">Inventario</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="medicines.php">
                                        <i class="ti ti-pill icon me-2"></i>Medicamentos
                                    </a>
                                    <a class="dropdown-item" href="categories.php">
                                        <i class="ti ti-category icon me-2"></i>Categorías
                                    </a>
                                    <a class="dropdown-item" href="lots.php">
                                        <i class="ti ti-stack icon me-2"></i>Lotes
                                    </a>
                                    <a class="dropdown-item" href="inventory-movements.php">
                                        <i class="ti ti-arrows-exchange icon me-2"></i>Movimientos
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item <?php echo ($currentPage == 'prescriptions') ? 'active' : ''; ?>">
                                <a class="nav-link" href="prescriptions.php">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-file-text icon"></i>
                                    </span>
                                    <span class="nav-link-title">Recetas</span>
                                </a>
                            </li>

                            <li class="nav-item dropdown <?php echo in_array($currentPage, ['customers', 'suppliers', 'doctors']) ? 'active' : ''; ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-people" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-users icon"></i>
                                    </span>
                                    <span class="nav-link-title">Personas</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="customers.php">
                                        <i class="ti ti-user icon me-2"></i>Clientes
                                    </a>
                                    <a class="dropdown-item" href="suppliers.php">
                                        <i class="ti ti-truck icon me-2"></i>Proveedores
                                    </a>
                                    <a class="dropdown-item" href="doctors.php">
                                        <i class="ti ti-stethoscope icon me-2"></i>Médicos
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item <?php echo ($currentPage == 'purchases') ? 'active' : ''; ?>">
                                <a class="nav-link" href="purchases.php">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-shopping-bag icon"></i>
                                    </span>
                                    <span class="nav-link-title">Compras</span>
                                </a>
                            </li>

                            <li class="nav-item dropdown <?php echo in_array($currentPage, ['reports']) ? 'active' : ''; ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-reports" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-chart-bar icon"></i>
                                    </span>
                                    <span class="nav-link-title">Reportes</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="reports-sales.php">
                                        <i class="ti ti-report-money icon me-2"></i>Ventas
                                    </a>
                                    <a class="dropdown-item" href="reports-inventory.php">
                                        <i class="ti ti-report icon me-2"></i>Inventario
                                    </a>
                                    <a class="dropdown-item" href="reports-expiry.php">
                                        <i class="ti ti-calendar-time icon me-2"></i>Vencimientos
                                    </a>
                                </div>
                            </li>

                            <?php if (hasRole('admin')): ?>
                            <li class="nav-item dropdown <?php echo in_array($currentPage, ['users', 'config']) ? 'active' : ''; ?>">
                                <a class="nav-link dropdown-toggle" href="#navbar-admin" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        <i class="ti ti-settings icon"></i>
                                    </span>
                                    <span class="nav-link-title">Administración</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="users.php">
                                        <i class="ti ti-users icon me-2"></i>Usuarios
                                    </a>
                                    <a class="dropdown-item" href="config.php">
                                        <i class="ti ti-settings icon me-2"></i>Configuración
                                    </a>
                                    <a class="dropdown-item" href="logs.php">
                                        <i class="ti ti-list-details icon me-2"></i>Logs
                                    </a>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
