<?php

namespace items;

use pocketmine\player\Player;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\item\VanillaItems;

class Items extends PluginBase implements Listener {
    use SingletonTrait;
    
    private array $cooldowns = [
        'Reverserd Ninja' => []
    ];
    
    protected function onLoad(): void {
        self::setInstance($this);
    }
    
    public function onEnable(): void {
        $this->saveResource("config.yml");
        $this->getServer()->getCommandMap()->register('HCF', new class($this) extends Command {
            public function __construct() {
                parent::__construct('items', 'New Partner Items by ClouderIDev');
                $this->setPermission('items.command');
            }
            
            public function execute(CommandSender $sender, string $commandLabel, array $args) : void {
                if (!$this->testPermission($sender)) return;
                
                if (count($args) < 1) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /items help'));
                    return;
                }
                
                switch (strtolower($args[0])) {
                        case 'give':
                          $reversedNinja = VanillaItems::NETHER_STAR();
                          $reversedNinja->setCustomName(TextFormat::colorize($this->getConfig()->get("reversedninja-name")));
                          $reversedNinja->setCount((int) 64);
                        
                          $lore = $this->getConfig()->get("reversedninja-lore");
                          $reversedNinja->setLore(array_map(fn(string $lore) => TextFormat::colorize($lore), $lore));
                          $reversedNinja->getNamedTag()->setInt("reversedninja", 1);
                          if ($sender->getInventory()->canAddItem($reversedNinja)) {
                              $sender->getInventory()->addItem($reversedNinja);
                          } else {
                              $sender->dropItem($reversedNinja);
                          }
                          break;
                }
            }
        });
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function inCooldown(string $type, string $player): bool {
        if (isset($this->cooldowns[$type]) && isset($this->cooldowns[$type][$player])) {
            return $this->cooldowns[$type][$player] > time();
        }
        return false;
    }
    
    public function getCooldown(string $type, string $player): int {
        return $this->cooldowns[$type][$player] - time();
    }
    
    public function addCooldown(string $type, string $player, int $time): void {
        $this->cooldowns[$type][$player] = time() + $time;
    }
    
    public function removeCooldown(string $type, string $player): void {
        $this->cooldowns[$type][$player] = null;
    }
    
    public function onItemUse(PlayerItemUseEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $ability = 'Reversed Ninja';
        $config = $this->getConfig();
        
        if ($item->getNamedTag()->getTag("reversedninja") !== null) {
                  if ($this->inCooldown($ability, $player->getName())) {
                      $cooldown = ($this->getCooldown($ability, $player->getName()));
                      if ($cooldown > 106) {
                          $player->sendTip("§3§l" . $ability . " §r§4█████████§7█ §7" . $cooldown . " seconds");
                      }
                      if ($cooldown >= 82 && $cooldown <= 106) {
                          $player->sendTip("§3§l" . $ability . " §r§c████████§7██ §7" . $cooldown . " seconds");
                      }
                      if ($cooldown >= 57 && $cooldown <= 81) {
                          $player->sendTip("§3§l" . $ability . " §r§6██████§7████ §7" . $cooldown . " seconds");
                      }
                      if ($cooldown >= 32 && $cooldown <= 56) {
                          $player->sendTip("§3§l" . $ability . " §r§e████§7██████ §7" . $cooldown . " seconds");
                      }
                      if ($cooldown >= 0 && $cooldown <= 31) {
                          $player->sendTip("§3§l" . $ability . " §r§a██§7████████ §7" . $cooldown . " seconds");
                      }
                      return;
                  }
                 if ($player->getLastDamageCause() === null) {
                      $player->sendMessage("§r§f» §cYou tried using the Reversed Ninja but we couldn't find the player.");
                      return;
                  }
                      $cause = $player->getLastDamageCause();
                      if ($cause === null) {
                          $player->sendMessage("§r§f» §cYou tried using the Reversed Ninja but we couldn't find the player.");
                          return;
                      }
                      if (!$cause instanceof EntityDamageByEntityEvent) {
                          $player->sendMessage("§r§f» §cYou tried using the Reversed Ninja but we couldn't find the player."); 
                          return;
                      }
                      $damager = $cause->getDamager();
                      if (!$damager instanceof Player) {
                          $player->sendMessage("§r§f» §cYou tried using the Reversed Ninja but we couldn't find the player.");
                          return;
                      }
                      if ($cause === null) {
                          $player->sendMessage("§r§f» §cYou tried using the Reversed Ninja but we couldn't find the player.");
                          return;
                      }
                      $damager->sendMessage("§l§cWARNING - You will be teleported!");
                      $damager->sendMessage('§r§f» §cYour enemy used the Reversed Ninja.');
                      $damager->sendMessage('§r§f» §cYou will be Teleported in 3 seconds.');
                      $item->pop();
                      $player->getInventory()->setItemInHand($item);
                      $this->addCooldown($ability, $player->getName(), $config->get("reversedninja-cooldown"));
                      $player->sendMessage("§r§f» §6You have used the §6§lReversed Ninja");
                      $player->sendMessage("§r§f» §6You now have a §l§f" . $config->get("reversedninja-cooldown") . " seconds §r§6cooldown");
                      $this->getScheduler()->scheduleRepeatingTask(new ReversedNinjaTask($damager, $player), 20);
        }      
    }
}
