<?php
namespace bulutbulutsun\listener;


use bulutbulutsun\Loader;
use pocketmine\block\Crops;
use pocketmine\block\Farmland;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\world\Position;

class EventListener implements Listener
{


    public function onBlockBreak(BlockBreakEvent $event)
    {
        if ($event->isCancelled()) return;
        $block = $event->getBlock();
        $position = $block->getPosition();
        if ($block instanceof Farmland) {
            Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
        }
        if ($block instanceof Crops) {
            Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
        }
    }
    public function onTrample(EntityTrampleFarmlandEvent $event){
        $block = $event->getBlock();
        $position = $block->getPosition();
        $crops = new Position($position->getX(), $position->getY() + 1, $position->getZ(), $position->getWorld());
        if ($position->getWorld()->getBlock($crops) instanceof Crops) {
            Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
        }
    }
    public function onBlockSpread(BlockSpreadEvent $event)
    {
        $blocks = $event->getBlock()->getAffectedBlocks();
        foreach ($blocks as $block) {
            if ($block instanceof Crops) {
                $position = $block->getPosition();
                Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
            }
        }
    }
    public function onBlockPlace(BlockPlaceEvent $event){
        if ($event->isCancelled()) return;
        $item = $event->getItem();
        $block = $event->getBlockAgainst();
        $position = $block->getPosition();
        $seeds = [VanillaItems::WHEAT_SEEDS(), VanillaItems::PUMPKIN_SEEDS(), VanillaItems::MELON_SEEDS(), VanillaItems::BEETROOT_SEEDS()];
        foreach ($seeds as $seed) {
            if ($item instanceof $seed) {
                Loader::getInstance()->setData($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
            }
        }
    }
}