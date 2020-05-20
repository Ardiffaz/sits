# Stars in the Stash
A not-so-simple tool to manage Steam keys, prepare them for creating giveaways on Steamgifts and also check group ownership/wishlist (Profile Features Limited games are being tracked too!)

##How to install
Add your params to .env.local file:
```bash
DATABASE_NAME=
DATABASE_USER=
DATABASE_PASSWORD=
DATABASE_ROOT_PASSWORD=

STEAM_API_KEY=
DATA_WORLD_API_KEY=
```
DataWorld Api Key is not required.

Then
```bash
docker-compose build
```

##How to run
```bash
./up
```


####Make yourself admin
```bash
docker-compose exec backend console user:role:add profileName ADMIN
```

##--
Everything else is done via web-interface.