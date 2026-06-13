<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — <?= htmlspecialchars($appName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #1a2332; min-height: 100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { width: 100%; max-width: 400px; }
        .login-logo { font-size: 2.5rem; color: #f59e0b; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="card shadow-lg border-0">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <div class="login-logo"><i class="bi bi-gear-wide-connected"></i></div>
                <h4 class="fw-bold mt-2"><?= htmlspecialchars($appName) ?></h4>
                <p class="text-muted small">Control de inventario</p>
            </div>

            <?php if (!empty($flash['error'])): ?>
                <?php foreach ($flash['error'] as $msg): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/?modulo=auth&accion=login">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control"
                               placeholder="usuario@taller.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control"
                               placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-white-50 small mt-3">
        <?= htmlspecialchars($appName) ?> &copy; <?= date('Y') ?>
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
