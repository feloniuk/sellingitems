<?php declare(strict_types=1);

namespace SellingItems\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route(path: "/selling-items", name: "frontend.selling-items.index", methods: ["GET"])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAssociation('category');
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');

        // Category filtering
        $categoryId = $request->query->get('category');
        if ($categoryId) {
            $criteria->addFilter(new EqualsFilter('categoryId', $categoryId));
        }

        // Sorting
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortOrder = $request->query->get('order', 'desc');

        $sortDirection = strtolower($sortOrder) === 'asc'
            ? FieldSorting::ASCENDING
            : FieldSorting::DESCENDING;

        switch ($sortBy) {
            case 'price':
                $criteria->addSorting(new FieldSorting('price', $sortDirection));
                break;
            case 'title':
                $criteria->addSorting(new FieldSorting('title', $sortDirection));
                break;
            default:
                $criteria->addSorting(new FieldSorting('createdAt', $sortDirection));
                break;
        }

        // Limit results for performance
        $criteria->setLimit(50);

        $searchResult = $this->sellingItemRepository->search($criteria, $context->getContext());

        // Get categories for filter
        $categoryCriteria = new Criteria();
        $categoryCriteria->addFilter(new EqualsFilter('active', true));
        $categoryCriteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));

        $categoriesResult = $this->sellingItemCategoryRepository->search($categoryCriteria, $context->getContext());

        return $this->renderStorefront('@SellingItems/storefront/page/selling-items/index.html.twig', [
            'items' => $searchResult->getElements(), // Получаем массив элементов
            'itemsTotal' => $searchResult->getTotal(), // Общее количество
            'categories' => $categoriesResult->getElements(), // Массив категорий
            'selectedCategory' => $categoryId,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder
        ]);
    }

    #[Route(path: "/selling-items/api/item/{id}", name: "frontend.selling-items.api.item", methods: ["GET"])]
    public function getItem(string $id, SalesChannelContext $context): Response
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('category');
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');

        $item = $this->sellingItemRepository->search($criteria, $context->getContext())->first();

        if (!$item) {
            return $this->json(['error' => 'Item not found'], 404);
        }

        return $this->json([
            'id' => $item->getId(),
            'title' => $item->getTitle(),
            'subtitle' => $item->getSubtitle(),
            'price' => $item->getPrice(),
            'mainImage' => $item->getMainImage() ? $item->getMainImage()->getUrl() : null,
            'previewImage' => $item->getPreviewImage() ? $item->getPreviewImage()->getUrl() : null,
            'category' => $item->getCategory() ? $item->getCategory()->getName() : null
        ]);
    }
}