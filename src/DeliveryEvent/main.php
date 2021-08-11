<?php

namespace DeliveryEvent;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player;
use pocketmine\item\Item;
use pocketmine\event\block\BlockBreakEvent;

class main extends PluginBase implements Listener
{

    private Config $sign;
    private Config $player1, $player2, $player3,  $player6, $player7;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        if (!file_exists($this->getDataFolder()))
        {
            @mkdir($this->getDataFolder(), 0755, true);
        }
        $this->sign = new Config($this->getDataFolder() . "sign.yml", Config::YAML);
        $this->player1 = new Config($this->getDataFolder() . "player1.yml", Config::YAML);
        $this->player2 = new Config($this->getDataFolder() . "player2.yml", Config::YAML);
        $this->player3 = new Config($this->getDataFolder() . "player3.yml", Config::YAML);
        $this->player6 = new Config($this->getDataFolder() . "player6.yml", Config::YAML);
        $this->player7 = new Config($this->getDataFolder() . "player7.yml", Config::YAML);
    }

    public function onSignChange(SignChangeEvent $event){
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        if($event->getLine(0) == "Delivery")
        {
            if (!$player->isOp())
            {
                $player->sendMessage("§cこの看板を設置する権限がありません");
                return;
            }
            if ($event->getLine(1) != (1 or 2 or 3 or 4 or 5 or 6 or 7))
            {
                $player->sendMessage("§cミッション番号を指定して下さい (1 ~ 7)");
                return;
            }
            $place = $x.":".$y.":".$z.":".$block->getLevel()->getFolderName();
            $this->sign->set($place,$event->getLine(1));
            $this->sign->save();
            $player->sendMessage("§bDelivery看板作成完了！!");
            $event->setLine(0, "§b【☆Delivery Event☆】");
            $event->setLine(1, "§6Mission {$event->getLine(1)}");
            $event->setLine(3, "§a看板タップでミッションクリア！");
        }
    }

    public function onTap(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $event->getBlock();
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        $place = $x.":".$y.":".$z.":".$block->getLevel()->getFolderName();
        if($this->sign->exists($place))
        {
            $delivery = $this->sign->get($place);
            if($this->CheckDelivery($delivery,$player) == false)
            {
                $player->sendMessage("§a[Delivery] §cアイテムが不足しています。");
                return;
            }
            if($this->CheckDeliveryPermission($delivery,$player) == false)
            {
                $player->sendMessage("§a[Delivery] §c既にこのミッションは達成しています。");
                return;
            }
            if($this->CheckInventory($delivery,$player) == false){
                $player->sendMessage("§a[Delivery] §cインベントリに空きがありません。。");
                return;
            }

            $this->getServer()->broadcastMessage("§a[Delivery] §f".date("H時i分s秒")."に{$name}が§eMission{$delivery}§fを達成しました！");
            $this->Delivery($delivery,$player);
        }
    }

    public function onbreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $event->getBlock();
        $x = $block->getX();
        $y = $block->getY();
        $z = $block->getZ();
        $place = $x . ":" . $y . ":" . $z . ":" . $block->getLevel()->getName();
        if ($this->sign->exists($place)) {
            if ($player->isOp()) {
                $this->sign->remove($place);
                $this->sign->save();
                $player->sendMessage("Delivery看板を解体しました");
            } else {
                $player->getServer()->broadcastMessage(" Delivery看板を§c" . $name . "§6が破壊しようとしている！");
                $event->setCancelled();
            }
        }
    }

    public function CheckInventory(Int $Delivery, Player $player) :bool{
        $inventory = $player->getInventory();
        switch($Delivery) {
            case 1:
            case 2:
                $item1 = Item::get(205, 0, 1);
                if(!$inventory->canAddItem($item1)){
                    return false;
                } else {
                    return true;
                }
            case 3:
                $item1 = Item::get(260, 0, 3);
                if(!$inventory->canAddItem($item1)){
                    return false;
                } else {
                    return true;
                 }
            case 4:
                $item1 = Item::get(319, 0, 10);
                if(!$inventory->canAddItem($item1)){
                    return false;
                } else {
                    return true;
                }
            case 5:
                $item1 = Item::get(260, 0, 5);
                if(!$inventory->canAddItem($item1)){
                    return false;
                } else {
                    return true;
                }
            case 6:
            case 7:
                return true;
        }

    }

    public function CheckDeliveryPermission(Int $Delivery, Player $player) :bool
    {
        $name = $player->getName();
        switch($Delivery){
            case 1:
                if($this->player1->exists($name)){
                    return false;
                }else{
                    return true;
                }
            case 2:
                if($this->player2->exists($name)){
                    return false;
                }else{
                    return true;
                }
            case 3:
                if($this->player3->exists($name)){
                    return false;
                }else{
                    return true;
                }
            case 6:
                if($this->player6->exists(1)){
                    return false;
                }else{
                    return true;
                }
            case 7:
                if($this->player7->exists(1)){
                    return false;
                }else{
                    return true;
                }
            case 4:
            case 5:
                return true;
            default:
                return false;
        }
    }

    public function CheckDelivery(Int $Delivery, Player $player) :bool
    {
        $inventory = $player->getInventory();
        switch($Delivery){
            case 1:
                $item = Item::get(264,0,320);
                if($inventory->contains($item)){
                    return true;
                } else {
                    return false;
                }
            case 2:
                $item = Item::get(322,0,320);
                if($inventory->contains($item)){
                    return true;
                } else {
                    return false;
                }
            case 3:
                $item = Item::get(81,0,256);
                if($inventory->contains($item)){
                    return true;
                } else {
                    return false;
                }
            case 4:
                $item1 = Item::get(297,0,10);
                $item2 = Item::get(391,0,10);
                $item3 = Item::get(392,0,10);
                $item4 = Item::get(293,0,10);
                if($inventory->contains($item1) and $inventory->contains($item2) and $inventory->contains($item3)){
                    return true;
                } else if($inventory->contains($item1) and $inventory->contains($item2) and $inventory->contains($item4)){
                    return true;
                } else {
                    return false;
                }
            case 5:
                $item = Item::get(394,0,128);
                if($inventory->contains($item)){
                    return true;
                } else {
                    return false;
                }
            case 6:
                $item1 = Item::get(264,0,6400);
                $item2 = Item::get(265,0,6400);
                $item3 = Item::get(266,0,6400);
                if($inventory->contains($item1) and $inventory->contains($item2) and $inventory->contains($item3)){
                    return true;
                } else {
                    return false;
                }
            case 7:
                $item1 = Item::get(264,0,999999999);
                $item2 = Item::get(265,0,999999999);
                $item3 = Item::get(266,0,999999999);
                if($inventory->contains($item1) and $inventory->contains($item2) and $inventory->contains($item3)){
                    return true;
                } else {
                    return false;
                }
            default:
                return false;
        }
    }

    public function Delivery(Int $Delivery, Player $player): void
    {
        $inventory = $player->getInventory();
        $name = $player->getName();
        switch($Delivery) {
            case 1:
                $item1 = Item::get(264, 0, 320);
                $item2 = Item::get(205, 0, 1);
                $inventory->removeItem($item1);
                $inventory->addItem($item2);
                $this->player1->set($name,date("Y年m月d日H時i分s秒"));
                $this->player1->save();
                break;
            case 2:
                $item1 = Item::get(322, 0, 320);
                $item2 = Item::get(205, 0, 1);
                $inventory->removeItem($item1);
                $inventory->addItem($item2);
                $this->player2->set($name,date("Y年m月d日H時i分s秒"));
                $this->player2->save();
                break;
            case 3:
                $item1 = Item::get(81, 0, 256);
                $item2 = Item::get(260, 0, 3);
                $inventory->removeItem($item1);
                $inventory->addItem($item2);
                $this->player3->set($name,date("Y年m月d日H時i分s秒"));
                $this->player3->save();
                break;
            case 4:
                $item1 = Item::get(297, 0, 10);
                $item2 = Item::get(391, 0, 10);
                $item3 = Item::get(392, 0, 10);
                $item4 = Item::get(293, 0, 10);
                $item5 = Item::get(319, 0, 10);
                if ($inventory->contains($item1) and $inventory->contains($item2) and $inventory->contains($item3)) {
                    $inventory->removeItem($item1);
                    $inventory->removeItem($item2);
                    $inventory->removeItem($item3);
                    $inventory->addItem($item5);
                } else if ($inventory->contains($item1) and $inventory->contains($item2) and $inventory->contains($item4)) {
                    $inventory->removeItem($item1);
                    $inventory->removeItem($item2);
                    $inventory->removeItem($item4);
                    $inventory->addItem($item5);
                }
                break;
            case 5:
                $item1 = Item::get(394, 0, 128);
                $item2 = Item::get(260, 0, 5);
                $inventory->removeItem($item1);
                $inventory->addItem($item2);
                break;
            case 6:
                $item1 = Item::get(264, 0, 6400);
                $item2 = Item::get(265, 0, 6400);
                $item3 = Item::get(266, 0, 6400);
                $inventory->removeItem($item1);
                $inventory->removeItem($item2);
                $inventory->removeItem($item3);
                $this->player6->set(1,date("Y年m月d日H時i分s秒"));
                $this->player6->set(2,$name);
                $this->player6->save();
                break;
            case 7:
                $item1 = Item::get(264, 0, 999999999);
                $item2 = Item::get(265, 0, 999999999);
                $item3 = Item::get(266, 0, 999999999);
                $inventory->removeItem($item1);
                $inventory->removeItem($item2);
                $inventory->removeItem($item3);
                $this->player7->set(1,date("Y年m月d日H時i分s秒"));
                $this->player7->set(2,$name);
                $this->player7->save();
                break;
        }

    }

}