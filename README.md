api_project
===========

A Symfony project created on January 20, 2017, 10:25 pm.

## ABOUT

Simple API for work with filesystem

## Functions

- Create file from request;
- Replace existing file on the server by file from request;
- Get some file from the server;
- Get file metadata;
- Get list of files from the server;

## Installation and Setup

- Clone this repository;
- Install all dependencies using Composer;
- Set target folder in <i> app\config\parameters.yml </i> by specifying
 the value of variable <b> folder_with_files </b>
 default value is "upload_files";
- Set maximum of files with similar names in folder 
  in <i> app\config\parameters.yml </i> by specifying the value
  of variable <b> max_same_names </b>
  default value is 2;
  
 <pre>
     #  app\config\parameters.yml
     ...
     folder_with_files: uploaded_files
     max_same_names: 2
 </pre>
  

## Documentation
For more information go to <b> /doc </b>

Requirement for _format: <b> json | xml | html </b> <br>

Requests:
- <pre> [GET] /api/.{_format} </pre> returns simple API response <br>
  available status codes: 200 (success) <br> <br>
  <b> Response sample: </b>
  <pre>
        {
          "code": 200,
          "parameters": {
            "greeting": "Hello! Seems to API is works fine"
          }
        }
  <pre>
  
- <pre> [GET] /api/list.{_format} </pre> returns list of directories and files <br>
  available status codes: 200 (success), 404 (folder not found) <br> <br>
  <b> Response sample: </b>
  <pre>
        {
          "code": 200,
          "parameters": {
            "directories": {
              "directory": [
                {
                  "path": "uploaded_files",
                  "files": [
                    "(1)cavy.jpg",
                    "cat.jpg",
                    "cavy.jpg",
                    "dog.jpg"
                  ]
                }
              ]
            }
          }
        }
  </pre>
  
- <pre> [GET] /api/file/{filename}/metadata.{_format} </pre> returns metadata for filename <br>
    available status codes: 200 (success), 404 (file doesn't exists) <br>
    requires: filename of type string  <br> <br>
    <b> Response sample: </b>
    <pre>
            {
              "code": 200,
              "parameters": {
                "metadata": {
                  "filename": "cat.jpg",
                  "bytes": 6708,
                  "modified": "January 21 2017 18:30:55.",
                  "mimetype": "image/jpeg"
                }
              }
            }
    </pre>
- <pre> [GET] /api/file/{filename}/download.{_format} </pre> returns attachment <br>
    available status codes: 200 (success), 404 (file doesn't exists) <br>
    requires: filename of type string  <br> <br>

- <pre> [POST] /api/upload.{_format} </pre> upload file and returns metadata <br>
    available status codes: 201 (success), 400 (no file attached), 409 (the server has more than maximum of files with similar names)  <br>
    requires: attachment file  <br> <br>
    <b> Response sample: </b>
    <pre>
            {
              "code": 201,
              "parameters": {
                "metadata": {
                  "filename": "dog.jpg",
                  "bytes": 74742,
                  "modified": "January 22 2017 13:23:04.",
                  "mimetype": "image/jpeg"
                }
              }
            }
    </pre>
- <pre> [POST] /api/replace.{_format} </pre> upload or upload and replace file and returns metadata <br>
    available status codes: 201 (success), 400 (no file attached) <br>
    requires: attachment file, replace_it of type string  <br> <br>
    <b> Response sample: </b>
    <pre>
            {
              "code": 201,
              "parameters": {
                "metadata": {
                  "filename": "dog.jpg",
                  "bytes": 74742,
                  "modified": "January 22 2017 13:23:04.",
                  "mimetype": "image/jpeg"
                }
              }
            }
    </pre>
    
## Testing
For testing this API you can:
- import the file named "Symfony Files API.postman_collection.json"
 to Postman;
- use shared Postman collection by link
 https://getpostman.com/collections/4964ceb68e1f59249de9