valgrind --tool=memcheck --num-callers=30 --log-file=php.log --leak-check=full --track-origins=yes -v /usr/bin/php test.php
chmod 777 php.log