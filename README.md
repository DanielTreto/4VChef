# 4VChef API 🍽️

Bienvenido a la API RESTful de **4VChef**, una aplicación desarrollada con Symfony para la gestión de recetas, ingredientes, pasos y valores nutricionales.

## 🚀 Tecnologías

*   **Framework:** Symfony 6/7
*   **Lenguaje:** PHP 8+
*   **Base de Datos:** MySQL / MariaDB (Doctrine ORM)
*   **Formato de Respuesta:** JSON

## ⚙️ Instalación

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/DanielTreto/4VChef.git
    cd 4VChef
    ```

2.  **Instalar dependencias:**
    ```bash
    composer install
    ```

3.  **Configurar base de datos:**
   
    Ajusta tu conexión en el archivo `.env`
    ```env
    DATABASE_URL="mysql://usuario:password@127.0.0.1:3306/4VChef?serverVersion=8.0.32&charset=utf8mb4"
    ```

5.  **Crear base de datos y esquema:**
    ```bash
    php bin/console doctrine:database:create
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
    ```

6.  **Iniciar servidor local:**
    ```bash
    symfony server:start
    ```

## 👥 Autores

Desarrollado para el módulo de Desarrollo Web - 2DAM.
Daniel Treto.
