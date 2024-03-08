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

4. Create the database 'siar-mysql' in mySQL and import `siar-mysql.sql`

5. For excel, run this command:

    ```sh
    composer require maatwebsite/excel
    ```

6. Run the web application:

    ```sh
    php artisan serve
    ```

## Docs

Table Name:

1. attdly1
2. attdly2
3. pnmempl
4. pnhhira
5. ssmhira
6. attrn2
7. atmrscd

Note: The table names mentioned above are the names of tables in SQL Server from which I retrieve the data. Therefore, there is a difference in table names compared to MySQL. In this program, the code that controls the data copying is located in the directory `app\Console\Commands\CopyDataCommand.php`. You can run it by visiting https://your-link/scheduler or by using the command `php artisan data:copy`. Emmm, sorry for the complicated query hehe.