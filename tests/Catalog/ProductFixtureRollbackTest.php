<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ProductFixtureRollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    public function testRollbackSingleProductFixture()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        ProductFixtureRollback::create()->execute($productFixture);
        $this->setExpectedException(NoSuchEntityException::class);
        $this->productRepository->getById($productFixture->getId());
    }

    public function testRollbackMultipleProductFixtures()
    {
        $productFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        $otherProductFixture = new ProductFixture(
            ProductBuilder::aSimpleProduct()->build()
        );
        ProductFixtureRollback::create()->execute($productFixture, $otherProductFixture);
        $productDeleted = false;
        try {
            $this->productRepository->getById($productFixture->getId());
        } catch (NoSuchEntityException $e) {
            $productDeleted = true;
        }
        $this->assertTrue($productDeleted, 'First product should be deleted');
        $this->setExpectedException(NoSuchEntityException::class);
        $this->productRepository->getById($otherProductFixture->getId());
    }
}
