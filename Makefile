build/clean:
	rm -rf target

build/dir:
	mkdir -p target/spectrum-intelligent-moderation
	mkdir -p target/spectrum-intelligent-moderation/_inc

build/source: build/dir
	cp -r src/php/* target/spectrum-intelligent-moderation
	cp src/readme.txt target/spectrum-intelligent-moderation
	cp -r src/js/* target/spectrum-intelligent-moderation/_inc
	sass src/sass/index.scss target/spectrum-intelligent-moderation/_inc/automod.css --style compressed
	sass src/sass/vendor/chartist/chartist.scss target/spectrum-intelligent-moderation/_inc/chartist.css --style compressed

sync: install
	rsync -azP target/spectrum-intelligent-moderation ec2-user@54.193.117.231:/var/www/html/wordpress/wp-content/plugins

install: build/clean build/source

archive: install
	zip -r target/spectrum-intelligent-moderation.zip target/spectrum-intelligent-moderation

.PHONY: install sync