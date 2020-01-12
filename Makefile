VERSION := 1.2.0
PLUGINSLUG := woodash
SRCPATH := $(shell pwd)/src

bin/linux/amd64/github-release:
	wget https://github.com/aktau/github-release/releases/download/v0.7.2/linux-amd64-github-release.tar.bz2
	tar -xvf linux-amd64-github-release.tar.bz2
	chmod +x bin/linux/amd64/github-release
	rm linux-amd64-github-release.tar.bz2

ensure: vendor
vendor: src/vendor
	composer install --dev
	composer dump-autoload -a

clover.xml: vendor test

unit: test

test: vendor
	bin/phpunit --coverage-html=./reports

src/vendor:
	cd src && composer install
	cd src && composer dump-autoload -a

build: ensure
	sed -i "s/@##VERSION##@/${VERSION}/" src/woodash.php
	sed -i "s/@##VERSION##@/${VERSION}/" src/i18n/woodash.pot
	mkdir -p build
	rm -rf src/vendor
	cd src && composer install --no-dev
	cd src && composer dump-autoload -a
	cp -ar $(SRCPATH) $(PLUGINSLUG)
	zip -r $(PLUGINSLUG).zip $(PLUGINSLUG)
	rm -rf $(PLUGINSLUG)
	mv $(PLUGINSLUG).zip build/
	sed -i "s/${VERSION}/@##VERSION##@/" src/woodash.php
	sed -i "s/${VERSION}/@##VERSION##@/" src/i18n/woodash.pot

dist: ensure
	sed -i "s/@##VERSION##@/${VERSION}/" src/woodash.php
	sed -i "s/@##VERSION##@/${VERSION}/" src/i18n/woodash.pot
	mkdir -p dist
	rm -rf src/vendor
	cd src && composer install --no-dev
	cd src && composer dump-autoload -a
	cp -r $(SRCPATH)/. dist/
	sed -i "s/${VERSION}/@##VERSION##@/" src/woodash.php
	sed -i "s/${VERSION}/@##VERSION##@/" src/i18n/woodash.pot

publish: build bin/linux/amd64/github-release
	bin/linux/amd64/github-release upload \
		--user woocart \
		--repo $(PLUGINSLUG) \
		--tag "v$(VERSION)" \
		--name $(PLUGINSLUG)-$(VERSION).zip \
		--file build/$(PLUGINSLUG).zip

release:
	git stash
	git fetch -p
	git checkout master
	git pull -r
	git tag v$(VERSION)
	git push origin v$(VERSION)
	git pull -r
	@echo "Go to the https://github.com/woocart/woodash/releases/new?tag=v$(VERSION) and publish the release in order to build the package for distribution!"

fmt: ensure
	bin/phpcbf --standard=WordPress src --ignore=src/vendor
	bin/phpcbf --standard=WordPress tests --ignore=vendor

lint: ensure
	bin/phpcs --standard=WordPress src --ignore=src/vendor
	bin/phpcs --standard=WordPress tests --ignore=vendor

psr: src/vendor
	composer dump-autoload -a
	cd src && composer dump-autoload -a

i18n: src/vendor
	wp i18n make-pot src src/i18n/woodash.pot

cover: vendor
	bin/coverage-check clover.xml 100

clean:
	rm -rf vendor/ bin src/vendor/
