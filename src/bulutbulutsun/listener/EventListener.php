<?php
namespace bulutbulutsun\listener;


use bulutbulutsun\Loader;
use pocketmine\block\Cactus;
use pocketmine\block\CocoaBlock;
use pocketmine\block\Crops;
use pocketmine\block\Dirt;
use pocketmine\block\Farmland;
use pocketmine\block\Grass;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\Sand;
use pocketmine\block\SoulSand;
use pocketmine\block\Sugarcane;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\BeetrootSeeds;
use pocketmine\item\Carrot;
use pocketmine\item\CocoaBeans;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\MelonSeeds;
use pocketmine\item\Potato;
use pocketmine\item\PumpkinSeeds;
use pocketmine\item\SweetBerries;
use pocketmine\item\WheatSeeds;
use pocketmine\world\Position;

class EventListener implements Listener
{


    public function onBlockBreak(BlockBreakEvent $event)
    {
        if ($event->isCancelled()) return;
        $block = $event->getBlock();
        $position = $block->getPosition();
        if ($block instanceof Farmland || $block instanceof SoulSand || $block instanceof Grass || $block instanceof Dirt || $block instanceof Sand) {
            //For data in the database
            Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
            //For data in the cache
            Loader::getInstance()->deleteCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
        }
        if ($block instanceof Crops || $block instanceof NetherWartPlant || $block instanceof CocoaBlock || $block instanceof SweetBerryBush || $block instanceof Cactus || $block instanceof Sugarcane) {
            //For data in the database
            Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
            //For data in the cache
            Loader::getInstance()->deleteCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
        }
    }
    public function explosion(EntityExplodeEvent $event)
    {
        $blocks = $event->getBlockList();
        $entity = $event->getEntity();
        if ($entity instanceof PrimedTNT) {
            foreach ($blocks as $block) {
                if ($block instanceof Crops || $block instanceof NetherWartPlant || $block instanceof CocoaBlock || $block instanceof SweetBerryBush || $block instanceof Cactus || $block instanceof Sugarcane) {
                    $position = $block->getPosition();
                    //For data in the database
                    Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
                    //For data in the cache
                    Loader::getInstance()->deleteCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
                }
            }

        }
    }

    public function onTrample(EntityTrampleFarmlandEvent $event)
    {
        $block = $event->getBlock();
        $position = $block->getPosition();
        $crops = new Position($position->getX(), $position->getY() + 1, $position->getZ(), $position->getWorld());
        if ($position->getWorld()->getBlock($crops) instanceof Crops || $position->getWorld()->getBlock($crops) instanceof NetherWartPlant || $block instanceof SweetBerryBush || $block instanceof Cactus || $block instanceof Sugarcane) {
            //For data in the database
            Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
            //For data in the cache
            Loader::getInstance()->deleteCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
        }
    }

    public function onBlockSpread(BlockSpreadEvent $event)
    {
        $blocks = $event->getBlock()->getAffectedBlocks();
        foreach ($blocks as $block) {
            if ($block instanceof Crops || $block instanceof NetherWartPlant || $block instanceof CocoaBeans || $block instanceof SweetBerryBush || $block instanceof Cactus || $block instanceof Sugarcane) {
                $position = $block->getPosition();
                //For data in the database
                Loader::getInstance()->deleteData($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
                //For data in the cache
                Loader::getInstance()->deleteCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $item = $event->getPlayer()->getInventory()->getItemInHand();
        $block = $event->getBlockAgainst();
        $position = $block->getPosition();
        $nether_wart = ItemIdentifier::fromBlock(VanillaBlocks::NETHER_WART());
        $cactus = ItemIdentifier::fromBlock(VanillaBlocks::CACTUS());
        $sugar_cane = ItemIdentifier::fromBlock(VanillaBlocks::SUGARCANE());
        if ($item instanceof SweetBerries or $item instanceof WheatSeeds or $item instanceof PumpkinSeeds or $item instanceof MelonSeeds or $item instanceof Potato or $item instanceof Carrot or $item instanceof BeetrootSeeds or $item->getTypeId() == $nether_wart->getTypeId()) {
            Loader::getInstance()->setCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
        }
        if ($block instanceof Sand){ //To exclude a cactus from data added above a cactus
            if ($item->getTypeId() == $cactus->getTypeId()){
                Loader::getInstance()->setCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
            }
        }
        if ($block instanceof Sand || $block instanceof Grass || $block instanceof Dirt){ //To exclude a cane from data added above a cane
            if ($item->getTypeId() == $sugar_cane->getTypeId()){
                Loader::getInstance()->setCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY() + 1, $position->getZ());
            }
        }
    }

    public function onBlockInteract(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();
        $item = $event->getItem();
        if ($item instanceof CocoaBeans) {
            if ($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                $position = $block->getSide($event->getFace())->getPosition();
                Loader::getInstance()->setCache($position->getWorld()->getFolderName(), $position->getX(), $position->getY(), $position->getZ());
            }
        }
    }
}