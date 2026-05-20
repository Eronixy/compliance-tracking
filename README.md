# Compliance Tracking

A PHP-based compliance tracking application for managing compliance requirements, monitoring status, and maintaining records.

## Overview

Compliance Tracking is a simple web application built with PHP and CSS that helps organizations organize and monitor compliance-related information. It provides a structured interface for tracking requirements, updating statuses, and managing compliance data.

## Requirements

- PHP 7.4 or higher
- Apache, Nginx, or any compatible web server
- MySQL or MariaDB

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/fionajade/compliance-tracking.git
cd compliance-tracking
```

### 2. Configure the Web Server

- Place the project inside your web server directory
- Ensure PHP is installed and enabled
- Configure the document root if necessary

### 3. Create the Database

1. Create a new MySQL database:

```sql
CREATE DATABASE compliance_tracking_system;
```

2. Import the provided SQL file if available.

### 4. Configure Database Connection

Update the database credentials in the configuration file:

```php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$db = "compliance_tracking_system";
```

## Running the Application

Start your Apache and MySQL services, then open:

```text
http://localhost/compliance-tracking
```

## Features

- Manage compliance requirements
- Track compliance status and progress
- Store compliance-related records
- Simple and organized interface
- Database-backed data management

## Project Structure

```text
compliance-tracking/
├── README.md
├── index.php
├── config/
├── includes/
├── css/
├── js/
└── assets/
```

## Development

To modify or extend the project:

- Update PHP files for backend functionality
- Edit CSS files for styling changes
- Test changes in a local development environment

## License

See the `LICENSE` file for licensing information.

## Support

For issues, bug reports, or contributions, visit the GitHub repository.
