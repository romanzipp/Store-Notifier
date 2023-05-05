<?php

namespace StoreNotifier\Providers;

use GuzzleHttp\Client;
use StoreNotifier\Providers\Data\ModelData\ProductData;
use StoreNotifier\Providers\Data\ModelData\VariantData;

abstract class AbstractKrasserStoffProvider extends AbstractProvider
{
    abstract protected static function getCategory(): string;

    abstract protected static function getShop(): string;

    final public static function getUrl(): string
    {
        return 'https://krasserstoff.com';
    }

    private static function requestGraphQl(Client $client, $query): object
    {
        $url = 'https://krasserstoff.com/api/v2/shops/' . static::getShop() . '/graphql?' . http_build_query([
            'locale' => 'de',
        ]);

        return json_decode(
            $client
                ->post($url, ['json' => ['query' => $query]])
                ->getBody()
                ->getContents()
        );
    }

    final public function handle(): void
    {
        /** @var \StoreNotifier\Providers\Data\ModelData\ProductData[] $products */
        $products = [];

        $client = self::newHttpClient();

        $categoriesData = self::requestGraphQl($client, <<<EOF
query {
  category(id: "{$this->getCategory()}") {
    slug
    id
    description
    name
    type
    subtitle
    subcategories {
      nodes {
        id
        slug
      }
    }
    merchProducts {
      nodes {
        id
        slug
      }
    }
  }
}
EOF);
        if (isset($categoriesData->errors)) {
            $this->logger->log(sprintf('Error in %s: %s', $this->getCategory(), implode(', ', array_map(fn ($errors) => $errors->message, $categoriesData->errors))));

            return;
        }

        $categories = $categoriesData?->data?->category?->subcategories?->nodes;
        $categories[] = (object) [
            'slug' => $this->getCategory(),
        ];

        foreach ($categories as $category) {
            $productsData = self::requestGraphQl($client, <<<EOF
query {
  category (id: "{$category->slug}") {
    merchProducts {
      nodes {
        id
        slug
        available
        artist
        description
        lowStock
        name
        price
        productType
        productVariants {
          nodes {
            id
            order
            price
            available
            description
          }
        }
        images {
          nodes {
            thumbnailAt740
          }
        }
      }
    }
  }
}
EOF);

            $categoryProducts = $productsData->data?->category?->merchProducts?->nodes;

            foreach ($categoryProducts as $categoryProduct) {
                $imageUrl = null;

                foreach ($categoryProduct->images->nodes as $imageNode) {
                    $imageUrl = $imageNode->thumbnailAt740;
                }

                if (empty($categoryProduct->productVariants->nodes)) {
                    continue;
                }

                $products[] = $product = new ProductData([
                    'store_product_id' => (string) $categoryProduct->slug,
                    'title' => "{$categoryProduct->name} {$categoryProduct->productType}",
                    'url' => "https://krasserstoff.com/products/{$categoryProduct->slug}",
                    'image_url' => $imageUrl,
                    'variants' => array_map(fn (object $variantDetails) => new VariantData([
                        'store_variant_id' => (string) $variantDetails->id,
                        'title' => $variantDetails->description ?? 'Default',
                        'price' => (int) ($variantDetails->price * 100),
                        'available' => $variantDetails->available,
                    ]), $categoryProduct->productVariants->nodes),
                ]);

                $this->logger->logProduct($product);
            }

            $this->storeProducts($products);
        }
    }
}
