OroIntegrationBundle
====================

Integration bundle provides abstraction for channels, transports and connectors. Those objects are responsible for
integration between application and third party applications or services (e.g. ECommerce stores, ERP systems etc..).
General purpose is to allow developers to create integration bundles and provide basic UI for its configuration.


- [Channel type definition](#channel-type-definition)
- [Transport definition](#transport-definition)
- [Connector definition](#connector-definition)

##Channel type definition

**Channel type** - type of application/service to connect.
**Channel** - and instance of configured channel type with enabled connectors.

Responsibility of channel is to split on groups transport/connectors by third party application type.
To define you own channel type developer should create class that will implement
`Oro\Bundle\IntegrationBundle\Provider\ChannelInterface` and register it as service with `oro_integration.channel` tag
that will contains `type` key, it's should be unique.

####Example:
``` yaml
    acme.demo_integration.provider.prestashop.channel:
        class: %acme.demo_integration.provider.prestashop.channel.class%
        tags:
            - { name: oro_integration.channel, type: presta_shop }
```

##Transport definition

Responsibility of **transport** is communicate connector and channel, it should perform read/write operations to third
party systems.
To define you own transport developer should create class that will implement
`Oro\Bundle\IntegrationBundle\Provider\TransportInterface` and register it as service with `oro_integration.transport`
tag that will contains `type` key, it's should be unique and `channel_type` key that shows for what channel type it
could be used.

####Example:
``` yaml
    acme.demo_integration.provider.db_transport:
        class: %acme.demo_integration.provider.db_transport.class%
        tags:
            - { name: oro_integration.transport, type: db, channel_type: presta_shop }
```

##Connector definition

**Channel connector** is responsible to bring data in and define compatible channel types. Examples: Magento
customers data connector, Magento catalog data connector.

To define you own connector developer should create class that will implement
`Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface` and register it as service with `oro_integration.connector`
tag that will contains `type` key, it's should be unique for the channel and `channel_type` key that shows for what
channel type it could be used.

####Example:
``` yaml
    acme.demo_integration.provider.prestashop_product.connector:
        class: %acme.demo_integration.provider.prestashop_product.connector.class%
        tags:
            - { name: oro_integration.connector, type: product, channel_type: presta_shop }
```

##Export job definition
This will export your data to your store based on channel definition.

**oro_integration.reader.entity.by_id** - service reads from entity by ID.

####Example:
``` yaml
    #batch_job.yml
    example_export:
        title: "Entity export"
        type:  export
        steps:
            export:
                title: export
                class: Oro\Bundle\BatchBundle\Step\ItemStep
                services:
                    reader:    oro_integration.reader.entity.by_id
                    processor: YOUR_PROCESSOR                       # service which processing each record. Could prepare changeset for writer
                    writer:    YOUR_REVERSE_WRITER                  # service that are responsible for data push to remote instance
                parameters: ~
```

Processor and writer could be initialized in your bundle in service.yaml
####Example:
``` yaml
    YOUR_PROCESSOR:
        class: %YOUR_PROCESSOR.class%
    YOUR_REVERSE_WRITER:
        class: %YOUR_REVERSE_WRITER.class%
```

Where `YOUR_PROCESSOR.class` - should implements Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface
and `YOUR_REVERSE_WRITER.class` - should implements Oro\Bundle\ImportExportBundle\Processor\WriterInterface

Implementation of those classes are very platform specific
