# Cryptocurrency Faucet

This is a cryptocurrency faucet (bitcoin forks) written in Php.

### Requirements
 * Mysql
 * PHP >=7.0
 * Composer
 * npm
 * Webserver (Apache, Nginx, etc.)

## Installation
Work in progress

 1. ``` git clone https://github.com/ctrl-Felix/faucet.git && cd faucet ```
 2. ``` composer install ```
 3. ``` npm install ```
 4. ``` nano .env ``` enter the created mysql user, password and database
 5. ``` nano config/faucet.yaml ``` enter your config
 6. ``` php bin/console make:migration ```
 7. ``` php bin/console doctrine:migration:migrate ```
 8. ``` composer dump-env prod ```
 9. ``` yarn encore prod ```
 10. Config your webserver and point it to the ../faucet/public folder