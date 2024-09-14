# Json Snitch Proxy

It's a really simple api based proxy to bypass sanctions or anything else like that!

## How to deploy?

### Docker

This project is shipped with `Dockerfile`! Just build it with below command:

```shell
> docker build -t snitch-proxy .
```

### CLI

First you should install `php` with `composer`. you need at least **`php 8`** and above. Then run below commands to
start project:

```shell
> composer install
> php server.php
```

## How to use?

It works in any language you think of. Just replace the url and put it in `X-Proxy-To` header.
for example in curl:

```shell
curl --location 'http://127.0.0.1:9898' \
--header 'X-Proxy-To: https://jsonplaceholder.typicode.com/todos/200' \
--header 'X-Proxy-Config: {"timeout": 1}'
```

additional configurations should go into `X-Proxy-Config`. config values are:

```json
{
  "timeout": 10,
  "followRedirects": true
}
```

1. `timeout`: the maximum time to wait for response from destination address
2. `followRedirects`: enable or disable follow redirects