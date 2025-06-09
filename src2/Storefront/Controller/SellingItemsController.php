<?php declare(strict_types=1);

namespace SellingItems\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class SellingItemsController extends StorefrontController
{
    private EntityRepository $sellingItemRepository;
    private EntityRepository $sellingItemCategoryRepository;

    public function __construct(
        EntityRepository $sellingItemRepository,
        EntityRepository $sellingItemCategoryRepository
    ) {
        $this->sellingItemRepository = $sellingItemRepository;
        $this->sellingItemCategoryRepository = $sellingItemCategoryRepository;
    }

    #[Route(path: "/dressing-room", name: "frontend.selling-items.index", methods: ["GET"])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        // Create criteria for items
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        // Important: Add associations for media entities
        $criteria->addAssociation('category');
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');

        // Add media associations to load full media data
        $criteria->getAssociation('mainImage')
            ->addAssociation('mediaFolder')
            ->addAssociation('thumbnails');

        $criteria->getAssociation('previewImage')
            ->addAssociation('mediaFolder')
            ->addAssociation('thumbnails');

        // Handle search
        $searchTerm = $request->query->get('search');
        if ($searchTerm) {
            $criteria->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new ContainsFilter('title', $searchTerm),
                    new ContainsFilter('subtitle', $searchTerm),
                    new ContainsFilter('category.name', $searchTerm)
                ])
            );
        }

        // Handle sorting
        $sort = $request->query->get('sort', 'createdAt');
        $order = $request->query->get('order', 'desc');

        if ($sort === 'price') {
            $criteria->addSorting(new FieldSorting('price', $order === 'desc' ? FieldSorting::DESCENDING : FieldSorting::ASCENDING));
        } else {
            $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        }

        // Handle category filter
        $categoryId = $request->query->get('category');
        if ($categoryId) {
            $criteria->addFilter(new EqualsFilter('categoryId', $categoryId));
        }

        // Search items
        $items = $this->sellingItemRepository->search($criteria, $context->getContext());

        // Get categories for filter
        $categoryCriteria = new Criteria();
        $categoryCriteria->addFilter(new EqualsFilter('active', true));
        $categoryCriteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));

        $categories = $this->sellingItemCategoryRepository->search($categoryCriteria, $context->getContext());

        // Get selected item (first item by default)
        $selectedItem = null;
        $selectedItemId = $request->query->get('selected');

        if ($selectedItemId) {
            $selectedCriteria = new Criteria([$selectedItemId]);
            $selectedCriteria->addAssociation('mainImage');
            $selectedCriteria->addAssociation('previewImage');

            // Add media associations
            $selectedCriteria->getAssociation('mainImage')
                ->addAssociation('mediaFolder')
                ->addAssociation('thumbnails');

            $selectedCriteria->getAssociation('previewImage')
                ->addAssociation('mediaFolder')
                ->addAssociation('thumbnails');

            $selectedResult = $this->sellingItemRepository->search($selectedCriteria, $context->getContext());
            if ($selectedResult->count() > 0) {
                $selectedItem = $selectedResult->first();
            }
        }

        // If no selected item, use the first item from results
        if (!$selectedItem && $items->count() > 0) {
            $selectedItem = $items->first();
        }

        $templateData = [
            'items' => $items,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'selectedItem' => $selectedItem,
            'searchTerm' => $searchTerm,
            'page' => [
                'metaInformation' => [
                    'metaTitle' => 'Dressing Room',
                    'metaDescription' => 'Browse our collection in the dressing room'
                ]
            ]
        ];

        // Check if this is an AJAX request
        if ($request->query->get('ajax') || $request->isXmlHttpRequest()) {
            // Return only the items grid for AJAX requests
            return $this->renderStorefront('@SellingItems/storefront/page/selling-items/items-grid.html.twig', $templateData);
        }

        return $this->renderStorefront('@SellingItems/storefront/page/selling-items/index.html.twig', $templateData);
    }

    #[Route(path: "/dressing-room/item/{itemId}", name: "frontend.selling-items.item", methods: ["GET"], options: ["seo" => false])]
    public function getItem(string $itemId, Request $request, SalesChannelContext $context): JsonResponse
    {
        $criteria = new Criteria([$itemId]);
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');
        $criteria->addAssociation('category');

        // Add media associations
        $criteria->getAssociation('mainImage')
            ->addAssociation('mediaFolder')
            ->addAssociation('thumbnails');

        $criteria->getAssociation('previewImage')
            ->addAssociation('mediaFolder')
            ->addAssociation('thumbnails');

        $item = $this->sellingItemRepository->search($criteria, $context->getContext())->first();

        if (!$item) {
            return new JsonResponse(['error' => 'Item not found'], 404);
        }

        $mainImageUrl = '';
        $previewImageUrl = '';

        if ($item->getMainImage()) {
            $mainImageUrl = $item->getMainImage()->getUrl() ?: $this->generateMediaUrl($item->getMainImage());
        }

        if ($item->getPreviewImage()) {
            $previewImageUrl = $item->getPreviewImage()->getUrl() ?: $this->generateMediaUrl($item->getPreviewImage());
        }

        return new JsonResponse([
            'id' => $item->getId(),
            'title' => $item->getTitle(),
            'subtitle' => $item->getSubtitle(),
            'price' => $item->getPrice(),
            'mainImage' => $mainImageUrl,
            'previewImage' => $previewImageUrl,
            'category' => $item->getCategory() ? [
                'id' => $item->getCategory()->getId(),
                'name' => $item->getCategory()->getName()
            ] : null
        ]);
    }

    private function generateMediaUrl($media): string
    {
        // This is a simplified version. In a real implementation,
        // you would use Shopware's media URL generation service
        return '/media/' . $media->getFileName() . '.' . $media->getFileExtension();
    }
}