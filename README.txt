Isidora Ogas 202473654-K
Antonia Contreras 202473554-3

Instrucciones de ejecución:
Abrir XAMPP y presionar start en MySQL y Apache, Ingresar a phpMyAdmin e importar los archivos sql.
Primero "script_de_creacion.sql" para crear las tablas, luego "script_de_insercion.sql" para poblar la base con los datos
y por ultimo "consultas.sql"

Supuestos:
Para los diagramas de la parte 1 y 2 usamos notacion UML y trabajamos en draw.io guiandonos del ppt de la unidad 2.
En la parte 2 identificamos las llaves primarias con FK, las llaves foraneas con FK y los atributos únicos con UQ.
Para el diccionario de datos nos basamos en el ejemplo del ppt de unidad 2.
En la parte 4 además de los datos pedidos, añadimos algunos extra para que las consultas de la parte 5 mostraran algo,
añadimos 2 postulaciones con equipo incompleto, una empresa que no esta relacionada a ninguna postulación.
En total hay 12 postulaciones, 10 con el equipo de trabajo correcto y 2 con el equipo incompleto.
También algunas postulaciones se exceden del plazo de 36 semanas.
Se asume que las semanas siempre serán semanas completas no media semana y que los Rut no llevan puntos.
No modelamos la entidad "Documentos", lo dejamos como un atributo VARCHAR(100) en la entidad iniciativa.









