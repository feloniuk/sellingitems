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

    #[Route(path: "/dressing-room", name: "frontend.selling-items.index", methods: ["GET"])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        // Create criteria for items
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAssociation('category');
        $criteria->addAssociation('mainImage');
        $criteria->addAssociation('previewImage');
        
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
            $selectedResult = $this->sellingItemRepository->search($selectedCriteria, $context->getContext());
            if ($selectedResult->count() > 0) {
                $selectedItem = $selectedResult->first();
            }
        }
        
        // If no selected item, use the first item from results
        if (!$selectedItem && $items->count() > 0) {
            $selectedItem = $items->first();
        }

        return $this->renderStorefront('@SellingItems/storefront/page/selling-items/index.html.twig', [
            'items' => $items,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'selectedItem' => $selectedItem
        ]);
    }
}