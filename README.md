# S1AR Application Installation Guide

This guide will walk you through the steps to install the SIAR application.

## Prerequisites

Before you begin, make sure you have the following software installed:

- Git
- Composer

## Installation Steps

1. Clone this GitHub repository:

    ```sh
    git clone https://github.com/naufalamr17/SIAR.git
    ```

2. Install Composer:

    ```sh
    composer install
    ```

3. Generate the .env file and application key:

    ```sh
    cp .env.example .env
    php artisan key:generate
    ```

4. Create the database 'siar-mysql' in mySQL and Migrate the database (before migrate db, change sqlsrv to local sqlsrv in env file. after that, you can change sqlsrv to AIIA-...):

    ```sh
    php artisan migrate
    ```

5. Run the web application:

    ```sh
    php artisan serve
    ```

## Docs

Table Name:

1. mySQL (users)
2. SQL Server (attdly1, attdly2, pnmempl, atttrn2, atmrscd)
3. Sikola
