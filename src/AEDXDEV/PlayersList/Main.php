<?php

/*
by AEDXDV

Youtube: @AEDXDEV
Discord: aedxdev

*/
namespace AEDXDEV\PlayersList;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Embed\Field;
use JaxkDev\DiscordBot\Models\Messages\Embed\Footer;
use JaxkDev\DiscordBot\Plugin\Events\MessageSent;
use JaxkDev\DiscordBot\Plugin\Api;
use JaxkDev\DiscordBot\Plugin\Main as DiscordBot;
use JaxkDev\DiscordBot\Plugin\ApiResultion;
use JaxkDev\DiscordBot\Plugin\ApiRejection;
use JaxkDev\DiscordBot\Models\Messages\Message;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

class Main extends PluginBase implements Listener{
  
  public DiscordBot $discord;
  
  public Config $config;
  
  public function onEnable() : void{
    $discord = $this->getServer()->getPluginManager()->getPlugin("DiscordBot");
    if (!$discord instanceof DiscordBot){
      throw new PluginException("Incompatible dependency 'DiscordBot' detected, see https://github.com/DiscordBot-PMMP/DiscordBot/releases for the correct plugin.");
    }
    eval(base64_decode("aWYoKCRwaGFyID0gXFBoYXI6OnJ1bm5pbmcoKSkgPT09ICIiKXsKICAgICAgdGhyb3cgbmV3IFBsdWdpbkV4Y2VwdGlvbigiQ2Fubm90IGJlIHJ1biBmcm9tIHNvdXJjZS4iKTsKICAgIH0KICAgICRkID0gJHRoaXMtPmdldERlc2NyaXB0aW9uKCk7CiAgICBpZiAoJGQtPmdldEF1dGhvcnMoKVswXSAhPT0gIkFFRFhERVYiIHx8ICRkLT5nZXROYW1lKCkgIT09ICJQbGF5ZXJzTGlzdCIpIHsKICAgICAgdGhyb3cgbmV3IFBsdWdpbkV4Y2VwdGlvbigiSXRzIGEgYmFkIGlkZWEgdG8gc3RlYWwgdGhlIHBsdWdpbiA6XSIpOwogICAgfQ"));
    $this->discord = $discord;
    $this->config = new Config($this->getDataFolder() . "config.yml", 2, [
      "ServerId" => 1234567890
    ]);
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function MessageSent(MessageSent $event){
    $api = $this->discord->getApi();
    $message = $event->getMessage();
    $content = $message->getContent();
    $channel_id = $message->getChannelId();
    $args = explode(" ", $content);
    // command
    if(($args[0] ?? "") == "!list"){
      $onlines = $this->getServer()->getOnlinePlayers();
      $players = implode("\n", array_map(fn(Player $player) => $player->getName(), $onlines));
      if (($server_id = $this->config->get("ServerId", 1234567890)) == 1234567890){
        $this->getLogger()->info("§cPlease add discord server id in config.yml");
        $this->getServer()->getPluginManager()->disablePlugin($this);
        return false;
      }
      $api->sendMessage($server_id, $channel_id, null, $message->getId(), [new Embed(
        "List Players",
        count($onlines) === 0 ? "There are no players in the server" : count($onlines) . "/" . $this->getServer()->getMaxPlayers(),
        null, time(), null,
        new Footer("List Players v" . $this->getDescription()->getVersion()),
        null, null, null, null, null,
        count($onlines) === 0 ? [] : [new Field("Players", $players, true)]
      )])->otherwise(function(ApiRejection $rejection){
        $this->getLogger()->error("Failed to send command response: " . $rejection->getMessage());
      });
    }
  }
}
