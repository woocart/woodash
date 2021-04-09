VERSION := 1.3.0
PLUGINSLUG := woodash
SRCPATH := $(shell pwd)/src

install: vendor
vendor: src/vendor
	composer install
	composer dump-autoload -o

clover.xml: vendor test

update_version:
	sed -i "s/@##VERSION##@/${VERSION}/" src/$(PLUGINSLUG).php
	sed -i "s/@##VERSION##@/${VERSION}/" src/i18n/$(PLUGINSLUG).pot

remove_version:
	sed -i "s/${VERSION}/@##VERSION##@/" src/$(PLUGINSLUG).php
	sed -i "s/${VERSION}/@##VERSION##@/" src/i18n/$(PLUGINSLUG).pot

unit: test

test: vendor
	bin/phpunit --coverage-html=./reports

src/vendor:
	cd src && composer install
	cd src && composer dump-autoload -o

build: install update_version
	mkdir -p build
	rm -rf src/vendor
	cd src && composer install --no-dev
	cd src && composer dump-autoload -o
	cp -ar $(SRCPATH) $(PLUGINSLUG)
	zip -r $(PLUGINSLUG).zip $(PLUGINSLUG)
	rm -rf $(PLUGINSLUG)
	mv $(PLUGINSLUG).zip build/
	make remove_version

dist: install update_version
	mkdir -p dist
	rm -rf src/vendor
	cd src && composer install --no-dev
	cd src && composer dump-autoload -o
	cp -r $(SRCPATH)/. dist/
	make remove_version

release:
	git stash
	git fetch -p
	git checkout master
	git pull -r
	git tag v$(VERSION)
	git push origin v$(VERSION)
	git pull -r

fmt: install
	bin/phpcbf --standard=WordPress src --ignore=src/vendor,src/assets
	bin/phpcbf --standard=WordPress tests

lint: install
	bin/phpcs --standard=WordPress src --ignore=src/vendor,src/assets
	bin/phpcs --standard=WordPress tests

psr: src/vendor
	composer dump-autoload -o
	cd src && composer dump-autoload -o

i18n: src/vendor
	wp i18n make-pot src src/i18n/$(PLUGINSLUG).pot --slug=kafkai --skip-js --exclude=vendor

cover: vendor
	bin/coverage-check clover.xml 84

clean:
	rm -rf vendor/ src/vendor/
