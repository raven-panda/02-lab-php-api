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

## Errors/Validation output

Errors and validation response messages are managed with codes I defnined myself to make the error management easier if someone wants to use it. They are shown as JSON object, with error codes as **key** and the string message as **value**.

### Error codes and messages

#### Form invalidity errors/warnings

- `100 : Syntax Error: Fields are missing or incorrect.` This message shows that you put bad fields or that some are missing.

- `101 : Syntax Error: Special characters aren't allowed.` This message shows that the name of your category/technology is not in the right format.

#### PDO MySQL errors/warnings

- `200 : Server Error: Cannot access to the requested ressource.` This message shows that there is a problem with the database connection/query. The error is not specified in order to avoid showing the structure of the database to an external person who might be a potential hacker. If you want to see what's the error, just change `$RES->errorMessage(200)` to `$err->getMessage()`, this will set the PDO error message as response.

- `201 : Server Warning: No changes.` This message shows that the request doesn't changed anything in the database.

- `202 : Server Error: Already exists.` This message shows that the category/technology already exists.

- `203 : Server Error: One of the categories doesn't exist or you put wrong categories.` This message shows that one of your category doesn't match any of the database's.

- `210 : Server Warning: No entries.` This message shows that no entries were found.

#### API request errors/warnings

- `400 : Request Error: Path or method used may be incorrect. ...` This message shows that you are trying to access an URL that doesn't exist, or you are using the wrong method.

### Validation messages

- `1 : Server: Added Successfully.` This message shows that you successfully created the technology/category.

- `2 : Server: Edited Successfully.` This message show that you successfully edited the technology/category.

## Testing the API

You can test the API using Postman or other API testing tools by using the URLs mentioned above.