# Valkiry
Projet Valkiry JE

## Deploy

### 1 - Pretiffy code

On Windows:

```bash
./vendor/bin/php-cs-fixer.bat fix ./
```

On Linux:

```bash
php ./vendor/bin/php-csfixer fix ./
```

### 2 - Minimize content

```bash
make deep-clean
```

Or

```bash
rm -rf vendor tmp
```

### 3 - Deploy

*(TODO using rsync)*

```bash
make deploy
```

### 4 - Update dependencies

**On the server**

```bash
make update
```

## Makefile

```bash
make help           # when you are lost
make clean          # clean cache & temp
make deep-clean     # clean cache, temp & vendor
make install        # install composer dependencies
make run-clean      # clean & run
make run            # run php web server
make serve          # run php web server
make update         # update composer dependencies
make vendor         # build vendor dir
```
