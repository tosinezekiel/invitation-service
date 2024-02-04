# PHP-based Invitation System

This is a REST API based invitation system developed in PHP. It allows a user (the Sender) to send an invitation to another user (the Invited). The Sender can cancel a sent invitation, and the Invited can either accept or decline an invitation.

## Features

1. Send an invitation
2. Cancel a sent invitation
3. Accept or decline an invitation

## Technologies Used

- PHP 8
- Symfony Framework 6.2
- MySQL

## Setup

To run this project, you need to do the following:

1. Clone the repository
2. Install dependencies using Composer
3. Configure your .env file for the database connection
4. Run the project using Symfony server
5. Run seeder `php bin/console doctrine:fixtures:load`

# Endpoints

1. POST /api/auth/invites
2. PATCH /api/auth/invites/{id}/cancel
3. PATCH /public/invites/{token}/accept
4. PATCH /public/invites/{token}/decline
5. POST /api/login/ -d {"email": "ex@mail.com", "password": "xxxx"}

## Testing

The project includes functional and/or unit tests written in the PHPUnit framework to demonstrate how the various API endpoints behave in relation to each other. To run the tests, use the following command:

```bash
php bin/phpunit 
