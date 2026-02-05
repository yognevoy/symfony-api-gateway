# Symfony API Gateway

API Gateway built with Symfony that provides a unified entry point for microservices and external APIs. The gateway offers advanced features like authentication, rate limiting, caching, request/response filtering, and load balancing.

## Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Configuration](#configuration)
- [Installation & Setup](#installation--setup)
- [Running the Application](#running-the-application)
- [Features](#features)
- [Configuration Options](#configuration-options)
- [Testing](#testing)
- [How to Contribute](#how-to-contribute)
- [License](#license)

## Overview

The Symfony API Gateway serves as a reverse proxy that routes incoming requests to backend services based on configurable route patterns.

## Technology Stack

- **Backend**: PHP 8.2+, Symfony 7.4.*
- **Caching**: Redis
- **DevOps**: Docker, Docker Compose

## Prerequisites

Before you begin, ensure you have met the following requirements:

- **Docker** (version 20.10 or higher)
- **Docker Compose** (version 2.0 or higher)
- **Git** (version 2.0 or higher)

## Configuration

### Environment Variables

Key environment variables in `.env`:

- `APP_ENV`: Application environment (dev, prod, test)
- `APP_SECRET`: Secret key for security-related operations
- `HTTP_PORT`: Port for the web server
- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`: Redis configuration for caching and rate limiting

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/yognevoy/symfony-api-gateway.git
cd symfony-api-gateway
```

### 2. Build and Start Containers

```bash
docker-compose up -d --build
```

### 3. Install PHP Dependencies

```bash
# Enter the PHP container
docker exec -it symfony_api_gateway_php composer install
```

## Running the Application

### Starting Services

```bash
docker-compose up -d
```

### Stopping Services

```bash
docker-compose down
```

### Accessing Services

- **API Gateway**: http://localhost:8000
- **Redis**: localhost:6379 (for external connections)

## Features

The API Gateway routes requests based on the configuration defined in `config/api_routes/routes.yaml`.

### Authentication Methods

The API Gateway supports multiple authentication methods:

1. **JWT Authentication**: Validates JWT tokens against a secret key
2. **API Key Authentication**: Validates API keys in headers
3. **Basic Authentication**: Validates username/password combinations
4. **No Authentication**: Allows anonymous access

### Rate Limiting

Rate limiting can be configured per route with the following parameters:
- `limit`: Maximum number of requests allowed
- `period`: Time period in seconds
- `per_client`: Whether to apply limits per client IP address

### Response Filtering

The gateway can filter response content by including or excluding specific fields:
- `include`: List of fields to include in the response
- `exclude`: List of fields to exclude from the response

### Caching

Responses can be cached with configurable TTL (time-to-live) values to improve performance and reduce load on backend services.

### Load Balancing

Multiple target URLs can be specified for a single route, and the gateway will distribute requests among them using a round-robin algorithm.

### Middleware System

The gateway supports a flexible middleware system that allows custom processing of requests and responses. Middleware can be configured per route in the route configuration file.

To create custom middleware:

1. Create a class in the `src/Middleware/` directory
2. Implement the `App\Middleware\MiddlewareInterface` which requires a `process()` method
3. Optionally, implement `ConfigurableMiddlewareInterface` if your middleware needs configuration parameters
4. Add middleware to the route configuration in `config/api_routes/routes.yaml`

Middlewares are executed in the order they appear in the configuration.

## Configuration Options

Route configurations are defined in YAML files located in the `config/api_routes/` directory. Each route can be configured with the following options:

### Basic Route Configuration

```yaml
routes:
  route_name:
    path: '/path/pattern'              # Path pattern to match
    target: 'https://api.example.com' # Target URL to forward requests to
    methods: ['GET', 'POST']          # HTTP methods allowed
```

### Advanced Configuration Options

```yaml
routes:
  route_name:
    path: '/advanced/{id}'
    target: 'https://api.example.com/advanced/{id}'
    methods: ['GET', 'POST', 'PUT', 'DELETE']

    # Authentication configuration
    authentication:
      type: 'jwt'                     # Type: jwt, api_key, basic, none
      secret: 'your-secret-key'       # For JWT authentication
      header: 'Authorization'         # Header name for API key
      prefix: 'Bearer '               # Prefix for API key
      keys: ['key1', 'key2']          # Valid API keys
      users:                          # For Basic authentication
        - username: 'admin'
          password: 'password123'

    # Rate limiting
    rate_limit:
      limit: 100                      # Max requests
      period: 60                      # Time period in seconds
      per_client: true               # Apply per client IP

    # Caching
    cache:
      ttl: 300                       # Cache TTL in seconds

    # Response filtering
    response_filter:
      include: ['field1', 'field2']  # Fields to include
      exclude: ['field3', 'field4']  # Fields to exclude

    # Request timeout and retries
    timeout:
      duration: 30                   # Request timeout in seconds
      retries: 3                     # Number of retry attempts
      retry_delay: 1000              # Delay between retries in ms

    # Logging
    logging:
      enabled: true                  # Enable logging
      type: 'file'                   # Log type: file, stream
      level: 'info'                  # Log level: debug, info, warning, error

    # Middleware
    middleware:
      - 'App\Middleware\LoggingMiddleware'
      - 'App\Middleware\CustomValidationMiddleware'
```

### Multiple Targets (Load Balancing)

You can specify multiple targets for load balancing:

```yaml
routes:
  load_balanced_route:
    path: '/balanced'
    target:
      - 'https://api1.example.com'
      - 'https://api2.example.com'
      - 'https://api3.example.com'
    methods: ['GET', 'POST']
```

## Testing

### Running Tests

```bash
# Run all tests
docker exec -it symfony_api_gateway_php ./bin/phpunit
```

## How to Contribute

If you find a bug or have a feature request, please check the [Issues page](https://github.com/yognevoy/symfony-api-gateway/issues) before creating a new one. For code contributions, fork the repository, make your changes on a new branch, and submit a pull request with a clear description of the changes. Please make sure to test your changes thoroughly before submitting.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
