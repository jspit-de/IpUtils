# IpUtils

IPv4 and IPv6 utility class

## Usage

### Input and Output

```php
require '/yourPath/IpUtils.php';  //or Autoload

$ip = IpUtils::create('192.168.178.10/24');
echo $ip->netMask();  //255.255.255.0
```

Supported Input Formats:

- IPv4 string 192.168.178.56
- IPv4 string with suffix 192.168.178.56/24
- IPv6 string ::8f56:1
- IPv6 string with suffix ::8f56:1/124
- integer (only for IPv4, for Suffix use setSuffix())
- Dec-String "123" (only for IPv6)
- Hex-String "0xf" (IPv4, IPv6 if more than 10 chars ""0x00000000f")

For Outputs use format()
```php
echo IpUtils::create('::1')->format('exp');  //0:0:0:0:0:0:0:1
echo IpUtils::create('178.114.56.1/24')->format('hex');  //0xb2723801
echo IpUtils::create('::8f56:1/120')->format('exp+'); //0:0:0:0:0:0:8f56:1/120
```
Supported Output Formats:

- "BIN" : string binary "11110101.."
- "DEC" : decimal string (may use for for bcmath)
- "HEX" : Hex-String with "0x" at first
- "RAW" : internal Bin-Pton-String (do not use with echo)
- "EXP" : expand output 0:0:567...
- "COMP": Compressed ::567:..
- "FULL"; Output 0000:0000:0567:...

With "+" the Suffix will add

### Range and Range-Intersect

```php
$range = IpUtils::create("192.168.115.110/24")
  ->range(); 
var_dump($range);  
//array(2) { [0]=> string(13) "192.168.115.0" [1]=> string(15) "192.168.115.255" }
```

```php
$ip1 = "192.168.115.110/24"; 
$ip2 = "192.168.115.65/26"; 
$range = IpUtils::create($ip1)
  ->rangeIntersect($ip2);

var_dump($range); 
//array(2) { [0]=> string(14) "192.168.115.64" [1]=> string(15) "192.168.115.127" }
```

## Class-Info

| Info | Value |
| :--- | :---- |
| Declaration | class IpUtils |
| Datei | IpUtils.php |
| Date/Time modify File | 2019-04-26 13:25:48 |
| File-Size | 15 KByte |
| MD5 File | a23ad83f3a7a431101fe6cde13717a30 |
| Version | 1.0 |
| Date | 2019-04-26 |

## Public Methods

| Methods and Parameter | Description/Comments |
| :-------------------- | :------------------- |
| public function __construct($ipInput) | @param mixed $ipInput  |
| public static function create($ipInput) | Create Object<br>@param string $ipInput<br>@return object IpUtils; |
| public function setSuffix($suffix) | setSuffix<br>@param int $suffix<br>@return $this or false if error |
| public function isIp4() | return true if valid Ip V4<br>@return bool; |
| public function isIp6() | return true if valid Ip V6<br>@return bool; |
| public function isIp() | return true if valid Ip (V4 or V6)<br>@return bool; |
| public function format($format = null) | Format Object<br>@param string $format<br>@return string |
| public function suffix() | get suffix <br>@return int suffix or false if suffix not exists  |
| public function hosts() | get Hosts number <br>@return float number of hosts or false if ip invalid |
| public function netMask($format = null) | get Net Mask <br>@param string $format<br>@return string netMask |
| public function netAdr($format = null) | get Net Address <br>@param string $format<br>@return string netMask |
| public function broadcast($format = null) | get Broadcast Address <br>@param string $format<br>@return string netMask |
| public function binRange() | get range as array with intern bin-ptop-strings<br>@return mixed array [binIpFrom, binIpTo] or<br>false if intern binIp == false |
| public function range($format = null) | get the range as array <br>@param string $format (null,&#039;dec&#039;,&#039;raw&#039;,&#039;full&#039;)<br>@return array [IpFrom, IpTo], false if error |
| public function setMinSubnet($ip) | set to minimal subnet with $ip<br>@param mixed $ip<br>@return $this |
| public function rangeIntersect($ip, $format = null) | get intercect range as array <br>empty array if not intersect or false if Error<br>@param mixed $ip<br>@param string $format (null,&#039;dec&#039;,&#039;raw&#039;,&#039;full&#039;)<br>@return mixed array [IpFrom, IpTo], bool |
| public function addOffset($add) | Add a integer offset to Ip<br>@param int add<br>@return object $this |
| public function __toString() | Returns the textual representation of the IP address.<br>Empty String if Ip invalid<br>@return string |
| public function getDataObj() | get Data as StdClass-Object<br>@return object of StdClass |
| public static function inetPtonFromString($ip) | get Inet Pton Bin String<br>@param string $ip<br>@return string inet_pton; |
| public static function cleanIP($ip) | Remove leading zero in IP<br>@param string IP<br>@return string |
| public static function ipsuffixRange($suffixIP) | @param $suffixIP ipv6/suffix or ipv4/suffix<br>return array(&quot;Start-IP&quot;,&quot;End-IP&quot;) or false if error |
| public static function formatBinIp($binIp, $format = null) | Format internal binIp<br>@param string $binIp<br>@param string $format (null,&#039;dec&#039;,&#039;raw&#039;,&#039;full&#039;)<br>@return string if ok, false if error |
| public static function suffixMask($suffix, $bitBasis = 128) | create a binary string mask from suffix<br>@param int $suffix range 1..bitBasis<br>@param int $bitBasis, must be a multiple of 8, default 128<br>@return string as packed inet_pton in_addr representation |
| public static function ptonToDec($ptonBinStr) | inet_pton String to BCMath decimal string |
| public static function decToPton($dec) | BCMath decimal string to inet_pton String  |
| public static function ptonToBin($ptonBinStr) | inet_pton String to binary string |
| public static function hexToPton($strHex) | hex string to inet_pton String <br>@param string how &quot;0cde67ff&quot;<br>@return string pton byte straem 4 or 16 Byte<br>if input-len &lt;= 8 result is for IPv4, else IPv6<br>return false if error  |
| public static function binStrAddInt($binStr, $add) | Add int offset to ip-pton-string<br>@param string binStr (&quot;\x00\x00\x01\xff&quot;)<br>@param int add ( 1 )<br>@return string (&quot;\x00\x00\x02\x00&quot;)<br>return false if error  |
| public static function binStrAdd($binStr, $binStrAdd) | Add ip-pton-strings<br>@param string binStr (&quot;\x00\x00\x01\xff&quot;)<br>@param string binStrAdd (&quot;\x00\x00\x00\x01&quot;)<br>@return string (&quot;\x00\x00\x02\x00&quot;)<br>return false if error  |
| public static function binStrSub($binStr, $binStrSub) | Sub ip-pton-strings<br>@param string binStr (&quot;\x00\x00\x01\xff&quot;)<br>@param string binStrSub (&quot;\x00\x00\x00\x01&quot;)<br>@return string (&quot;\x00\x00\x02\x00&quot;)<br>return false if error  |

## Constants

| Declaration/Name | Value | Description/Comments |
| :--------------- | :---- | :------------------- |
|  const BIN = &quot;BIN&quot;; //string binary &quot;11110101..&quot; | &#039;BIN&#039; |  format constants<br>string binary &quot;11110101..&quot;  |
|  const DEC = &quot;DEC&quot;; //string decimal for bcmath | &#039;DEC&#039; |  string decimal for bcmath  |
|  const HEX = &quot;HEX&quot;; //Hex-string with &quot;0x&quot; at first | &#039;HEX&#039; |  Hex-string with &quot;0x&quot; at first  |
|  const RAW = &quot;RAW&quot;; //string internal bin pton | &#039;RAW&#039; |  string internal bin pton  |
|  const EXP = &quot;EXP&quot;; //expand output 0:0:567... | &#039;EXP&#039; |  expand output 0:0:567...  |
|  const COMP = &quot;COMP&quot;; //Compressed ::567:.. | &#039;COMP&#039; |  Compressed ::567:..  |
|  const FULL = &quot;FULL&quot;; //output 0000:0000:0567:... | &#039;FULL&#039; |  output 0000:0000:0567:...  |
|  const SUFFIX = &quot;+&quot;; //output with suffix | &#039;+&#039; |  output with suffix  |

### Demo and Test

http://jspit.de/check/phpcheck.class.iputils.php

### Requirements

- PHP 5.6+

## Thanks

This work was heavily inspired by [Dormilich/network-objects](https://github.com/Dormilich/network-objects).