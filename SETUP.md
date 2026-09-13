# Setup

this repository contains multiple generations of tamagotchi town sites, accross multiple different eras;
keep in mind that the setup is a bit of a hack right now;

first some background, Tamagotchi Town exclusivlely made use of an old standard called CGI ("Common Gateway Interface") 
which requires some specific setup in NGINX to get working.

furthermore they made a bunch of assumptions about things

for CGI scripts, we opted to rewrite them using Python, and the CGI library,
.. however since then, python has removed the CGI package from python

you can manually install it with :
```
pip install legacy-cgi
```

dreamtown also requires a SQLLite database, such as mariadb,
be sure to install its library with
```
pip install mariadb
```


## Server configuration

```
server {
        listen 80;
        listen [::]:80;

        include snippets/ssl.conf;

        access_log /var/log/nginx/famitama.xyz-access.log;
        error_log /var/log/nginx/famitama.xyz-error.log;

        root /home/web/public_html/famitama.xyz/;
        index index.html index.php index.cgi;
        server_name famitama.xyz www.famitama.xyz;

        include snippets/tamatown.conf

        location / {
                try_files $uri $uri/ =404;
        }

        include snippets/php.conf;
}
```
where `snippets/tamatown.conf` is:

```
location ~ ^/friends/cgi-bin/auth/5555$ {
        rewrite /friends/cgi-bin/auth/5555 /friends/cgi-bin/auth/5555/index.cgi last;
}

location ~ (cgi-bin|cgi)/ {
        include snippets/pythoncgi.conf;
        gzip off;
        root  /home/web/public_html/famitama.xyz;
		
        fastcgi_pass unix:/var/run/fcgiwrap.socket;
        fastcgi_param DOCUMENT_ROOT /home/web/public_html/famitama.xyz;
        fastcgi_param DT_DATABASE_USER "<MARIADB USERNAME HERE>";
        fastcgi_param DT_DATABASE_PASSWORD "<MARIADB PASSWORD HERE>";
        fastcgi_param DT_DATABASE_HOST "<MARIADB HOST HERE>";
        fastcgi_param DT_DATABASE_PORT "<MARIADB PORT HERE>";
        fastcgi_param DT_DATABASE_NAME "<MARIADB DATABASE NAME HERE>";
        fastcgi_param PYTHONPATH <full path to friends/cgi-bin>;
        fastcgi_index index.cgi;
        include /etc/nginx/fastcgi_params;
}
```

you might want to change "famitama.xyz" and other related things (file paths, etc) to whatever your server is,
 - if you are using a webserver other than NGINX, i have no idea how to configure it for those, sorry.


note that for dreamcast to work, this has to be set on the default vhost, 
as well as that ``crossdomain.xml`` but be present on the root of the default vhost, as well as the actual one
## Tamagotchi V5

Tamagotchi V5 hard-codes a domain to connect to for the server as "famitama.com" .. 
to do this you need to use the [JPEXS Flash Decompiler](https://github.com/jindrapetrik/jpexs-decompiler) to patch the famitama_shell.swf;

if you use the pre-patched one it'll just load from a relative path,


- Tamagotchi Friends

Tamagotchi friends had a way more in-depth

## Dreamtown

DreamTown has an encrypted XML config
"mmog.cebd" that tells it what server to look for;

which is an encrypted XML file, you need [cebd_decrypt](https://github.com/Reagotchi/cebd_decrypt) to decrypt/encrypt it
you can decrypt and change the server_url feild,

in all my testing, it appeared that this did not work with DNS
and had to be a direct IP, but recent tests i did later seem to contradict this
the one included on our server also loads from a local path. 

