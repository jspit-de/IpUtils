<?php
/**
.---------------------------------------------------------------------------.
|  Software: class IpUtils : Utility Functions for IPv4/Ipv6                |
|   Version: 1.2                                                            |
|      Date: 2021-04-05                                                     |
|       PHP: >= 7.0                                                         |
| ------------------------------------------------------------------------- |
| Copyright © 2019..2021, Peter Junk (alias jspit). All Rights Reserved.    |
' ------------------------------------------------------------------------- '
*/

class IpUtils
{
  //format constants
  const BIN = "BIN";  //string binary "11110101.."
  const DEC = "DEC";  //string decimal for bcmath
  const HEX = "HEX";  //Hex-string with "0x" at first
  const RAW = "RAW";  //string internal bin pton
  const EXP = "EXP";  //expand output 0:0:567...
  const COMP = "COMP"; //Compressed ::567:..
  const FULL = "FULL"; //output 0000:0000:0567:...
  const SUFFIX = "+";  //output with suffix
  
  private $binIp = null;
  private $suffix = null;  //Suffix
  private $ip;

  /**
   * @param mixed $ipInput 
   */
  public function __construct($ipInput)
  {
    if(is_string($ipInput)) {
      //IPv4,IPv6,"decimal","0x_hex"
      $ipsuffix = explode("/",$ipInput);
      $this->ip = $ipsuffix[0];
      if(isset($ipsuffix[1])) $this->suffix = (int)$ipsuffix[1];
      $this->binIp = self::inetPtonFromString($this->ip);
    }
    elseif($ipInput instanceOf self){
      //IpUtils Object
      $stdObj = $ipInput->getDataObj();
      $this->ip = $stdObj->ip;
      $this->suffix = $stdObj->suffix;
      $this->binIp = $stdObj->binIp;
    }
    elseif(is_int($ipInput)){
      //integer only to IPv4
      $this->ip = $ipInput;
      $this->binIp = inet_pton(long2ip($ipInput)); 
    }
    else {
      $msg = "Input could not use for IpUtils.";
      throw new \InvalidArgumentException($msg);
    }
  }

  /**
   * Create Object
   * @param string       $ipInput
   * @return object IpUtils or false with warning if error
   */
  public static function create($ipInput)
  {
    try{
      $ipUtil = new static($ipInput);
    }
    catch (Exception $e) {
      trigger_error(
        'Error Method '.__METHOD__
          .', Message: '.$e->getMessage()
          .' stack trace'.$e->getTraceAsString(),
        E_USER_WARNING
      );
      $ipUtil = false;
    }
    return $ipUtil;
  }

  /**
   * Create Object
   * @param string       $ipInput
   * @return object IpUtils; false if error
   */
  public static function createFromBinString($binString)
  {
    $ip = IpUtils::formatBinIp($binString);
    if($ip === false) return false;
    return new static($ip);
  }
  
  /**
   * setSuffix
   * @param int $suffix
   * @return $this or false if error
   */
  public function setSuffix($suffix)
  {
    $maxsuffix = strlen($this->binIp) == 16 ? 128 : 32;
    if($suffix < 1 OR $suffix > $maxsuffix) return false;
    $this->suffix = $suffix;
    return $this;
  }

  /**
   * setSuffixFromNetMask
   * @param int $suffix
   * @return $this or false if error
   * @throws InvalidArgumentException if $netmask is invalid 
   */
  public function setSuffixFromNetMask($netMask)
  {
    $suffix = self::suffixFromNetMask($netMask);
    if($suffix === false){
      $msg = "Invalid Net Mask '$netMask'";
      throw new \InvalidArgumentException($msg);
    }
    $this->suffix = $suffix;
    return $this;
  }
 
  /**
   * return true if valid Ip V4
   * @return bool;
   */
  public function isIp4()
  {
    return strlen($this->binIp) == 4;
  }

  /**
   * return true if valid Ip V6
   * @return bool;
   */
  public function isIp6()
  {
    return strlen($this->binIp) == 16;
  }

  /**
   * return true if valid Ip (V4 or V6)
   * @return bool;
   */
  public function isIp()
  {
    return $this->isIp4() OR $this->isIp6();
  }

  /**
   * return true Ip is V4 or V6 and may use as Netmask
   * @return bool;
   */
  public function isNetmask()
  {
    if(!$this->isIp()) return false;
    $binNetMask = $this->format(self::BIN);
    return (bool)preg_match('~^1*0*$~',$binNetMask);
  }

  /**
   * return true if $gateWayIp may use or false
   * @param mixed $gateWayIp
   * @return bool;
   */
  public function checkGateway($ip)
  {
    return $this->rangeIntersect($ip) === [$ip,$ip];
  }

  
  /**
   * Format Object
   * @param string $format
   * @return string
   */
  public function format($format = null)
  {
    if($ip = self::formatBinIp($this->binIp, $format)) {
      if($format === null OR strpos($format,"+") !== false) {
        $ip .= $this->suffix !== null ? ("/".$this->suffix) : "";
      }  
      return $ip;
    }
    return false;
  }
      
  /**
   * get suffix 
   * @return int suffix or false if suffix not exists 
   */
  public function suffix()
  {
    return is_int($this->suffix)
      ? $this->suffix
      : false;
  }

  /**
   * return true if suffix set
   * @return bool true or false
   */
  public function hasSuffix()
  {
    return is_int($this->suffix);
  }
  

  /**
   * get Hosts number 
   * @return float number of hosts or false if ip invalid
   */
  public function hosts()
  {
    $bits = strlen($this->binIp) * 8;
    if($bits == 0) return false;
    if($this->suffix === null) return 1.0; 
    $p = $bits - $this->suffix;
    if($p < 0 OR $p > $bits) return false;
    return (float)(2 ** $p);
  }
  

  /**
   * get Net Mask 
   * @param string $format
   * @return string netMask or false if error
   */
  public function netMask($format = null)
  {
    $binMask = self::suffixMask($this->suffix, strlen($this->binIp) * 8);
    if($binMask === false) return false;
    return self::formatBinIp($binMask, $format);
  }

  /**
   * get Net Address 
   * @param string $format
   * @return string netMask
   */
  public function netAdr($format = null)
  {
    $range = $this->range($format);
    return is_array($range) ? $range[0] : false; 
  }

  /**
   * get Default gateway Address
   * @param string $format
   * @return string gateway address Or bool false if error
   */
  public function defaultGateway($format = null)
  {
    $binRange = $this->binRange();
    if($binRange === false) return false;
    //default Gateway = netAdr + 1
    $binDefGateway = self::binStrAddInt($binRange[0], 1);
    return $binDefGateway < $binRange[1] 
      ? self::formatBinIp($binDefGateway,$format)
      : false
    ;
  }


  /**
   * get Broadcast Address 
   * @param string $format
   * @return string netMask
   */
  public function broadcast($format = null)
  {
    $range = $this->range($format);
    return is_array($range) ? $range[1] : false; 
  }
  
  
  /**
   * get range as array with intern bin-ptop-strings
   * @return mixed array [binIpFrom, binIpTo] or
   *  false if intern binIp == false
   */
  public function binRange()
  {
    if(($binIp = $this->binIp) === false) {
      return false; //invalid ip basis
    }
    elseif($this->suffix === null) {
      return [$this->binIp, $this->binIp];
    }
    else {
      $binMask = self::suffixMask($this->suffix, strlen($this->binIp) * 8);
      if($binMask === false) return false;
      return [
        $this->binIp & $binMask,
        $this->binIp | ~$binMask
      ];
    }  
  }

  /**
   * get the range as array 
   * @param string  $format (null,'dec','raw','full')
   * @return array [IpFrom, IpTo], false if error
   */
  public function range($format = null)
  {
    if($binRange = $this->binRange()) {
      return [
        self::formatBinIp($binRange[0], $format),
        self::formatBinIp($binRange[1], $format)
      ];
    }
    return false;
  }

  /**
   * set to minimal subnet with $ip
   * @param mixed $ip
   * @return $this
   */
  public function setMinSubnet($ip)
  {
    $ipData = self::create($ip)->getDataObj();
    $binStrIp2 = $ipData->binIp;
    $ip2Bytes = strlen($binStrIp2);
    if($ip2Bytes == 0 OR $ip2Bytes != strlen($this->binIp)) {
      //Error
      $msg = "Invalid Ip";
      throw new \InvalidArgumentException($msg);
    }
    $binStrX = self::ptonToBin($this->binIp ^ $binStrIp2);
    $bits = strlen($binStrX);
    $minsuffix = $bits - strlen(ltrim($binStrX ,"0")); 
    $binMask = self::suffixMask($minsuffix, $bits);    
    $this->binIp &= $binMask;
    $this->suffix = $minsuffix;
    return $this;
  }

  
  /**
   * get intercect range as array 
   * empty array if not intersect or false if Error
   * @param mixed $ip
   * @param string  $format (null,'dec','raw','full')
   * @return mixed array [IpFrom, IpTo], bool false if error
   */
  public function rangeIntersect($ip, $format = null)
  {
    if($binRange1 = $this->binRange()) {
      if($ip2 = self::create($ip)) {
        if($binRange2 = $ip2->binRange()) {
          $isec0 = max($binRange1[0], $binRange2[0]);
          $isec1 = min($binRange1[1], $binRange2[1]);
          if($isec0 > $isec1) return [];
          return [
            self::formatBinIp($isec0, $format),
            self::formatBinIp($isec1, $format)
          ];
        }
      }
    }
    return false;
  }
  
 /*
  * Add a integer offset to Ip
  * @param int add
  * @return object $this
  */
  public function addOffset($add)
  {
    $this->binIp = self::binStrAddInt($this->binIp, (int)$add);
    return $this;
  }  
  
  /**
   * Returns the textual representation of the IP address.
   * Empty String if Ip invalid
   * @return string
   */
  public function __toString()
  {
    return (string)$this->format();
  }

 /**
  * magic method für debug (var_dump)
  */
  public function __debugInfo() {
    $binIp = is_string($this->binIp) 
      ? '\\x'.implode('\\x',str_split(bin2hex($this->binIp),2))
      : 'null'
    ;
    return [
      'ip' => $this->ip,
      'suffix' => $this->suffix,
      'binIp' => $binIp,
    ];
  }

  /**
   * get Data as StdClass-Object
   * @return object of StdClass
   */
  public function getDataObj(){
    $obj = new stdClass;
    $obj->ip = $this->ip;
    $obj->suffix = $this->suffix;
    $obj->binIp = $this->binIp;
    return $obj;
  }

  
  /**
   * get Inet Pton Bin String
   * @param string       $ip
   * @return string inet_pton;
   */
  public static function inetPtonFromString($ip)
  {
    if(is_string($ip)){
      if(filter_var($ip, FILTER_VALIDATE_IP)) {
        //IPv4 AND IPv6
        return inet_pton($ip);
      }
      elseif(preg_match('~^0x[0-9A-F]{1,32}$~i', $ip)){
        //hex-format "0x00ffcd78"
        return self::hexToPton($ip);
      }
      elseif(ctype_digit($ip)) { 
        //dec bc string 
        return self::decToPton($ip); 
      }
    }
    return false;
  }
  
 /*
  * Remove leading zero in IP
  * @param string IP
  * @return string
  */
  public static function cleanIP($ip) {
    return preg_replace('~(^|[.:])0{1,2}(\d)~','$1$2',$ip);
  }
  

 /*
  * @param $suffixIP ipv6/suffix or ipv4/suffix
  * return array("Start-IP","End-IP") or false if error
  */
  public static function ipsuffixRange($suffixIP)
  {
    list($ip, $suffix) = explode('/', $suffixIP);
    $ipBin = inet_pton($ip);
    if($ipBin === false) {
      return false;
    }
    //subnet-mask
    $strMask = self::strMaskFromsuffix($suffix, strlen($ipBin)*8);
    if($strMask === false) return false;
 
    return [
      inet_ntop($ipBin & $strMask),
      inet_ntop($ipBin | ~$strMask)
    ];
  }
  
 
  /**
   * Format internal binIp
   * @param string  $binIp
   * @param string  $format (null,'dec','raw','full')
   * @return string if ok, false if error
   */
  public static function formatBinIp($binIp, $format = null)
  {
    if(!is_string($binIp)) return false;
    
    if($format === null OR stripos($format, SELF::COMP) !== false) {
      return inet_ntop($binIp);
    }

    elseif(stripos($format, SELF::BIN) !== false) {
      //binary string 
      return self::ptonToBin($binIp);
    }
    
    elseif(stripos($format, SELF::DEC) !== false) {
      //decimal
      return self::ptonToDec($binIp);
    }

    elseif(stripos($format, SELF::HEX) !== false) {
      //hexadecimal
      $strHex = ltrim(bin2hex($binIp),"0");
      if(strlen($binIp) == 16) {
        //add leading zeros for IPv6        
        $strHex = str_pad($strHex,9,"0",STR_PAD_LEFT);
      }
      return "0x".$strHex;
    }

    elseif(stripos($format, SELF::RAW) !== false) {
      //bin stream 
      return $binIp;
    }

    elseif(stripos($format, SELF::EXP) !== false) {
      if(strlen($binIp) == 4){ //v4
        //without leading 0, because with leading 0 is invalid
        return inet_ntop($binIp);      
      }
      elseif(strlen($binIp) == 16){
        //v6 as xxxx:xxxx: .. without leading 0
        return implode(":", array_map(
          function($word){return sprintf("%x", $word);},
          unpack('n*', $binIp)) //words
        );
      }
    } 

    elseif(stripos($format, SELF::FULL) !== false) {
      if(strlen($binIp) == 4){ //v4
        //without leading 0, because with leading 0 is invalid
        return inet_ntop($binIp);      
      }
      elseif(strlen($binIp) == 16){
        //v6 as xxxx:xxxx: ..
        return implode(":", array_map(
          function($word){return sprintf("%04x", $word);},
          unpack('n*', $binIp)) //words
        );
      }
    }        
    
    return false;
  }
      
 /**
  * create a binary string mask from suffix
  * @param int $suffix range 1..bitBasis
  * @param int $bitBasis, must be a multiple of 8, default 128
  * @return string as packed inet_pton in_addr representation
  */
  public static function suffixMask($suffix, $bitBasis = 128)
  {
    if($suffix < 0 OR $suffix > $bitBasis OR $suffix === null ) {
      return false;
    }
    $zeroBits = $bitBasis - $suffix;
    $restBits = $zeroBits % 8;
    $strMask = $restBits ? chr(0xff << $restBits) : "";
    $strMask .= str_repeat("\x00",(int)($zeroBits/8));
    return str_pad($strMask,(int)($bitBasis/8),"\xff",STR_PAD_LEFT);
  }
  
  //inet_pton String to BCMath decimal string
  public static function ptonToDec($ptonBinStr)
  {
    $arrInt16 = unpack("n*", $ptonBinStr);
    
    foreach($arrInt16 as $i => $value){
      $bc = ($i == 1) ? $value : bcadd(bcmul($bc,"65536"),$value);
    }

    return $bc;
  }

  //BCMath decimal string to inet_pton String 
  public static function decToPton($dec)
  {
    for($bin = "", $i=0; $dec != "0" AND $i < 8; ++$i){
      $bin = pack("n",(int)bcmod($dec,"65536")).$bin;
      $dec = bcdiv( $dec, "65536", 0 );    
    }
    return str_pad($bin,16,"\x00",STR_PAD_LEFT);
  }
  
  //inet_pton String to binary string
  public static function ptonToBin($ptonBinStr)
  {
    $arrInt16 = unpack("n*", $ptonBinStr);
    
    $binstr = "";
    foreach($arrInt16 as $i => $value){
      $binstr .= sprintf("%016b",$value);  
    }
    return $binstr;
  }
  

 /** 
  * hex string to inet_pton String 
  * @param string how "0cde67ff"
  * @return string pton byte straem 4 or 16 Byte
  *  if input-len <= 8 result is for IPv4, else IPv6
  *  return false if error  
  */
  public static function hexToPton($strHex)
  {
    if(stripos($strHex,"0x") === 0) $strHex = substr($strHex,2);
    $lenForPton = strlen($strHex) <= 8 ? 8 : 32;
    $strHex = str_pad($strHex,$lenForPton,"0",STR_PAD_LEFT);
    return pack('H*',$strHex);
  }

  
 /** 
  * Add int offset to ip-pton-string
  * @param string binStr    ("\x00\x00\x01\xff")
  * @param int add          ( 1 )
  * @return string          ("\x00\x00\x02\x00")
  *  return false if error  
  */
  public static function binStrAddInt($binStr, $add){
    $bytesBasis = strlen($binStr); //4 or 16
    $binStrAdd = pack((PHP_INT_SIZE == 4 ? "N" : "J"),abs((int)$add)); //4 or 8 Byte
    $binStrAdd = ltrim($binStrAdd,"\x00");
    $binStrSum = $add >= 0 
      ? self::binStrAdd($binStr, $binStrAdd)
      : self::binStrSub($binStr, $binStrAdd)
    ;
    $bytesStrSum = strlen($binStrSum);
    return $bytesStrSum == $bytesBasis ? $binStrSum : false;
  }
  
 /** 
  * Add ip-pton-strings
  * @param string binStr    ("\x00\x00\x01\xff")
  * @param string binStrAdd ("\x00\x00\x00\x01")
  * @return string          ("\x00\x00\x02\x00")
  *  return false if error  
  */
  public static function binStrAdd($binStr, $binStrAdd){
    $len = max(strlen($binStr),strlen($binStrAdd));
    $binStr = str_pad($binStr,$len,"\x00",STR_PAD_LEFT);
    $binStrAdd = str_pad($binStrAdd,$len,"\x00",STR_PAD_LEFT);
    
    $overflow = 0;
    for($i=$len-1; $i >= 0; $i--){
      $iByte = ord($binStr[$i]) + ord($binStrAdd[$i]) + $overflow;
      $binStr[$i] = chr($iByte % 256);
      $overflow = (int)($iByte/256);
    }
    return $overflow ? false : $binStr;
  }

 /** 
  * Sub ip-pton-strings
  * @param string binStr    ("\x00\x00\x01\xff")
  * @param string binStrSub ("\x00\x00\x00\x01")
  * @return string          ("\x00\x00\x02\x00")
  *  return false if error  
  */
  public static function binStrSub($binStr, $binStrSub){
    $len = max(strlen($binStr),strlen($binStrSub));
    $binStr = str_pad($binStr,$len,"\x00",STR_PAD_LEFT);
    $binStrSub = str_pad($binStrSub,$len,"\x00",STR_PAD_LEFT);
    
    $overflow = 0;
    for($i=$len-1; $i >= 0; $i--){
      $iByte = ord($binStr[$i]) - ord($binStrSub[$i]) - $overflow;
      $overflow = 0;
      if($iByte < 0) {
        $iByte += 256;
        $overflow = 1;
      }
      $binStr[$i] = chr($iByte);
    }
    return $overflow ? false : $binStr;
  }
  
  /**
   * setSuffixFromNetMask
   * @param String $netMask how '255.255.255.0'
   * @return int suffix (24 for '255.255.255.0') or bool false if error
   */
  public static function suffixFromNetMask($netMask)
  {
    $netMaskObj = self::create($netMask);
    if(!$netMaskObj OR !$netMaskObj->isNetmask()) {
      return false;
    }
    $binNetMask = $netMaskObj->format('BIN');
    $suffix = strlen($binNetMask) - substr_count($binNetMask,'0');
    return $suffix;
  }

}