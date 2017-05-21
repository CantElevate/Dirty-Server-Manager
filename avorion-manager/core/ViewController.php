<?php
class ViewController extends CommonController{
  public $Data;

  function __construct(){
    parent::__construct();

    $this->Data['ERROR'] = array();
    if(!is_file($this->Config['ManagerConfig'])){
      $this->Data['ERROR'][] = 'Config Option, ManagerConfig: "'.$this->Config['ManagerConfig'].'" Is not a valid file path.';
    }
    if(!is_file($this->Config['Manager'])){
      $this->Data['ERROR'][] = 'Config Option, Manager: "'.$this->Config['Manager'].'" Is not a valid file path.';
    }
    if(!is_dir($this->Config['LogsDir'])){
      $this->Data['ERROR'][] = 'Config Option, LogsDir: "'.$this->Config['LogsDir'].'" Is not a valid directory path.';
    }
    if(!is_dir($this->Config['StatusBannerDir'])){
      $this->Data['ERROR'][] = 'Config Option, StatusBannerDir: "'.$this->Config['StatusBannerDir'].'" Is not a valid directory path.';
    }
    if(!is_file($this->Config['ConsoleLog'])){
      $this->Data['ERROR'][] = 'Config Option, ConsoleLog: "'.$this->Config['ConsoleLog'].'" Is not a valid file path.';
    }
  }

  private function LoadView($View){
    $Data = $this->Data;
    $this->LogMessage('Loading page: '.$View);
    include __DIR__.'/../views/'.$View.'.php';
  }
  public function Index(){
    $IPAddress = exec("hostname -I | awk '{print $1}'");
    $this->Data['ConsoleAccess'] = 'Disabled';
    $this->Data['AccessServerConfigPage'] = 'Disabled';
    $this->Data['UserManagmentAccess'] = 'Disabled';
    $this->Data['AccessPlayerPage'] = 'Disabled';
    $this->Data['AccessFactionsPage'] = 'Disabled';
    $this->Data['AccessGraphsPage'] = 'Disabled';
    $this->Data['AccessDiscoveredMapPage'] = 'Disabled';
    $this->Data['AccessFactionsMapPage'] = 'Disabled';
    if($this->RoleAccess($this->Config['AccessConsolePage'])){//Role required for specific feature
      $this->Data['ConsoleAccess'] = '';
    }
    if($this->RoleAccess($this->Config['AccessServerConfigPage'])){//Role required for specific feature
      $this->Data['AccessServerConfigPage'] = '';
    }
    if($this->RoleAccess($this->Config['AccessUserManagmentPage'])){//Role required for specific feature
      $this->Data['UserManagmentAccess'] = '';
    }
    if($this->RoleAccess($this->Config['AccessPlayerPage'])){//Role required for specific feature
      $this->Data['AccessPlayerPage'] = '';
    }
    if($this->RoleAccess($this->Config['AccessFactionsPage'])){//Role required for specific feature
      $this->Data['AccessFactionsPage'] = '';
    }
    if($this->RoleAccess($this->Config['AccessGraphsPage'])){//Role required for specific feature
      $this->Data['AccessGraphsPage'] = '';
    }
    if($this->RoleAccess($this->Config['AccessDiscoveredMapPage'])){//Role required for specific feature
      $this->Data['AccessDiscoveredMapPage'] = '';
    }
    if($this->RoleAccess($this->Config['AccessFactionsMapPage'])){//Role required for specific feature
      $this->Data['AccessFactionsMapPage'] = '';
    }

    $this->Data['Username'] = '';
    $this->Data['LoggedIn'] = false;
    $this->Data['LoggedInClass'] = 'Disabled';
    $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
    if($this->SessionLoggedIn()) {
      $this->Data['LoggedInClass'] = '';
      $this->Data['LoggedIn'] = true;
      $this->LogMessage('Accessed Web Interface with valid session.');
      $this->Data['Username'] = 'Logged in as: <span>'.$_SESSION['username'].'</span>';
    }elseif ($cookie) {
      require_once __DIR__ . '/AccountModel.php';
      $AccountModel = new AccountModel;
      if($AccountModel->CheckCookie($cookie)){
        $this->Data['Username'] = 'Logged in as: <span>'.$_SESSION['username'].'</span>';
        $this->Data['LoggedInClass'] = '';
        $this->Data['LoggedIn'] = true;
      }
    }
    $this->Data['IPAddress'] = exec("hostname -I | awk '{print $1}'");

    $this->LoadView('index');
  }
  public function About(){
    $AvailableVersion = `wget -O - -o /dev/null https://api.github.com/repos/dirtyredz/Dirty-Server-Manager/releases/latest | grep tag_name | sed -e 's/.*://g' -e 's/"//g' -e 's/,//g' | tr -d '[:blank:]'`;
    $InstalledVersion = `grep VERSION {$this->Config['Manager']} | head -n1 | sed -e 's/.*=//g'`;
    $this->Data['Version'] = $InstalledVersion;
    if($InstalledVersion != $AvailableVersion){
      $this->Data['UpToDate'] = 'Dirty Server Manager is not up to date!';
    }else{
      $this->Data['UpToDate'] = 'Dirty Server Manager up to date!';
    }

    $this->LoadView('About');
  }
  public function Account(){
    $this->Data['IPAddress'] = exec("hostname -I | awk '{print $1}'");
    $this->SessionRequired();
    $this->LoadView('Account');
  }
  public function ServerConfig(){
    $this->SessionRequired();
    $this->RoleRequired($this->Config['AccessServerConfigPage']);//Role required to view page

    require_once  __DIR__ .'/ServerConfigController.php';
    $ServerConfigController = new ServerConfigController();

    $this->Data['ServerINI'] = $ServerConfigController->GetServerINI();
    $this->Data['ServerINIDetails'] = array(
      array('name' => 'Seed',
            'Definition' => 'seed of the server',
            'Type' => 'input'),
      array('name' => 'Difficulity',
            'Definition' => 'difficulty of the server, allowed values are: -3, -2, -1, 0, 1, 2, 3 Default: 0',
            'Type' => 'select',
            'Values' => array('Beginner'=>'-3','Easy'=>'-2','Normal'=>'-1','Veteran'=>'0','Difficult'=>'1','Hard'=>'2','Insane'=>'3')),
      array('name' => 'InfiniteResources',
            'Definition' => 'enable infinite resources for all players',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'CollisionDamage',
            'Definition' => 'amount of damage done to an object on collision, from 0 to 1. 0: no damage, 1: full damage. default: 1',
            'Type' => 'input'),
      array('name' => 'BigWreckageDespawnTime',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'SmallWreckageDespawnTime',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'LootDiminishingFactor',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'ResourceDropChance',
            'Definition' => 'Effect unknown (chance of resources dropping from destroyed wreckage pieces?)',
            'Type' => 'input'),
      array('name' => 'TurretDropChanceFromTurret',
            'Definition' => 'The chance that a turret will drop from an NPC space craft when the turret is destroyed',
            'Type' => 'input'),
      array('name' => 'TurretDropChanceFromCraft',
            'Definition' => 'The chance that a turret will drop from an NPC space craft when the craft is destroyed',
            'Type' => 'input'),
      array('name' => 'TurretDropChanceFromBlock',
            'Definition' => 'The chance that a turret will drop from a block of wreckage when it is destroyed',
            'Type' => 'input'),
      array('name' => 'SystemDropChanceFromCraft',
            'Definition' => 'The chance that a ship system will drop from an NPC space craft when the craft is destroyed	',
            'Type' => 'input'),
      array('name' => 'SystemDropChanceFromBlock',
            'Definition' => 'The chance that a ship system will drop from a block of wreckage when it is destroyed',
            'Type' => 'input'),
      array('name' => 'ColorDropChanceFromCraft',
            'Definition' => 'The chance that a color will drop from a space craft when the craft is destroyed',
            'Type' => 'input'),
      array('name' => 'ColorDropChanceFromBlock',
            'Definition' => 'The chance that a color will drop from a block of wreckage when it is destroyed',
            'Type' => 'input'),
      array('name' => 'sameStartSector',
            'Definition' => 'Indicates if all players should start in the same sector. If false, a random empty sector on the outer rim is populated and used as the home sector for each new player.',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'startUpScript',
            'Definition' => 'Specifies a Lua script to run on server startup.',
            'Type' => 'input'),
      array('name' => 'startSectorScript',
            'Definition' => 'Specifies a Lua script to run when generating a start sector for a player.',
            'Type' => 'input'),
      array('name' => 'saveInterval',
            'Definition' => 'The time between server saves, in seconds.',
            'Type' => 'input'),
      array('name' => 'sectorUpdateTimeLimit',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'emptySectorUpdateInterval',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'workerThreads',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'generatorThreads',
            'Definition' => 'Effect unknown.',
            'Type' => 'input'),
      array('name' => 'weakUpdate',
            'Definition' => 'Effect unknown.',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'immediateWriteout',
            'Definition' => 'Effect unknown. (Presumably changes behaviour of writing to the log file.)',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'profiling',
            'Definition' => 'Effect unknown.',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'port',
            'Definition' => 'The default port to access the server on. Does not affect the TCP/UDP game traffic port or the query ports.',
            'Type' => 'input'),
      array('name' => 'broadcastInterval',
            'Definition' => 'The time between server activity broadcasts (in seconds?)',
            'Type' => 'input'),
      array('name' => 'isPublic',
            'Definition' => 'indicate if the server should allow other players to join',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'isListed',
            'Definition' => 'indicate if the server should show up on public server lists',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'isAuthenticated',
            'Definition' => 'Effect unknown. (Presumably identical to the ingame setting "Authenticate Users" which toggles steam authentication)',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'useSteam',
            'Definition' => 'Determines whether the server can be joined via Steam, using options like "join game".',
            'Type' => 'select',
            'Values' => array('True'=>'true','False'=>'false')),
      array('name' => 'maxPlayers',
            'Definition' => 'The max number of players allowed on the server at one time',
            'Type' => 'input'),
      array('name' => 'name',
            'Definition' => 'The name of the server, shown in the server list.',
            'Type' => 'input'),
      array('name' => 'description',
            'Definition' => 'A description for the server, shown in the server list.',
            'Type' => 'input'),
      array('name' => 'password',
            'Definition' => 'Password requirment for the server.',
            'Type' => 'input'),
      array('name' => 'accessListMode',
            'Definition' => 'Determines whether the server uses a blacklist or a whitelist to restrict access.',
            'Type' => 'select',
            'Values' => array('Blacklist'=>'Blacklist','Whitelist'=>'Whitelist')),
    );
    $this->Data['ManagerConfig'] = $ServerConfigController->GetManagerConfig();
    $this->Data['PHPConfig'] = $this->Config;

    $this->LoadView('ServerConfig');
  }
  public function Console(){
    $this->SessionRequired();
    $this->RoleRequired($this->Config['AccessConsolePage']);//Role required to view page
    if($this->RoleAccess($this->Config['ConsoleCommandsAccess'])){//Role required for specific feature
      $this->LogMessage('Extra Console Access Granted.');
      $this->Data['AccessGranted'] = true;
    }else{
      $this->Data['AccessGranted'] = false;
    }
    $this->LoadView('Console');
  }
  public function DiscoveredMap(){
    $this->RoleRequired($this->Config['AccessDiscoveredMapPage']);//Role required to view page
    include __DIR__ ."/../SectorData.php";
    $this->Data['SectorData'] = json_encode( $SectorData );
    $this->LoadView('DiscoveredMap');
  }
  public function Factions(){
    $this->RoleRequired($this->Config['AccessFactionsPage']);//Role required to view page
    include __DIR__ ."/../SectorData.php";
    $this->Data['SectorData'] = $SectorData;
    $this->LoadView('Factions');
  }
  public function FactionsMap(){
    $this->RoleRequired($this->Config['AccessFactionsMapPage']);//Role required to view page
    include __DIR__ ."/../SectorData.php";
    $this->Data['SectorData'] = json_encode( $SectorData );
    $this->LoadView('FactionsMap');
  }
  public function Graphs(){
    $this->RoleRequired($this->Config['AccessGraphsPage']);//Role required to view page
    $this->Data['ServerLoadGraph'] = false;
    $this->Data['OnlinePlayersGraph'] = false;
    $this->Data['InMemoryGraph'] = false;
    $this->Data['UpdatesGraph'] = false;
    $this->Data['CpuUsageGraph'] = false;

    if($this->RoleAccess($this->Config['ServerLoadGraph'])){//Role required for specific feature
      $this->Data['ServerLoadGraph'] = true;
    }
    if($this->RoleAccess($this->Config['OnlinePlayersGraph'])){//Role required for specific feature
      $this->Data['OnlinePlayersGraph'] = true;
    }
    if($this->RoleAccess($this->Config['InMemoryGraph'])){//Role required for specific feature
      $this->Data['InMemoryGraph'] = true;
    }
    if($this->RoleAccess($this->Config['UpdatesGraph'])){//Role required for specific feature
      $this->Data['UpdatesGraph'] = true;
    }
    if($this->RoleAccess($this->Config['CpuUsageGraph'])){//Role required for specific feature
      $this->Data['CpuUsageGraph'] = true;
    }

    $this->Data['MaxPlayers'] = `grep MAX {$this->Config['ManagerConfig']} | sed -e 's/.*=//g'`;
    $this->LoadView('Graphs');
  }
  public function Home(){
    if($this->RoleAccess($this->Config['HomeChatLog'])){//Role required for specific feature
      $this->Data['ShowChatLog'] = true;
    }else{
      $this->Data['ShowChatLog'] = false;
    }

    if($this->RoleAccess($this->Config['HomePlayerList'])){//Role required for specific feature
      $this->Data['ShowOnlinePlayers'] = true;
    }else{
      $this->Data['ShowOnlinePlayers'] = false;
    }

    if($this->RoleAccess($this->Config['HomeDiskUsage'])){//Role required for specific feature
      $this->Data['ShowDiskUsage'] = true;
    }else{
      $this->Data['ShowDiskUsage'] = false;
    }

    $this->Data['ShowOnlinePlayerCount'] = $this->Config['ShowOnlinePlayerCount'];
    $this->Data['CustomMessageOne'] = $this->Config['HomeCustomMessageOne'];
    $this->Data['CustomMessageTwo'] = $this->Config['HomeCustomMessageTwo'];
    $this->Data['CustomMessageThree'] = $this->Config['HomeCustomMessageThree'];
    $this->Data['CustomMessageFour'] = $this->Config['HomeCustomMessageFour'];
    $this->Data['GalaxyName'] = `grep GALAXY {$this->Config['ManagerConfig']} | sed -e 's/.*=//g'`;
    $this->Data['OnlineStatus'] = `if [ $(pidof $(grep SERVER= {$this->Config['Manager']} | sed -e 's/.*=//g')) > /dev/null ]; then echo 'Online'; else echo 'Offline'; fi  | tr -d '[:space:]'`;
    if($this->Data['ShowDiskUsage']){
      $this->Data['DiskUsage'] = `df -h --total | awk '{print $5}' | tail -n 1 | sed -e 's/%//g'`;
    }
    if($this->Data['ShowOnlinePlayers'] && $this->Data['OnlineStatus'] == "Online"){
      $OnlinePlayers = explode(", ",`tac {$this->Config['ConsoleLog']} | grep 'online players (' | head -n 1 | sed -e 's/.*://g' -e 's/^.//g' -e 's/.$//g'`);
      $NewOnlinePlayers = array();
      $PID = `pidof AvorionServer | tr -d '\n'`;
      $ConnectionList = `awk "/Connection accepted from/,/joined the galaxy/" /proc/"{$PID}"/fd/3 | grep 'accepted\|joined' | sed -e 's/.*> //g' -e 's/ joined.*//g' -e 's/.*from //g' -e 's/:.*//g'`;
      if(strlen($OnlinePlayers[0]) > 1){
        foreach ($OnlinePlayers as $key => $value) {
          $Name = str_replace("\n", '', $value);
          $IP = `echo "{$ConnectionList}" | grep -B1 "{$Name}" | head -n1 | tr -d '\n'`;
          $NewOnlinePlayers[$Name]['IP'] = $IP;
          $GEO = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$IP));
          $NewOnlinePlayers[$Name]['CountryCode'] = strtolower($GEO['geoplugin_countryCode']);
          $NewOnlinePlayers[$Name]['CountryName'] = strtolower($GEO['geoplugin_countryName']);
        }
        $this->Data['OnlinePlayers']  = $NewOnlinePlayers;
      }
    }
    if($this->Config['ShowOnlinePlayerCount']){
      $this->Data['MaxPlayers'] = `grep MAX {$this->Config['ManagerConfig']} | sed -e 's/.*=//g'`;
      $this->Data['OnlinePlayerCount'] = `netstat -tlunp 2> /dev/null | grep -iv ':270'|grep -i avorion|wc -l|tr -d "[:space:]"`;
    }
    $this->Data['IPAddress'] = exec("hostname -I | awk '{print $1}'");
    $this->LoadView('Home');
  }
  public function Players(){
    $this->RoleRequired($this->Config['AccessPlayerPage']);//Role required to view page
    if($this->RoleAccess($this->Config['AccessDetailedPlayerData'])){//Role required for specific feature
      $this->LogMessage('Extra Player Data Granted.');
      $this->Data['AccessGranted'] = true;
    }else{
      $this->Data['AccessGranted'] = false;
    }
    include __DIR__ ."/../PlayerData.php";
    $this->Data['PlayerData'] = $PlayerData;
    $this->LoadView('Players');
  }
  public function SignIn(){
    $this->Data['IPAddress'] = exec("hostname -I | awk '{print $1}'");
    $this->LoadView('SignIn');
  }
  public function UserManagment(){
    $this->SessionRequired();
    $this->RoleRequired($this->Config['AccessUserManagmentPage']);//Role required to view page
    $this->LoadView('UserManagment');
  }
  public function RSS(){
    if($this->Config['EnableRSS']){
      include __DIR__ .'/../core/RefreshModel.php';
      $RefreshModel = new RefreshModel;
      $this->Data['ServerLoad'] = $RefreshModel->GetCurrentServerLoad();
      $this->Data['IPAddress'] = exec("hostname -I | awk '{print $1}'");
      $this->Data['GalaxyName'] = `grep GALAXY {$this->Config['ManagerConfig']} | sed -e 's/.*=//g'`;
      $this->Data['OnlineStatus'] = `if [ $(pidof $(grep SERVER= {$this->Config['Manager']} | sed -e 's/.*=//g')) > /dev/null ]; then echo 'Online'; else echo 'Offline'; fi`;
      $this->Data['ShowOnlinePlayerCount'] = $this->Config['ShowOnlinePlayerCount'];
      if($this->Config['ShowOnlinePlayerCount']){
        $this->Data['MaxPlayers'] = `grep MAX {$this->Config['ManagerConfig']} | sed -e 's/.*=//g'`;
        $this->Data['OnlinePlayerCount'] = `netstat -tlunp 2> /dev/null | grep -iv ':270'|grep -i avorion|wc -l|tr -d "[:space:]"`;
      }
      $this->LoadView('rss');
    }
  }
}