build/clean:
	rm -rf target

build/dir:
	mkdir -p target/automod
	mkdir -p target/automod/_inc

build/source: build/dir
	cp -r src/php/* target/automod
	sass src/sass/index.scss target/automod/_inc/automod.css --style compressed
	sass src/sass/vendor/chartist/chartist.scss target/automod/_inc/chartist.css --style compressed

sync: install
	rsync -azP target/automod ec2-user@54.193.117.231:/var/www/html/wordpress/wp-content/plugins

install: build/clean build/source

.PHONY: install sync