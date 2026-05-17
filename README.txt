Isidora Ogas 202473654-K
Antonia Contreras 202473554-3

INSTRUCCIONES DE INSTALACIÓN Y EJECUCIÓN:
1. Copiar la carpeta "PHP" completa dentro del directorio "htdocs" de XAMPP
2. Iniciar los módulos de Apache y MySQL en el panel de control de XAMPP
3. Abrir phpMyAdmin (http://localhost/phpmyadmin)
4. Importar el archivo SQL adjunto para crear la base de datos "ct_usm_postulaciones" junto con sus tablas, vistas, funciones, triggers, procedimientos almacenados y datos de prueba
5. Acceder a la plataforma desde el navegador web en la ruta: http://localhost/[nombre_de_la_carpeta]/PHP/index.php
6. Para probar los distintos niveles de acceso, utilizar las siguientes credenciales de prueba (la contraseña para todos es '1234'):
   - Rol 1 (Responsable / Postulante): postulante@usm.cl
   - Rol 1 (Responsable / postulante): postulante2@usm.cl
   - Rol 2 (Evaluador): evaluador@usm.cl
   - Rol 2 (Evaluador): evaluador2@usm.cl
   - Rol 3 (Administrador): admin@usm.cl


SUPUESTOS TÉCNICOS Y DECISIONES DE DISEÑO:
De acuerdo con las instrucciones de la pauta, declaramos los siguientes supuestos asumidos durante el desarrollo:

1. Un postulante solo puede borrar y editar las postulaciones que él creó.

2. Solo el postulante puede las postulaciones en estado "borrador"

3. Se asume que la creación de cuentas se hace directamente en la base de datos, solo se implementó log in con cuentas 
   previamente creadas

4. Se implementó la librería Bootstrap (tema Minty de Bootswatch) en todas las vistas del sistema para garantizar una 
   interfaz limpia, linda y ordenada.










