#!/bin/bash
version="1.0.0"
base_dir="tphpsql-$version"
source_dir="$base_dir/tphpsql-source-$version"
debian_dir="$base_dir/tphpsql-debian-$version"
backup="$base_dir.zip"
backup_dir='/home/*/Bureau/'
clear


php -d phar.readonly=0 build-tphpsql-phar.php
cp tphpsql.phar $base_dir
chmod 755 tphpsql.phar 2>/dev/null && mv tphpsql.phar $debian_dir/usr/bin/tphpsql

if test `id -u` = 0;then
	dpkg-deb --build $debian_dir && mv $base_dir/tphpsql-debian-$version.deb $base_dir/tphpsql-$version.deb
	zip -u $backup -r .

	mv $backup $backup_dir && chmod 777 ${backup_dir}$backup

	dpkg -i $base_dir/$base_dir.deb
fi
