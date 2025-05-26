# Laravel Chat Demo

This is a simple chat application built with Laravel and Vue.js. The application allows users to send messages to each other in real-time.

## Installation

1. Clone the repository
2. Run `composer install`
3. Run `npm install`
4. Run `php artisan migrate`
5. Run `php artisan db:seed`
6. Run `npm run dev`
7. Run `php artisan ser`
8. Open the application in your web browser at [http://localhost:8000](http://localhost:8000)

## Features

* Real-time messaging
* User authentication
* Emoji support
* Message editing
* Message deletion
* User presence indicators

## Screenshots

![Screenshot of the chat application](/screenshot.jpg)

## How it works

The application uses Laravel's broadcast functionality to send messages to connected clients. When a user sends a message, the application broadcasts the message to all connected clients. The clients then receive the message and update their local message lists.

The application also uses Laravel's presence functionality to track which users are online. When a user connects to the application, the application joins the user to a presence channel. When a user disconnects from the application, the application leaves the presence channel.

The application uses Vue.js to render the user interface. The application uses the `laravel-vue-pagination` package to handle pagination.

## License

This application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
