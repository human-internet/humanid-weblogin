# Contribution Guide

## Set-up Local Development

### Prerequisites

1. Docker
2. Docker Compose
3. UNIX Shell (`bash`/ )

#### Optional

1. Node v14
   > Install and manage node version with `nodenv` is recommended

### Quick Start

1. Configure local deployment in `.env`
    ```shell
    cp .example.env .env
    ```

2. Configure app in `src/.env`
    ```bash
    cp src/.example.env src/.env
    ```

3. Start Server
    ```shell
    docker-compose up -d
    ```

4. Install Dependencies and Fix Permission
    ```
    # Enter container shell
    docker-compose exec app bash

    # Install dependencies
    composer install

    # Set permissions
    chmod -R 775 application/cache/ application/logs/ sessions/
    chmod g+s application/cache/ application/logs/ sessions/
    ```

5. Check App by open web page `http://localhost:8000/demo` in Web Browser

## Building Stylsheets

This step is required if any changes made in `scss` directory. Compiled stylesheets has been included in project.

```shell
npm install
npm run prod
```
