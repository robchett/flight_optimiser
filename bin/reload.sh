./bin/clean.sh && ./bin/phpize && ./configure --with-php-config=./bin/php-config && make && make install && yes | cp modules/geometry.so `./php-config --extension-dir` && ./bin/php ./test/test.php && service php-fpm reload
