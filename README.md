![Project screenshot](https://github.com/thiagomrvieira/price-hub/blob/main/image.png)

# Usecase

You have a user logged into an ecommerce store. Only prices for their account should be
shown to the user. The storefront makes a request to the pricing API (simulated by a JSON feed) passing
the account and product id. If there is no match, you must find the match in the Database. If there is no
match in the Database, then that product is not available for that user and no price should be returned.

## Files

- import.csv - This file has references to products, accounts and users
- live_prices.json - this file simulates an external service where you can read dynamic live prices in
real time. A price is always related to a product, and it could optionally be related to an account.

## Implementations

- Write a script/function to import the prices provided in import.csv - this file has columns with
references to each entity - into the prices table
- Read the JSON price file in real-time and do not load it into the database. This represents a “live
pricing” feed.
- Develop an output for the function that gets the price

## Stack

- PHP
- Laravel
- MySQL Stored procedures
- VueJS
- Tailwind CSS


## Setup and Configuration

1. Clone the project repository.
2. Setup the app using:
   ```shell
   php artisan app:install
   ```
   It will create the environment file and install the dependencies.
3. Setup the database running:
   ```shell
   php artisan migrate:fresh --seed
   ```
   It will create the database, run the migrations and seed the database.
4. Build and run the Vue.js app:
    ```shell
    # Install the required dependencies
    npm install

    # Build the Vue.js app for production
    npm run build

    # Start the development server
    npm run dev
    ```
5. Start the development server:
    ```shell
    php artisan serve
    ```
4. You can now use the price hub system by calling the route http://127.0.0.1:8000/.


