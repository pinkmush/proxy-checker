# Proxy Checker by [Georgiy Baranov](https://www.upwork.com/freelancers/~01e16b15fde86cc973)

##Install
* cp laradock/env-example laradock/.env
* cp laradock/nginx/sites/default.conf.example laradock/nginx/sites/default.conf
* Edit configs above
* docker-compose up -d nginx workspace



## Additional info
* if domain != proxy.georgiy.pro, then change app/dist/check.php