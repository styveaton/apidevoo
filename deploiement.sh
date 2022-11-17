
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=Bulk;
php bin/console d:m:migrate --em=Bulk;
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=User;
php bin/console d:m:migrate --em=User;
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=Bulk;
php bin/console d:m:migrate --em=Bulk;
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=Route;
php bin/console d:m:migrate --em=Route;
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=Licence;
php bin/console d:m:migrate --em=Licence;
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=Vitrine;
php bin/console d:m:migrate --em=Vitrine;
rm migrations -r;
mkdir migrations;
php bin/console d:m:diff --em=Pub;
php bin/console d:m:migrate --em=Pub;
rm migrations -r;
mkdir migrations;

php bin/console d:m:diff;
php bin/console d:m:migrate;
rm migrations -r;
mkdir migrations;

