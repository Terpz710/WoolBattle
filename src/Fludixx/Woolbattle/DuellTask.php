<?php

namespace Fludixx\Woolbattle;

use pocketmine\item\Item;
use pocketmine\world\World;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as f;
use pocketmine\player\GameMode;

use Fludixx\Woolbattle\Woolbattle;

class DuellTask extends Task {

	public $world;
	public $pl;

	public function __construct(Woolbattle $pl, World $world)
	{
		$this->world = $world;
		$this->pl = $pl;
	}

	public function onRun() : void
	{
		$world = $this->world;
		$players = $world->getPlayers();
		$inWorld = NULL;
		foreach($players as $player) {
			if($player->getGamemode() === GameMode::SURVIVAL) {
				$inWorld++;
			}
		}
		foreach($players as $player) {
			if ($player->getGamemode() === GameMode::SURVIVAL) {
				$lifes = $this->pl->players[$player->getName()]["lifes"];
				$oplayername = $this->pl->players[$player->getName()]["ms"];
				$olifes = $this->pl->players[$oplayername]["lifes"];
				if ($lifes < 0) {
					$oplayer = $this->pl->getServer()->getPlayer($oplayername);
					mt_srand(ip2long($player->getAddress()) + time());
					$elo = mt_rand(1, 45);
					$player->sendMessage($this->pl::PREFIX . "You lost aginst {$oplayer->getName()}! " . f::RED . " - $elo ELO");
					$c = new Config($this->pl->playercfg . $player->getName()
						. $this->pl->endings[$this->pl->configtype], $this->pl->configtype);
					$c->set("elo", (int)$c->get("elo") - $elo);
					$c->save();
					$oplayer->sendMessage($this->pl::PREFIX . "You won aginst {$player->getName()}! " . f::GREEN . " + $elo ELO");
					$c = new Config($this->pl->playercfg . $oplayer->getName()
						. $this->pl->endings[$this->pl->configtype], $this->pl->configtype);
					$c->set("elo", (int)$c->get("elo") + $elo);
					$c->save();
					$player->teleport($this->pl->getServer()->getDefaultLevel()->getSafeSpawn());
					$oplayer->teleport($this->pl->getServer()->getDefaultLevel()->getSafeSpawn());
					foreach($players as $spec) {
						if($spec->getGamemode() != 0) {
							$spec->setGamemode(0);
							$spec->teleport($this->pl->getServer()->getDefaultLevel()->getSafeSpawn());
						}
					}
					$level->unload();
					$id = (int)filter_var($level->getFolderName(), FILTER_SANITIZE_NUMBER_INT);
					$this->pl->resetArena($id);
					$this->pl->PlayerResetArray($player);
					$this->pl->PlayerResetArray($oplayer);
					$this->pl->getLobbyItems($player);
					$this->pl->getLobbyItems($oplayer);
					$this->pl->getScheduler()->cancelTask($this->getTaskId());
					return true;
				}
				if ($inLevel == 1) {
					mt_srand(ip2long($player->getAddress()) + time());
					$elo = mt_rand(1, 45);
					$player->sendMessage($this->pl::PREFIX . "Looks like your opponent left the Game!" . f::GREEN . " + $elo ELO");
					$this->pl->PlayerResetArray($player);
					$c = new Config($this->pl->playercfg . $player->getName()
						. $this->pl->endings[$this->pl->configtype], $this->pl->configtype);
					$c->set("elo", (int)$c->get("elo") + $elo);
					$c->save();
					$player->teleport($this->pl->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
					foreach($players as $spec) {
						if($spec->getGamemode() != 0) {
							$spec->setGamemode(0);
							$spec->teleport($this->pl->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
						}
					}
					$level->unload();
					$id = (int)filter_var($level->getFolderName(), FILTER_SANITIZE_NUMBER_INT);
					$this->pl->resetArena($id);
					$this->pl->PlayerResetArray($player);
					$this->pl->getLobbyItems($player);
					$this->pl->getScheduler()->cancelTask($this->getTaskId());
					return true;
				}
				$player->addActionBarMessage(f::GRAY . $player->getName() . f::YELLOW . "($lifes)" . f::WHITE . " vs " . f::GRAY . "$oplayername" . f::YELLOW . "($olifes)");
				$c = new Config($this->pl->playercfg . $player->getName()
					. $this->pl->endings[$this->pl->configtype], $this->pl->configtype);
				if ($c->get("perk") == "enderpearl" and !$player->getInventory()->contains(Item::get(Item::ENDER_PEARL))) {
					$player->getInventory()->addItem(Item::get(Item::ENDER_PEARL)->setCustomName(f::LIGHT_PURPLE . "Enderpearl"));
				}
				if ($c->get("perk") == "switcher" and !$player->getInventory()->contains(Item::get(Item::SNOWBALL))) {
					$player->getInventory()->addItem(Item::get(Item::SNOWBALL)->setCustomName(f::YELLOW . "Switcher"));
				}
			}
		}
	}

}
