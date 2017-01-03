## sinonimai.lt

> Sinonimų žodynas yra gyvosios kalbos turtinimo projektas, skatinantis domėtis turimais kalbos turtais, jais naudotis ir juos kurti, įtraukti visuomenę, ypatingai jaunimą, į lietuvių kalbos puoselėjimo, jos vartojimo, aktyvinimo kūrybinę veiklą.

This is a code base of http://sinonimai.lt/ project. The code has not been modified since 2011.

I don't have time to further maintain this project. However, seeing that it has an active user base, I have decided to open-source development of this project.

I have containerized the code base and written [Kubernetes](http://kubernetes.io/) manifest [Helm](https://github.com/kubernetes/helm) templates.

## Prerequisites

* Kubernetes ^v1.15
* Helm ^v2

## Setup

```bash
git clone git@github.com:gajus/sinonimai.lt.git
cd ./sinonimai.lt
helm install ./chart
```

Use `hostPath` configuration to mount host `./workdir` for development purposes, e.g.

```bash
helm install --set hostPath="${PWD}/workdir" ./chart
```

## Data sanitization

To protect user privacy, all user identifying data has been removed from the public data dump (`./sinonimai.lt.sql`).

The following data sanitization has been performed:

```sql
UPDATE `admins` SET `full_name` = concat('full_name', `id`), `email` = concat('email', `id`), `password` = concat('password', `id`), `rate_1` = 0, `rate_2` = 0;
UPDATE `users` SET `facebook_id` = `id`, `first_name` = concat('first_name', `id`), `last_name` = concat('last_name', `id`), `email` = concat('email', `id`), `gender` = 0;
UPDATE `works` SET `text` = '';
```
