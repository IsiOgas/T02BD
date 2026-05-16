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
   - Rol 2 (Evaluador): evaluador@usm.cl
   - Rol 3 (Administrador): admin@usm.cl


SUPUESTOS TÉCNICOS Y DECISIONES DE DISEÑO:
De acuerdo con las instrucciones de la pauta, declaramos los siguientes supuestos asumidos durante el desarrollo:

1. Filtro "Mis Postulaciones" (Rol 1): Dado que el modelo original no vinculaba una postulación con el usuario del 
sistema que la digito, se añadió la columna 'Correo_Responsable' a la tabla 'Postulacion'. Se asume que el sistema
web registra automáticamente el correo de la sesión activa al momento de insertar una nueva postulación.

2. Evaluadores y Asignaciones (Rol 3): Se asume que la asignación de postulaciones realizada por el Administrador (Rol 3) 
a través de su panel exclusivo es de carácter administrativo y organizativo para el control interno de CT-USM. 
A nivel de sistema, todos los Evaluadores (Rol 2) pueden visualizar y evaluar cualquier postulación. 
Esto se diseñó intencionalmente para permitir que, en caso de ausencia del evaluador asignado originalmente, 
otro evaluador de turno pueda tomar el caso sin requerir que el Administrador reasigne el proyecto por sistema.

3. Creación de Cuentas: Dado que el enfoque del CRUD es la postulación, se asume que la creación de cuentas para Evaluadores 
y Administradores se realiza directamente a nivel de motor de base de datos o por un Superadministrador de TI 
externo a la plataforma web.

4. Diseño Web (Bonus Frontend): Se implementó la librería Bootstrap (tema Minty de Bootswatch) en todas las vistas
del sistema para garantizar una interfaz limpia, responsiva y ordenada.









