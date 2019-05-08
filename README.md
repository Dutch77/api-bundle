asyf_api:
    default_normalizer: Asyf\ApiBundle\Service\Normalizer\BasicNormalizer
    entities:
        DateTime:
            normalizer: Asyf\ApiBundle\Service\Normalizer\DateTimeNormalizer
            options:
                format: 'Y-m-d H:i:s'

        Asyf\SearchBundle\Results\Result:
            fields:
                object:
                score:
                boostedScore:
                wordsToHighlight:

        App\Entity\Example\Category:
            fields:
                title:
                    expose: true
                slug:
                    expose: false
                items:
                    orderBy:
                        title:
                            priority: 1
                            direction: DESC
                    limit: 2
                    expose: true


        App\Entity\Example\Item:
            fields:
                title:
                    expose: true
                address:
                    expose: true
                createdAt:
                    expose: true

        App\Entity\Location\Address:
            fields:
                createdAt:
                    options:
                        format: Y
                streetName:
                streetNumber:
                city:
                latitude:
                longitude:

        App\Entity\Restaurant\Restaurant:
            fields:
                title:
                    expose: true
                address:
                    expose: true
