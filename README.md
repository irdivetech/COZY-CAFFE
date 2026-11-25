# CozyCafe

CozyCafe is a native PHP 8.2, MySQL 8, and Apache cafe ordering and table reservation application. It does not use a PHP framework or Composer.

## Requirements

- PHP 8.x with PDO MySQL for local XAMPP use.
- MySQL 8.0.
- Docker Engine with the Docker Compose plugin for containerized local use.

## Run locally with XAMPP

1. Copy the project into `C:\xampp\htdocs\cozycoffe`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Import `database/cozycafe.sql` in phpMyAdmin. The script creates the `cozycafe` database and its initial schema and seed data. Importing it again drops and recreates the application tables.
4. The default local connection settings in `config/database.php` use `localhost:3306`, database `cozycafe`, user `root`, and an empty password. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` in the PHP environment if your local MySQL credentials differ.
5. Open `http://localhost/cozycoffe/public/`.

The sample accounts created by the SQL seed are:

- Admin: `admin@cozycafe.com` / `password123`
- Customer: `customer@cozycafe.com` / `password123`

Change the default Admin password after the first login.

## Run locally with Docker Compose

Docker Compose builds the PHP/Apache image, starts a separate MySQL 8.0 service, imports the SQL seed on the database's first initialization, and persists both database data and uploads in named volumes.

1. From the project root, set strong local database passwords in your shell or a local `.env` file. For example:

   ```env
   DB_USER=cozycafe_user
   DB_PASS=replace-with-a-strong-local-password
   MYSQL_ROOT_PASSWORD=replace-with-a-different-strong-password
   APP_PORT=8080
   ```

   `.env` is ignored by Docker build context and should not be committed.

2. Build and start the services:

   ```powershell
   docker compose up --build -d
   ```

3. Open `http://localhost:8080/`.
4. Follow the initial-login guidance above. Uploaded menu images and MySQL data persist across container rebuilds in `uploads_data` and `mysql_data`.
5. Stop the services with `docker compose down`. To preserve database and uploads, do not add `--volumes`.

MySQL only runs the initialization SQL when its data directory is empty. To re-import the schema for a disposable local environment, remove the Compose volumes with `docker compose down --volumes` and start the services again. This permanently deletes the local Compose database and uploaded files.

## Deploy to Dokploy

### 1. Create the project and database

1. In Dokploy, create a project named `CozyCafe`.
2. Add and deploy a MySQL 8.0 database service to that project.
3. Record the database's internal hostname, database name, user, and strong password.
4. Import `database/cozycafe.sql` into the database once using a database client or the Dokploy terminal. The Compose database auto-import is for local Compose use; Dokploy's separate database service is initialized independently.

### 2. Deploy the application

1. Push the project to a Git repository and connect that repository and deployment branch in Dokploy.
2. Add an Application service and choose **Dockerfile** as its build type. Set the Dockerfile path to `./Dockerfile` and the build context to the project root.
3. Configure these application environment variables with the credentials from the database service:

   ```env
   DB_HOST=<internal-database-hostname>
   DB_PORT=3306
   DB_NAME=cozycafe
   DB_USER=<database-user>
   DB_PASS=<strong-database-password>
   APP_ENV=production
   ```

4. Add a persistent volume mount for menu uploads:

   - Volume: `cozycafe-uploads`
   - Container path: `/var/www/html/public/uploads`

5. Configure the application domain to route to container port `80` and enable HTTPS/Let's Encrypt.
6. Deploy the application and check the build and runtime logs.

### 3. Verify the deployment

1. Open the configured HTTPS domain and confirm the application connects to MySQL.
2. Sign in with the seeded Admin account, then change its default password.
3. Create a menu and upload an image; redeploy the app and confirm the image remains available.
4. Test customer registration/login, a reservation, and both Takeaway and Dine-in checkout flows.
5. Confirm orders and cash payments from the Admin order page.

## Database and uploads

- Local XAMPP database environment variables are optional when using the default local credentials.
- Docker Compose supplies the database connection variables to the app container and keeps MySQL private to the Compose network.
- The application container exposes Apache on port `80`; Compose maps it to host port `8080` by default.
- For Dokploy, use its internal database hostname and a persistent volume mounted at `/var/www/html/public/uploads`.
