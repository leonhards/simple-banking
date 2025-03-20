# README.md

# Simple Banking Plugin

## Installation

1. Clone this repository into your WordPress plugin directory:
   ```sh
   git clone https://github.com/your-repo/simple-banking.git wp-content/plugins/simple-banking
   ```
2. Activate the plugin from the WordPress admin panel.

## Running in Docker

1. Start the environment:
   ```sh
   docker-compose up -d
   ```
2. Access WordPress at `http://localhost:8000`.
3. Login with:
   - **Username:** admin
   - **Password:** admin

## Running Tests

To run PHPUnit tests:

```sh
docker exec -it wordpress-container vendor/bin/phpunit
```

---
