<?php declare(strict_types=1);

namespace SellingItems\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SellingItemsJsonUpdateSubscriber implements EventSubscriberInterface
{
    private EntityRepository $sellingItemRepository;
    private EntityRepository $sellingItemCategoryRepository;
    private EntityRepository $salesChannelRepository;

    public function __construct(
        EntityRepository $sellingItemRepository,
        EntityRepository $sellingItemCategoryRepository,
        EntityRepository $salesChannelRepository
    ) {
        $this->sellingItemRepository = $sellingItemRepository;
        $this->sellingItemCategoryRepository = $sellingItemCategoryRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'selling_item.written' => 'onSellingItemWritten',
            'selling_item_category.written' => 'onSellingItemCategoryWritten',
        ];
    }

    public function onSellingItemWritten(EntityWrittenEvent $event): void
    {
        $this->updateJsonData($event->getContext());
    }

    public function onSellingItemCategoryWritten(EntityWrittenEvent $event): void
    {
        $this->updateJsonData($event->getContext());
    }

    private function updateJsonData($context): void
    {
        try {
            // Проверяем, включено ли автообновление
            if (!$this->isAutoUpdateEnabled($context)) {
                return;
            }

            // Получаем категории
            $categoryCriteria = new Criteria();
            $categoryCriteria->addFilter(new EqualsFilter('active', true));
            $categories = $this->sellingItemCategoryRepository->search($categoryCriteria, $context);

            // Получаем все активные selling items
            $itemsCriteria = new Criteria();
            $itemsCriteria->addFilter(new EqualsFilter('active', true));
            $itemsCriteria->addAssociation('category');
            $itemsCriteria->addAssociation('mainImage');
            $itemsCriteria->addAssociation('previewImage');

            $items = $this->sellingItemRepository->search($itemsCriteria, $context);

            // Генерируем JSON структуру
            $jsonData = $this->generateJsonStructure($categories, $items);

            // Обновляем sales channels
            $this->updateSalesChannelsJsonData($jsonData, $context);

        } catch (\Exception $e) {
            error_log('SellingItems auto-update JSON error: ' . $e->getMessage());
        }
    }

    private function isAutoUpdateEnabled($context): bool
    {
        // Получаем первый sales channel и проверяем настройку автообновления
        $criteria = new Criteria();
        $criteria->setLimit(1);
        
        $salesChannel = $this->salesChannelRepository->search($criteria, $context)->first();
        
        if ($salesChannel && $salesChannel->getCustomFields()) {
            return $salesChannel->getCustomFields()['selling_items_auto_update'] ?? false;
        }

        return false;
    }

    private function updateSalesChannelsJsonData(array $jsonData, $context): void
    {
        $salesChannelCriteria = new Criteria();
        $salesChannels = $this->salesChannelRepository->search($salesChannelCriteria, $context);

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
            $this->salesChannelRepository->update($updateData, $context);
        }
    }

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

    private function generateSlug(string $text): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $slug = preg_replace('/\s+/', '', $slug);
        $slug = lcfirst($slug);
        
        return $slug;
    }

    private function getMediaUrl($media): string
    {
        if (!$media) {
            return '';
        }

        if (method_exists($media, 'getUrl') && $media->getUrl()) {
            return $media->getUrl();
        }

        if ($media->getFileName() && $media->getFileExtension()) {
            return '/media/' . $media->getFileName() . '.' . $media->getFileExtension();
        }

        return '';
    }
}