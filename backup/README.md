# Valkiry
Projet Valkiry JE

## TODO

### Major

- [ ] Account
    - [ ] **?** CompanyManager: profile
        - [ ] Table in database
        - [ ] Profile management
    - [ ] CompanyManager: Account information
    - [ ] Employee: Account information
- [x] CompanyManager: License information
- [x] Questions: table in database
- [x] Questions: fetch for CompanyManger
- [x] Questions: random question fetch for Employee
- [x] Questions: employee form
- [x] Questions: add method
- [x] Questions: answer method

### Minor

- [ ] Questions: duplicate verification algorithm
- [ ] Global: csrf

## Makefile

```bash
make help           # when you are lost
make clean          # clean cache & temp
make deep-clean     # clean cache, temp & vendor
make fix            # (broken) run php-cs-fixer
# use: .\vendor\bin\php-cs-fixer(.bat) fix ./
make install        # install composer dependencies
make run-clean      # clean & run
make run            # run php web server
make serve          # run php web server
make update         # update composer dependencies
make vendor         # build vendor dir
```
