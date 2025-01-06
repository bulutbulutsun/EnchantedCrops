<?php

namespace bulutbulutsun;

use bulutbulutsun\listener\EventListener;
use bulutbulutsun\task\GrowTask;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Crops;
use pocketmine\block\MelonStem;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\PumpkinStem;
use pocketmine\block\Stem;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Loader extends PluginBase{
    /** @var DataConnector */
    private $database;

    /** @var Loader|null */
    private static $instance;

    public static function getInstance(): ?Loader{
        return self::$instance;
    }
    protected function onEnable(): void
    {
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->saveDefaultConfig();
        $this->initDatabase();
        $this->getServer()->getLogger()->info("Plugin loaded!");
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->getServer()->getAsyncPool()->submitTask(new GrowTask());
        }), 20 * $this->getConfig()->get("seconds")); // 20 * 60 = 1200 tick = 60 second
    }
    protected function onDisable(): void
    {
        $this->getServer()->getLogger()->info("Plugin disabled!");
    }
    private function initDatabase(): void {
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "mysql" => "mysql.sql"
        ]);
        $this->database->executeGeneric('table.create', [], null, function($error) {
            $this->getLogger()->error("Failed to create table: " . $error->getMessage());
        });
    }
    public function setData($world,$x,$y,$z)
    {
        $this->database->executeInsert('table.setdata', [
            "world" => $world,
            "x" => $x,
            "y" => $y,
            "z" => $z
        ], null, function($error) {
            $this->getLogger()->error("Failed to insert data: " . $error->getMessage());
        });
    }
    public function deleteData($world,$x,$y,$z)
    {
        $this->database->executeChange('table.deletedata', [
            "world" => $world,
            "x" => $x,
            "y" => $y,
            "z" => $z
        ], null, function($error) {
            $this->getLogger()->error("Failed to delete data: " . $error->getMessage());
        });
    }
    public function getData(callable $callback): void {
        $this->database->executeSelect("table.getdata", [], function(array $rows) use ($callback) {
            $coordinates = [];
            foreach ($rows as $row) {
                $coordinates[] = [
                    "world" => $row["world"],
                    "x" => $row["x"],
                    "y" => $row["y"],
                    "z" => $row["z"]
                ];
            }
            $callback($coordinates);
        });
    }
    public function tickCrops($world,$x,$y,$z)
    {
        $world = $this->getServer()->getWorldManager()->getWorldByName($world);
        if ($world instanceof World) {
            if (!$world->isLoaded()) {
                $this->getServer()->getWorldManager()->loadWorld($world->getFolderName());
            }
            if (!$world->isChunkLoaded($x, $y)) {
                $world->loadChunk($x, $y);
            }
            $crop = $world->getBlockAt($x, $y, $z);
            if ($crop instanceof Crops) {
                if ($crop->getAge() < Crops::MAX_AGE) {
                    $tempAge = $crop->getAge() + mt_rand(2, 5);
                    if ($tempAge > Crops::MAX_AGE) {
                        $tempAge = Crops::MAX_AGE;
                    }
                    $newcrop = $crop->setAge($tempAge);
                    BlockEventHelper::grow($crop, $newcrop, null);
                }
            }
            if ($crop instanceof NetherWartPlant){
                if ($crop->getAge() < NetherWartPlant::MAX_AGE) {
                    $tempAge = $crop->getAge() + mt_rand(2, 5);
                    if ($tempAge > NetherWartPlant::MAX_AGE) {
                        $tempAge = NetherWartPlant::MAX_AGE;
                    }
                    $newcrop = $crop->setAge($tempAge);
                    BlockEventHelper::grow($crop, $newcrop, null);
                }
            }
            if ($crop instanceof PumpkinStem) {
                if ($crop->getAge() >= PumpkinStem::MAX_AGE) {
                    $grow = VanillaBlocks::PUMPKIN();
                    foreach(Facing::HORIZONTAL as $side){
                        if($crop->getSide($side)->hasSameTypeId($grow)){
                            return;
                        }
                    }
                    $facing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];
                    $side = $crop->getSide($facing);
                    if($side->getTypeId() === BlockTypeIds::AIR && $side->getSide(Facing::DOWN)->hasTypeTag(BlockTypeTags::DIRT)){
                        if(BlockEventHelper::grow($side, $grow, null)){
                            $crop->getPosition()->getWorld()->setBlock($crop->getPosition(), $crop->setFacing($facing));
                        }
                    }
                }
            }
            if ($crop instanceof MelonStem) {
                if ($crop->getAge() >= MelonStem::MAX_AGE) {
                    $grow = VanillaBlocks::MELON();
                    foreach(Facing::HORIZONTAL as $side){
                        if($crop->getSide($side)->hasSameTypeId($grow)){
                            return;
                        }
                    }
                    $facing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];
                    $side = $crop->getSide($facing);
                    if($side->getTypeId() === BlockTypeIds::AIR && $side->getSide(Facing::DOWN)->hasTypeTag(BlockTypeTags::DIRT)){
                        if(BlockEventHelper::grow($side, $grow, null)){
                            $crop->getPosition()->getWorld()->setBlock($crop->getPosition(), $crop->setFacing($facing));
                        }
                    }
                }
            }
        }
    }
}
