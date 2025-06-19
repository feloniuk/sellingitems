<?php declare(strict_types=1);

namespace SellingItems\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
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
        // Simply render the Vue app template
        $templateData = [
            'page' => [
                'metaInformation' => [
                    'metaTitle' => 'Dressing Room',
                    'metaDescription' => 'Browse our collection in the dressing room'
                ]
            ]
        ];

        return $this->renderStorefront('@SellingItems/storefront/page/selling-items/vue-index.html.twig', $templateData);
    }
}