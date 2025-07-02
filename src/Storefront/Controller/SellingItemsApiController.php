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
class SellingItemsApiController extends StorefrontController
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

    #[Route(path: "/selling-items/api/items", name: "frontend.selling-items.api.items", methods: ["GET"], options: ["seo" => false])]
    public function getItems(Request $request, SalesChannelContext $context): JsonResponse
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        
        // Add associations
        $criteria->addAssociation('category');
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');
        
        // Add media associations with thumbnails
        $criteria->getAssociation('mainImage')
            ->addAssociation('thumbnails');
            
        $criteria->getAssociation('previewImage')
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

        // Handle pagination
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 24);
        $offset = ($page - 1) * $limit;
        
        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        // Get items
        $searchResult = $this->sellingItemRepository->search($criteria, $context->getContext());
        
        $items = [];
        foreach ($searchResult->getEntities() as $item) {
            $items[] = $this->formatItem($item);
        }

        return new JsonResponse([
            'items' => $items,
            'total' => $searchResult->getTotal(),
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($searchResult->getTotal() / $limit)
        ]);
    }

    #[Route(path: "/selling-items/api/categories", name: "frontend.selling-items.api.categories", methods: ["GET"], options: ["seo" => false])]
    public function getCategories(Request $request, SalesChannelContext $context): JsonResponse
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));
        
        $categories = $this->sellingItemCategoryRepository->search($criteria, $context->getContext());
        
        $result = [];
        foreach ($categories->getEntities() as $category) {
            $result[] = [
                'id' => $category->getId(),
                'name' => $category->getName()
            ];
        }

        return new JsonResponse([
            'categories' => $result,
            'total' => $categories->getTotal()
        ]);
    }

    #[Route(path: "/selling-items/api/item/{itemId}", name: "frontend.selling-items.api.item", methods: ["GET"], options: ["seo" => false])]
    public function getItem(string $itemId, Request $request, SalesChannelContext $context): JsonResponse
    {
        $criteria = new Criteria([$itemId]);
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');
        $criteria->addAssociation('category');
        
        // Add media associations
        $criteria->getAssociation('mainImage')
            ->addAssociation('thumbnails');
            
        $criteria->getAssociation('previewImage')
            ->addAssociation('thumbnails');

        $item = $this->sellingItemRepository->search($criteria, $context->getContext())->first();

        if (!$item) {
            return new JsonResponse(['error' => 'Item not found'], 404);
        }

        return new JsonResponse($this->formatItem($item));
    }

    private function formatItem($item): array
    {
        $mainImageUrl = '';
        $previewImageUrl = '';

        if ($item->getMainImage()) {
            $media = $item->getMainImage();
            // Get the URL from media entity
            if (method_exists($media, 'getUrl')) {
                $mainImageUrl = $media->getUrl();
            } else {
                // Fallback: construct URL manually
                $mainImageUrl = '/media/' . $media->getPath();
            }
        }

        if ($item->getPreviewImage()) {
            $media = $item->getPreviewImage();
            // Get the URL from media entity
            if (method_exists($media, 'getUrl')) {
                $previewImageUrl = $media->getUrl();
            } else {
                // Fallback: construct URL manually
                $previewImageUrl = '/media/' . $media->getPath();
            }
        }

        return [
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
        ];
    }
}