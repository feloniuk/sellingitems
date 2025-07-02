<?php declare(strict_types=1);

namespace SellingItems;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SellingItems extends Plugin
{
    private $groups = [
        'selling_items_json_export'
    ];

    private $fields = [
        [
            'name' => 'selling_items_json_export',
            'global' => true,
            'config' => [
                'label' => [
                    'en-GB' => 'Selling Items JSON Export'
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'sales_channel'
                ]
            ],
            'customFields' => [
                [
                    'name' => 'selling_items_json_data',
                    'type' => CustomFieldTypes::HTML,
                    'config' => [
                        'label' => [
                            'en-GB' => 'All Items JSON Data'
                        ],
                        'helpText' => [
                            'en-GB' => 'This field contains JSON data of all selling items. It is automatically generated.'
                        ],
                        'componentName' => 'sw-code-editor',
                        'customFieldType' => 'textEditor',
                        'customFieldPosition' => 0,
                    ]
                ],
                [
                    'name' => 'selling_items_auto_update',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Auto-update JSON on item changes'
                        ],
                        'type' => 'checkbox',
                        'customFieldType' => 'checkbox',
                        'componentName' => 'sw-switch-field',
                        'customFieldPosition' => 1,
                    ]
                ],
                [
                    'name' => 'selling_items_last_updated',
                    'type' => CustomFieldTypes::DATETIME,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Last Updated'
                        ],
                        'type' => 'date',
                        'dateType' => 'datetime',
                        'customFieldType' => 'date',
                        'customFieldPosition' => 2,
                    ]
                ]
            ]
        ]
    ];

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = \rtrim($this->getPath(), '/') . '/Resources/config';

        $configLoader->load($confDir . '/{packages}/*.yaml', 'glob');
    }

    public function install(InstallContext $context): void
    {
        parent::install($context);
        $this->addFields($context);
    }

    public function activate(ActivateContext $context): void
    {
        $this->addFields($context);
        $this->updateJsonData($context);
        parent::activate($context);
    }

    public function update(UpdateContext $context): void
    {
        $this->removeFields($context);
        $this->addFields($context);
        $this->updateJsonData($context);
        parent::update($context);
    }

    public function deactivate(DeactivateContext $context): void
    {
        $this->removeFields($context);
        parent::deactivate($context);
    }

    public function uninstall(UninstallContext $context): void
    {
        $this->removeFields($context);
        parent::uninstall($context);
    }

    private function removeFields($context): void
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = (new Criteria())->addFilter(new EqualsAnyFilter('name', $this->groups));
        $results = $customFieldSetRepository->search($criteria, $context->getContext())->getEntities();

        $ids = [];

        foreach ($results as $result) {
            $id = ['id' => $result->get('id')];
            array_push($ids, $id);
        }

        if (!empty($ids)) {
            $customFieldSetRepository->delete($ids, $context->getContext());
        }
    }

    private function addFields($context): void
    {
        /** @var EntityRepository $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $customFieldsGroups = $this->getAllCustomFieldsGroups();
        $addedCustomFieldsGroups = [];
        
        foreach ($customFieldsGroups as $group) {
            if (!$this->customFieldGroupIsExist($group['name'], $context)) {
                array_push($addedCustomFieldsGroups, $group);
            }
        }

        if ($addedCustomFieldsGroups) {
            $customFieldSetRepository->create($addedCustomFieldsGroups, $context->getContext());
        }
    }

    private function customFieldGroupIsExist(string $name, $context)
    {
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $criteria = (new Criteria())->addFilter(new EqualsFilter('name', $name));
        $results = $customFieldSetRepository->search($criteria, $context->getContext())->getEntities()->first();

        return $results;
    }

    private function getAllCustomFieldsGroups()
    {
        return $this->fields;
    }

    /**
     * Обновляет JSON данные со всеми selling items
     */
    private function updateJsonData($context): void
    {
        try {
            // Получаем все selling items
            $sellingItemRepository = $this->container->get('selling_item.repository');
            $categoryRepository = $this->container->get('selling_item_category.repository');
            $salesChannelRepository = $this->container->get('sales_channel.repository');

            // Получаем категории
            $categoryCriteria = new Criteria();
            $categoryCriteria->addFilter(new EqualsFilter('active', true));
            $categories = $categoryRepository->search($categoryCriteria, $context->getContext());

            // Получаем все активные selling items
            $itemsCriteria = new Criteria();
            $itemsCriteria->addFilter(new EqualsFilter('active', true));
            $itemsCriteria->addAssociation('category');
            $itemsCriteria->addAssociation('mainImage');
            $itemsCriteria->addAssociation('previewImage');

            $items = $sellingItemRepository->search($itemsCriteria, $context->getContext());

            // Генерируем JSON структуру
            $jsonData = $this->generateJsonStructure($categories, $items);

            // Сохраняем в custom field всех sales channels
            $salesChannelCriteria = new Criteria();
            $salesChannels = $salesChannelRepository->search($salesChannelCriteria, $context->getContext());

            $updateData = [];
            foreach ($salesChannels as $salesChannel) {
                $customFields = $salesChannel->getCustomFields() ?? [];
                $customFields['selling_items_json_data'] = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $customFields['selling_items_last_updated'] = new \DateTime();

                $updateData[] = [
                    'id' => $salesChannel->getId(),
                    'customFields' => $customFields
                ];
            }

            if (!empty($updateData)) {
                $salesChannelRepository->update($updateData, $context->getContext());
            }

        } catch (\Exception $e) {
            // Логирование ошибки
            error_log('SellingItems JSON update error: ' . $e->getMessage());
        }
    }

    /**
     * Генерирует JSON структуру по образцу
     */
    private function generateJsonStructure($categories, $items): array
    {
        $result = [];

        foreach ($categories as $category) {
            $categoryData = [
                'title' => $category->getName(),
                'id' => $this->generateSlug($category->getName()),
                'type' => 'custom',
                'values' => []
            ];

            // Фильтруем items по категории
            foreach ($items as $item) {
                if ($item->getCategoryId() === $category->getId()) {
                    $categoryData['values'][] = $this->formatItemForJson($item);
                }
            }

            $result[] = $categoryData;
        }

        return $result;
    }

    /**
     * Форматирует item для JSON
     */
    private function formatItemForJson($item): array
    {
        $mainImageUrl = '';
        $previewImageUrl = '';

        if ($item->getMainImage()) {
            $mainImageUrl = $this->getMediaUrl($item->getMainImage());
        }

        if ($item->getPreviewImage()) {
            $previewImageUrl = $this->getMediaUrl($item->getPreviewImage());
        }

        return [
            'name' => $item->getTitle(),
            'subtitle' => $item->getSubtitle() ?? 'Item Level 623',
            'price' => $item->getPrice() ?? 0,
            'id' => $this->generateSlug($item->getTitle()),
            'link' => $item->getLink() ?? '',
            'image' => $mainImageUrl,
            'preview' => $previewImageUrl,
            'category' => $this->generateSlug($item->getCategory()->getName())
        ];
    }

    /**
     * Генерирует slug из строки
     */
    private function generateSlug(string $text): string
    {
        // Удаляем специальные символы и заменяем пробелы на underscores
        $slug = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $slug = preg_replace('/\s+/', '', $slug);
        $slug = lcfirst($slug);
        
        return $slug;
    }

    /**
     * Получает URL медиа файла
     */
    private function getMediaUrl($media): string
    {
        if (!$media) {
            return '';
        }

        // Если есть метод getUrl(), используем его
        if (method_exists($media, 'getUrl') && $media->getUrl()) {
            return $media->getUrl();
        }

        // Иначе генерируем путь самостоятельно
        if ($media->getFileName() && $media->getFileExtension()) {
            return '/media/' . $media->getFileName() . '.' . $media->getFileExtension();
        }

        return '';
    }
}