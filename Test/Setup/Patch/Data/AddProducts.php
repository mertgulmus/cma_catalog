<?php
/**
 * @category    Scandiweb
 * @package     Scandiweb_Test
 * @author      Mert Gulmus <mert.gulmus@scandiweb.com || info@scandiweb.com>
 * @copyright   Copyright (c) 2023 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Scandiweb\Test\Setup\Patch\Data;

use Exception;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Scandiweb\Migration\Helper\Cms\CmsFileParser;
use Scandiweb\Migration\Helper\MediaMigration;
use Magento\Framework\App\State;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AddProducts
 * @package Scandiweb\Test\Setup\Patch\Data
 */
class AddProducts implements DataPatchInterface
{
    protected const SKU = 't-shirt';
    protected const NAME = 'Sports T-Shirt';
    protected const PRICE = 14.99;

    protected ModuleDataSetupInterface $setup;

    protected ProductInterfaceFactory $productInterfaceFactory;

    protected ProductRepositoryInterface $productRepository;

    protected State $appState;

    protected EavSetup $eavSetup;

    protected StoreManagerInterface $storeManager;

    protected CategoryLinkManagementInterface $categoryLink;

    protected array $sourceItems = [];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CmsFileParser $cmsHelper
     * @param MediaMigration $mediaMigration
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        CategoryLinkManagementInterface $categoryLink
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->categoryLink = $categoryLink;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    public function execute()
    {
        $product = $this->productInterfaceFactory->create();

        if ($product->getIdBySku(self::SKU)) {
            return;
        }

        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setName(self::NAME)
        ->setSku(self::SKU)
        ->setUrlKey(self::SKU)
        ->setPrice(self::PRICE)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED);

        $product = $this->productRepository->save($product);

        $this->categoryLink->assignProductToCategories($product->getSku(), [2]);
    }

    /**
     * {@inheritDoc}
     *  */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
