# Store Notifier

Scans all products in a specific store and sends notifications on new products and/or product variants (sizes).

## Supported Stores

- [Billie Eilish Store (DE)](https://www.billieeilishstore.de/) - _Shopify_
- [Billie Eilish Store (UK)](https://shopuk.billieeilish.com) - _Shopify_
- [Billie Eilish Store (US)](https://store.billieeilish.com) - _Shopify_
- [Bring Me The Horizon](https://www.horizonsupply.co/) - _Shopify_
- [Finneas](https://www.finneasofficial.com/)
- [Kraftklub](https://krasserstoff.com/kraftklub-kargo) - _Krasserstoff_
- [Kummer](https://krasserstoff.com/artists/kummer) - _Krasserstoff_
- [MPB](https://mpb.com)
- [Nike](https://www.nike.com)
- [Phoebe Bridgers (UK)](https://phoebe-bridgers-uk.myshopify.com) - _Shopify_
- [Phoebe Bridgers (US)](https://store.phoebefuckingbridgers.com) - _Shopify_
- [girl in red (US)](https://shopgirlinred.com/) - _Shopify_
- [Ready2Drink](https://www.ready2drink.de/collections/knabe-malz/products/knabe-malz.json) - _Shopify_

## Execute

```shell
php app.php

php app.php --filter=girl

php app.php --filter=girl --dry

php app.php --preset=prio
php app.php --preset=lame
```

## Docker

### Build

```shell
docker build -t store-notifier .
```

```shell
docker run \
  --name store-notifier \
  -v "$(pwd)/.env:/app/.env" \
  -v "$(pwd)/database/db.sqlite:/app/database/db.sqlite" \
  store-notifier
```

### Push

```shell
aws ecr get-login-password --region <aws_region> --profile private | docker login --username AWS --password-stdin <aws_id>.dkr.ecr.<aws_region>.amazonaws.com
```

```shell
docker build -t store-notifier .
```

```shell
docker tag store-notifier:latest <aws_id>.dkr.ecr.<aws_region>.amazonaws.com/store-notifier/cli:latest
```

```shell
docker push <aws_id>.dkr.ecr.<aws_region>.amazonaws.com/store-notifier/cli:latest
```

## Authors

- [Roman Zipp](https://ich.wtf)
