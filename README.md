#Cryptocurrency Faucet

This is a cryptocurrency faucet (bitcoin forks) written in Php by using the Symfony Framework.


### Requirements
* Mysql
* PHP >=7.0
* Composer
* npm
* Webserver (Apache, Nginx, etc.)

## Installation

1. ``` git clone https://github.com/ctrl-Felix/faucet.git && cd faucet ```
2. ``` composer install ```
3. ``` npm install ```
4. ``` nano .env ``` and edit it with your values   
5. ``` php bin/console make:migration ```
6. ``` php bin/console doctrine:migration:migrate ```
7. ``` composer dump-env prod ```
8. ``` yarn encore prod ```
9. Config your webserver and point it to the ../faucet/public folder