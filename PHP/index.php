<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CT-USM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card p-4 shadow" style="width: 25rem;">
        <h3 class="text-center mb-4">Plataforma CT-USM</h3>
        
        <form action="validar_login.php" method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario / Correo</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>

            <div class="mb-3">
                <label for="rol" class="form-label">Rol de ingreso</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="" selected disabled>Seleccione un rol...</option>
                    <option value="1">Responsable académico (Postulante)</option>
                    <option value="2">Evaluador CT-USM</option>
                    <option value="3">Administrador CT-USM</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">Ingresar</button>
        </form>
    </div>

</body>
</html>