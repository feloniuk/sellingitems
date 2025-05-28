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
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        $categoryId = $request->query->get('category');
        if ($categoryId) {
            $criteria->addFilter(new EqualsFilter('categoryId', $categoryId));
        }

        $items = $this->sellingItemRepository->search($criteria, $context->getContext());

        // Получаем категории для фильтра
        $categoryCriteria = new Criteria();
        $categoryCriteria->addFilter(new EqualsFilter('active', true));
        $categoryCriteria->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));
        
        $categories = $this->sellingItemCategoryRepository->search($categoryCriteria, $context->getContext());

        return $this->renderStorefront('@SellingItems/storefront/page/selling-items/index.html.twig', [
            'items' => $items,
            'categories' => $categories,
            'selectedCategory' => $categoryId
        ]);
    }
}