# FOLDED Layer RESTful API
The Layer API services the Layer backend and Layer Applications, in a scalable and Task Queue balanced matter.
The Layer API is built on top of Laravel 5.

The **Folded** flavour is specifically for POC and demo. It provides all functionality of the layered stack, in 1 demonstration package.

### OAauth2
Layer implements the bearer protocol from the OAuth 2 standard. 
Each app connecting to the API therefore requires an App ID, each user an unique authentication token.

### Validation
The API only handles first-line validation of the endpoint parameters, to guarantee a swift processing (routing) of the requests.

### Laravel PHP Framework
Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as authentication, routing, sessions, queueing, and caching.

Laravel is accessible, yet powerful, providing powerful tools needed for large, robust applications. A superb inversion of control container, expressive migration system, and tightly integrated unit testing support give you the tools you need to build any application with which you are tasked.

#### Official Documentation
Documentation for the framework can be found on the [Laravel website](http://laravel.com/docs).

## Install API
1. Download the git package
1. Run composer install
1. Copy .env.example as .env and edit to your liking
1. Point your vhost/nginx conf file to the public folder
1. Profit.

## License
Layer uses Open Source software, and is MIT.
Use at your own risk.