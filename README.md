## PLT Tech Test

Tech test built with the laravel framework.## PLT Tech Test

## Installation

- Clone or download the GIT repository.
- Create an SQLite database by running the command `touch storage/database.sqlite` on the command line.
- Create your .env file as per laravel docs.
- Run `composer install`
- Run the `php artisan migrate` command to setup the database structure.
 
## Loading CSV Files

To load a CSV file run this command:
`php artisan product:load storage/products-test.csv`

If you would like to show errors relating to each row append --errors after the file path.

## License

This code is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
