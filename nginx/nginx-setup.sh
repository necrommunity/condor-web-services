#!/bin/bash
adduser rtmp 

echo "Installing dependencies and packages"
apt-get install build-essential libpcre3 libpcre3-dev libssl-dev git sudo checkinstall policykit-1 certbot incron apache2-utils -y
echo "Creating and swapping to working directory"
mkdir working && cd working

echo "Downlading Nginx and the RTMP module"
wget http://nginx.org/download/nginx-1.12.1.tar.gz && tar -zxvf nginx-1.12.1.tar.gz
git clone -b dev https://github.com/sergey-dryabzhinsky/nginx-rtmp-module.git

echo "Swapping to Nginx directory"
cd nginx-1.12.1
./configure --with-http_ssl_module --add-module=../nginx-rtmp-module --pid-path=/usr/local/nginx/sbin/nginx.pid --without-http_gzip_module

echo "Nginx configured, making and installing"
make && checkinstall -y

echo "Download and move nginx.service file"
wget https://raw.githubusercontent.com/necrommunity/condor-web-services/master/nginx/nginx.service && mv nginx.service /etc/systemd/system
if [-d /usr/local/nginx/logs]
then
	cd /usr/local/nginx/logs && touch error.log access.log
	echo "Logs folder exists, creating log files"
else
	echo "Logs folder does not exist, creating..."
	mkdir /usr/local/nginx/logs
	cd /usr/local/nginx/logs && touch error.log access.log
fi
chmod 777 /etc/systemd/system/nginx.service
echo "Update systemctl"
systemctl daemon-reload

echo "Generating nginx PID file"
/usr/local/nginx/sbin/nginx && /usr/local/nginx/sbin/nginx -s stop

systemctl start nginx.service && systemctl enable nginx.service

read -p 'Enter location code (au/eu/use/usw): ' host
echo "Downloading index and CSS file"
cd /usr/local/nginx/html && rm index.html 
wget https://raw.githubusercontent.com/necrommunity/condor-web-services/master/nginx/index.css && wget "https://raw.githubusercontent.com/necrommunity/condor-web-services/master/nginx/ingest-index.html" -O index.html && wget https://github.com/necrommunity/condor-web-services/raw/master/nginx/purplebg.jpg && wget https://github.com/necrommunity/condor-web-services/raw/master/nginx/favicon.ico
sed -i "s/XX/${host^^}/g" index.html

certbot certonly --webroot -w /usr/local/nginx/html -d $host.condor.host -d $host.condorleague.tv
chmod -R 500 /etc/letsencrypt/live/$host.condor.host

echo "Stopping Nginx"
systemctl stop nginx.service

echo "Copying configs"
mkdir /mnt/rtmp
scp rtmp@condor.host:"/mnt/rtmp/$host-rtmp.conf /mnt/rtmp/ingest-nginx.conf /mnt/rtmp/.htpasswd /mnt/rtmp/stat.xsl" /usr/local/nginx/conf
cd /usr/local/nginx/conf && rm nginx.conf && mv ingest-nginx.conf nginx.conf 
sed -i "s/XX/$host/g" nginx.conf

echo "Starting Nginx"
systemctl start nginx

#crontab -e
#* 12 * * * certbot renew

echo "All done!"