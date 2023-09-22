# Lab PHP : Project 2 - REST API

![Made by Me](https://github.com/raven-panda/ressources/blob/main/badges/made-by-me.svg)

This PHP-based REST API allows you to manage various web development technologies, including creating, reading, updating, and deleting technologies, as well as managing associated categories. It uses a MySQL database to store information.

## Server Configuration

To run this API, you will need two Docker containers made with a docker-compose:

1. **Database Server (MySQL):** The MySQL database in a Docker service. You can use the provided SQL file to create the database structure.

2. **Apache + PHP Server:** The Apache server and PHP in another Docker service. Ensure that the server is configured to allow URL rewriting and route all URLs to the `index.php` file.

## Installation

1. Clone this GitHub repository to your local system:

```bash
git clone https://github.com/your-username/php-dev-api.git
```

2. Configure your Docker environment with the two containers mentioned above.

3. Configure the MySQL database connection settings in the config.php file.

You can now use the API by following the documented URLs and endpoints below.

## API Endpoints

This is work in progress.

## Example JSON Payload for Creating a Technology

```json
{
  "name": "Ruby",
  "category": "Programming Languages",
  "resources": ["https://ruby-lang.org"],
  "logo": "ruby.png"
}
```


## Testing the API

You can test the API using Postman or other API testing tools by using the URLs mentioned above.