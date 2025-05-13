<?php
declare(strict_types=1);

namespace RadWorks\Dropday\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateModulePathPrefixForConfigValues implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $setup
     */
    private ModuleDataSetupInterface $setup;

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(ModuleDataSetupInterface $setup)
    {
        $this->setup = $setup;
    }

    /**
     * Run code inside patch.
     *
     * @return $this
     */
    public function apply()
    {
        $connection = $this->setup->getConnection();
        $connection->update(
            $connection->getTableName('core_config_data'),
            [
                'path' => new \Zend_Db_Expr('REGEXP_REPLACE(path, "^[a-z]+_dropday/", "radworks_dropday/")')
            ],
            ['path LIKE ?' => '%_dropday/%']
        );

        return $this;
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}