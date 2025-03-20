# Simple Banking Plugin

## Overview

This is a WordPress plugin for a simple banking system, developed as a custom plugin. The project is containerized using Docker for easy deployment and local development.

## Prerequisites

Before starting, ensure you have installed:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com/)

## Build Docker Container

1. Create a new directory for your WordPress project and navigate into it:

   ```sh
   mkdir wordpress-docker
   cd wordpress-docker
   ```

2. Clone the repository containing the docker-compose.yaml file:

   ```sh
   git clone https://github.com/your-repo/simple-banking.git
   ```

3. Copy docker-compose.yaml into the root directory.

4. Run the following command to build and start the WordPress and MySQL containers:

   ```sh
   docker-compose up -d
   ```

5. Move plugin file under wp-content/plugins/

## Running in Docker

1. Access WordPress at `http://localhost:8005/wp-admin`.

2. If this is the first time running, install the WordPress by following the interactive prompts.

3. In login page page, use account below (setup on the docker-compose.yaml):

   - **Username:** admin
   - **Password:** admin

4. On the left menu, click Plugins > Installed Plugins then activate 'Simple Banking System'.

5. New menu will be added called 'Banking'.
