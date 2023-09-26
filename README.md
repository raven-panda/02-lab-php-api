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
git clone https://github.com/your-username/php-dev-api.git ./
```

2. Configure your Docker environment with the two containers mentioned above.

3. Configure the MySQL database connection settings in the config.php file.

You can now use the API by following the documented URLs and endpoints below.

## API Endpoints

Response are in JSON and requests body must be in JSON (except POST, examples bellow).

- GET /categories : Retrieve a list of all technology categories.

- POST /categories : Create a new category.

- GET /categories/{cat_id} : Retrieve details of a category, replace "{cat_id}" with the name of the category.

- PUT /categories/{cat_id} : Update category informations, replace "{cat_id}" with the name of the category.

- DELETE /categories/{cat_id} : Delete a category, replace "{cat_id}" with the name of the category.

- GET /technologies : Retrieve a list of all technologies.

- POST /technologies : Create a new technology.

- GET /technologies/{tech_id} : Retrieve details of a technology, replace "{tech_id}" with the name of the technology.

- PUT /technologies/{tech_id} : Update technology informations, replace "{tech_id}" with the name of the technology.

- DELETE /technologies/{tech_id} : Delete a technology, replace "{tech_id}" with the name of the technology.

## Example requests

### Example POST/PUT form-data Payload with Postman-like tool for Creating a Category

```
+-------------+----------------------------+
| Key         | Value                      |
+-------------+----------------------------+
| name        | front-development          |
+-------------+----------------------------+
```

### Example POST/PUT form-data Payload with Postman-like tool for Creating a Technology

```
+-------------+-----------------------------------------------------------------+
| Key         | Value                                                           |
+-------------+-----------------------------------------------------------------+
| name        | HTML                                                            |
| ressources  | [{"url":"https://developer.mozilla.org/fr/docs/Glossary/HTML"}] |
| icon        | [html-logo.png] (here a file input)                             |
| category    | front-development                                               |
+-------------+-----------------------------------------------------------------+
```

### Example PUT JSON Payload returned for Getting Categorr or Technology

```json
{
  "name":"the-name",
  "category":"category-of-the-technology",
  "ressources":[{"url":"the.url/of/the/ressource"}, ...],
  "icon_name":"your-icon.png"
}
```


## Testing the API

You can test the API using Postman or other API testing tools by using the URLs mentioned above.