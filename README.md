# IpUtils

IPv4 and IPv6 utility class

## Usage

```php
require '/yourPath/IpUtils.php';  //or Autoload

$ip = IpUtils::create('192.168.178.10/24');

var_dump($ip->isIp4() AND $ip->hasSuffix());  //bool(true)

echo $ip->netMask();  //255.255.255.0

echo $ip->defaultGateWay();  //192.168.178.1

//check a Gateway
$gateWay = '192.168.178.3';
var_dump($ip->checkGateway($gateWay));  // bool(true)
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


### Demo and Test

http://jspit.de/check/phpcheck.class.iputils.php

### Documentation

http://jspit.de/tools/classdoc.php?class=IpUtils

### Requirements

- PHP 7.0+

## Thanks

This work was heavily inspired by [Dormilich/network-objects](https://github.com/Dormilich/network-objects).