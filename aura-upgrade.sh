#!bin/bash

set -e # exit

echo "preparing backup directory..." 
# create backup directory. fails if already exists. you need to remove it first.
mkdir /var/www/html~

echo "removing current cache to preserve space..." 
# remove current cache
rm -r /var/www/html/var/cache

echo "moving instance to backup (html~)" 
# move all files from html to html~
shopt -s dotglob # include hidden files as well (.e.g .env)
mv /var/www/html/* /var/www/html~/

echo "copying new files..."
# copy new source files
cp -R /usr/src/mautic/. /var/www/html

echo "moving back config and other instance-specific resources..."
# copy instance-spcecific files from html~ to the new instance
cp -R /var/www/html~/media/. /var/www/html/media/
cp -R /var/www/html~/themes/. /var/www/html/themes/
cp /var/www/html~/app/config/local*.php /var/www/html/app/config

echo "setting ownership permissions"
# set ownership permission on all directories
chown -R www-data:www-data /var/www/html

echo "removing cache on the new instance"
# remove cache (might have been corrupted during the execution of this script)
/var/www/html/bin/console cache:clear

echo "All done. check mautic, also check configuration page."