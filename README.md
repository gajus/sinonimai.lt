## sinonimai.lt

> Sinonimų žodynas yra gyvosios kalbos turtinimo projektas, skatinantis domėtis turimais kalbos turtais, jais naudotis ir juos kurti, įtraukti visuomenę, ypatingai jaunimą, į lietuvių kalbos puoselėjimo, jos vartojimo, aktyvinimo kūrybinę veiklą.

This is a code base of http://sinonimai.lt/ project. The code has not been modified since 2011.

I don't have time to further maintain this project. However, seeing that it has an active user base, I have decided to open-source development of this project.

I have containerized the code base and written manifests that enable [Kubernetes](http://kubernetes.io/) setup. The only non-containerized component of the application is the database – production is using an external (Google Cloud SQL) database.

## Setup

```bash
DATABASE_USER='sinonimai'
DATABASE_PASSWORD=''
DATABASE_HOST='127.0.0.1'
DATABASE_NAME='sinonimai.lt'
FACEBOOK_APP_ID=''
FACEBOOK_APP_SECRET=''

mysql -u${DATABASE_USER} -p${DATABASE_PASSWORD} -h${DATABASE_HOST} ${DATABASE_NAME} < ./sinonimai.lt.sql

kubectl create secret generic sinonimai-lt --from-literal=database_user="${DATABASE_USER}",database_password="${DATABASE_PASSWORD}",database_host="${DATABASE_HOST}",database_name="${DATABASE_NAME}",facebook_app_id="${FACEBOOK_APP_ID}",facebook_app_secret="${FACEBOOK_APP_SECRET}"
kubectl label secret sinonimai-lt app=sinonimai-lt
kubectl create -f ./manifests/app.yaml
```

## Data sanitization

To protect user privacy, all user identifying data has been removed from the public data dump (`./sinonimai.lt.sql`).

The following data sanitization has been performed:

```sql
UPDATE `admins` SET `full_name` = concat('full_name', `id`), `email` = concat('email', `id`), `password` = concat('password', `id`), `rate_1` = 0, `rate_2` = 0;
UPDATE `users` SET `facebook_id` = `id`, `first_name` = concat('first_name', `id`), `last_name` = concat('last_name', `id`), `email` = concat('email', `id`), `gender` = 0;
UPDATE `works` SET `text` = '';
```
