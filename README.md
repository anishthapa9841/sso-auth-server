# Auth Server 

An openid based single sign on (SSO) authentication server and oauth authorization server implementation in php.

## Installing

To test this auth server git clone this project to your web server
```bash
git clone https://github.com/anishthapa9841/sso-server-php.git
```
cd into the cloned folder in terminal and then run
```bash
composer install 
``` 

## Importing database

Create a empty database in your mysql server. For example:
```bash
create database `db_oauth`;
```
There are two ways to import database tables.

1. open then migration folder in project root there you will find a dump file then run this dump file in your database server.
2. open the url public/migration_setup/setup_database.php in browser then copy the table creation code and run in mysql client sql editor.


Then Rename .env_example to .env then open the file and make neccessary changes into the file specially database connection.
```bash
DB_DSN=mysql:dbname=db_oauth;port=3306;host=127.0.0.1
DB_USERNAME=test
DB_PASSWORD=test
OPENID_ISSUER=ssoaccount.com
``` 
And now your setup is complete.

## Getting started

First create a private and public RSA keypair. For eg:
```bash
openssl genrsa -out rsa.private 4096
openssl rsa -in rsa.private -out rsa.public -pubout -outform PEM
```

In your database create a new client and new user. Client are unique id that identify the application while user are unique value that uses that application. For eg: mis is the client and xyz is the user. 

Sample database query:
```bash
INSERT INTO `oauth_clients` (`client_id`, `client_secret`, `redirect_uri`, `grant_types`, `scope`, `user_id`)
VALUES
  ('mis', 'test1234', 'https://test.com/login_callback.php', NULL, 'openid logout default', NULL);

INSERT INTO `oauth_users` (`compcode`, `username`, `password`, `first_name`, `last_name`, `email`, `email_verified`, `scope`)
VALUES
  ('7777', 'xyz', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'xyz', 'ttt', 'xyzhub@testmail.com', 1, 'default');

INSERT INTO `oauth_public_keys` (`client_id`, `public_key`, `private_key`, `encryption_algorithm`)
VALUES
  ('mis', '-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC9jFjqIdfQHaaR0iNuTWJBFhR1\nNE/iztt764QxPX/O3PZlm3v6PzVWpvBmsWykzGxcSAKhh3VgjoaB6qLUbOPDbGIk\nVJ3SUZh4dprp2lmA/ZXm4VGxUvkkc5xHa0lR2bjSUMT71reHMgewOmKo1t3APkA7\nC53Gz6pmgJjFAaEa1QIDAQAB\n-----END PUBLIC KEY-----\n', '-----BEGIN RSA PRIVATE KEY-----\nMIICXgIBAAKBgQC9jFjqIdfQHaaR0iNuTWJBFhR1NE/iztt764QxPX/O3PZlm3v6\nPzVWpvBmsWykzGxcSAKhh3VgjoaB6qLUbOPDbGIkVJ3SUZh4dprp2lmA/ZXm4VGx\nUvkkc5xHa0lR2bjSUMT71reHMgewOmKo1t3APkA7C53Gz6pmgJjFAaEa1QIDAQAB\nAoGBAIpPN64YZc6hZCLxUBMzeid+Ag2Hz2bzhCpHL11yv6jliRGZQ/zVVIlXJQH6\nIwmZdNRdYzYUjqyXM0TnPkt87TAvXVGGDjILO43kbFmoTzARmkDN6YRDbDfvUYGf\nz19HTgoTGCWEVvhX9vsqX1UcGDOPLlgiIABP7PH8B1WwDSr9AkEA96uHWdfEJH/+\nKXJXKEVpIfkb/yWZqTJxpEXg/zmHJnqJ8s8te1J9gfsadfsadfPZU7Lc6VGdW3KPB8psRK0DW\nXQyFwwbIzwJBAMPsYd8wiuk5K25wSmwuay7cIAxWo7YLGHtxkYD2AoVOEUlHemHx\nyzVV1zbZ6si7zYzVkdJytBnTKETcFRBYgxsCQQDQICuouyHPzmMmLzjA1btoWi9F\ntTIwtfw7oKFXuN8y3azJB4Lf7E0dL2PkXIE+5mxfE5nrJpZB88VwAhLx9eSPAkEA\nslFywrBrvdlKrjmFgvC8nOm8QX6ZghaXMcMrqzQ9Fxb2pLtpp7tqOCDowAOWrRxA\n+O1oyyGayeZlwEaO5VGqAQJAF3puN79uI+lULjUVMCytHUYku+sG0j7BDvvdD+ln\n9oYfkhkHQ3xDe+l6NUDQx4VPUSx7GkfKNdLvCegfufcBfA==\n-----END RSA PRIVATE KEY-----\n', 'RS256');

``` 
**Note**: In the above query password is sha1 value 'admin'. Last insert query in the above example where the public key and private key are the contents of the rsa.public and rsa.private generated in the first step using openssl. And also you need to save this rsa.public in the client application (For eg: https://test.com/).

### During login

In your other application (for eg: logmis ) where you want to implement this login call auth_openid.php endpoint inside public dir in this auth server with following parameters from your other application.
```bash
client_id : mis
redirect_uri : https://test.com/login_callback.php
response_type : code
scope : openid
state : xyz
session : 322ni7dtunj0aopl92vkvjijps
```
**Note**: "State" parameter to encode an application state that will round-trip to the client application after the transaction completes. In this way, the application can put the user where they were before the authentication process happened. Session in the above params is the value of session cookie PHPSESSID which we can get by session_id() function in php.

For eg:
```bash
ssoaccount.com/auth_openid.php?client_id=mis&redirect_uri=https://test.com/login_callback.php&response_type=code&scope=openid&state=5071dbbe9a1c90c1&session=322ni7dtunj0aopl92vkvjijps
```

Output of the above reindirection is a login page is auth server where the user is prompted to enter username and password. After you enter the username and password in this page 
you are redirected the redirect_uri (for eg: https://test.com/login_callback.php) 
with fol parameters. For eg:
```bash
http://172.28.128.3/login_callback.php?code=5900842ce7f01b75122f8631b8d0262f8bfd0ed8&id_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJuYWFjY291bnQubWlsLm5wIiwic3ViIjoiYW5pc2giLCJuYW1lIjoiMSIsInBubyI6IjY1NzUiLCJlbWFpbCI6ImFuaXNodGhhcGFAbGl2ZS5jb20iLCJhdWQiOiJlbmRwb2ludF9jbGllbnQiLCJpYXQiOjE1ODgyNDQ2MjAsImV4cCI6MTU4ODI0ODIyMCwiYXV0aF90aW1lIjoxNTg4MjQ0NjIwLCJzY29wZSI6Im9wZW5pZCBsb2dvdXQgZGVmYXVsdCIsImFtciI6WyJwd2QiXSwibm9uY2UiOiJrZW4wYWUxYWwya2dzN3R0Ympxc2ppbm5ubiIsInN0YXRlIjoiNDc3ODFmMzMyN2NlYjlmMyIsImp0aSI6IjcxZDFJbHplSnFtQXZ4cVJ5bXNzWVEifQ.W-O9VaeORnvPPm9rRYDg_zFIzNtcs4L1fGQ3ZDSfwMvJMAP1MAvrrWIOcZ-4VU6jhc3uJ8BHptbDxaif8VLtPxmPGtE7Z4GtzgzYZdMg4LY_9feT343-h7pX-qIYeXSvWlnUiW5t109ZiZT2ipJOJyNK0CbT89hJkyYgoOs3JLY
```
**Note**: In the above example code=5900842ce7f01b75122f8631b8d0262f8bfd0ed8 is the authorization code which has no use till now in this flow and id_token is the jwt value which will be decoded in the application server (for eg: test.com). For your testing you can go to [jwt.io debugger] (https://jwt.io/) and paste the above id token over there and see its value. The beauty of this jwt token is that is digitally signed and can be verified with your public key without central authority to verify it. The decoded above jwt looks like this.
```bash
{
  "iss": "ssoaccount.mil.np",
  "sub": "steve",
  "name": "1",
  "email": "test@mail.com",
  "aud": "endpoint_client",
  "iat": 1588244620,
  "exp": 1588248220,
  "auth_time": 1588244620,
  "scope": "openid logout default",
  "amr": [
    "pwd"
  ],
  "nonce": "ken0ae1al2kgs7ttbjqsjinnnn",
  "state": "47781f3327ceb9f3",
  "jti": "71d1IlzeJqmAvxqRymssYQ"
}
```
Now in your application you can verify the validity i.e exp time manually .And you also need to check the digital signature with the public key and start a new session.

### During logout

Logout is a two step process.

* Call token.php endpoint to get access token to call logout endpoint
Make a call to token endpoint example 
```bash
curl --location --request POST 'ssoaccount.com/token.php' \
--form 'grant_type=client_credentials' \
--form 'scope=logout'
```
and sample response looks like this
```bash
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjkwZjU4NGNjMjdmM2E1ZjI1MTZkYzkxMWQwODI0OWRkYmYwNmM0NWIiLCJqdGkiOiI5MGY1ODRjYzI3ZjNhNWYyNTE2ZGM5MTFkMDgyNDlkZGJmMDZjNDViIiwiaXNzIjoibmEubWlsLm5wIiwiYXVkIjoiZW5kcG9pbnRfY2xpZW50Iiwic3ViIjpudWxsLCJleHAiOjE1ODgyMzAxNDQsImlhdCI6MTU4ODIyNjU0NCwidG9rZW5fdHlwZSI6ImJlYXJlciIsInNjb3BlIjoibG9nb3V0In0.dOYl6jT4JCpAeEI4O0E87TTvQyTRZRciU6WwAoY0ro5NwNWEdeb1H8igiRe4pnITnGDD4magCFxUoY0vFsh5tfMDj_PeRIZETU-s7VsDFpWVgtJnnBjmadaaFOVdY83i9o1YYGZxqnq915qKdCtndHovVLmLRwNncrITYVFhFOE",
    "expires_in": 3600,
    "token_type": "bearer",
    "scope": "logout"
}
```
Here the important thing is the access_token which will be used to call the logout_work.php endpoint

* Then call logout_work.php endpoint example
```bash
curl --location --request GET ssoaccount.com/logout_work.php?access_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6IjdmYjZhNTA5M2FkOWZhMGJjZDcxZDgzZDlkMmRjYzlkMmMzMGIxYmYiLCJqdGkiOiI3ZmI2YTUwOTNhZDlmYTBiY2Q3MWQ4M2Q5ZDJkY2M5ZDJjMzBiMWJmIiwiaXNzIjoibmFhY2NvdW50Lm1pbC5ucCIsImF1ZCI6ImVuZHBvaW50X2NsaWVudCIsInN1YiI6bnVsbCwiZXhwIjoxNTg4MjQ5NTQyLCJpYXQiOjE1ODgyNDU5NDIsInRva2VuX3R5cGUiOiJiZWFyZXIiLCJzY29wZSI6ImxvZ291dCJ9.eBUlkckEqxStmv09NoOczfUaZBm3FSrPYrHvVjPLZSgRFm17oTK_38UUIVV7-8x3_JANYe_BKRVGaOxaKF4dY1_sv7iozgf7Yo-sL9nF_YzKXGuY2TPhC5NV0Ise-nBmzRXALr3UDNyzxTeLDweM2RR2YDlThJvtOOWMhzAqXsw&session_id=ken0ae1al2kgs7ttbjqsjinnnn
```
whose successful response looks like
```bash
{"success":true,"message":"You successfully logged out"}
```

After getting this reply from the server then destroy session variable from the application (for eg: https://test.com/login_callback.php). In this way the logout process is complete

## Recommendation

We recommend you to run your webserver inside a vagrant box. For more detail [Go to this page](https://www.vagrantup.com/).

### Prerequisites

you will need following this installed in your development enviroment to run this project
* [git](https://git-scm.com/downloads)
* [composer](https://getcomposer.org/download/)
* web server [Apache](https://httpd.apache.org/download.cgi)/ [Nginx](http://nginx.org/en/download.html)
* [php](https://www.php.net/downloads)
* [mysql](https://www.mysql.com/downloads/)


### Project structure 

```bash
├── migration                         # houses the mysql dump file of the database 
│   └── db_dump.sql                   
├── public
│   ├── assets                        # has all the assets like images, css, js etc
│   │   └── miligram                  # css framework
│   ├── migration_setup               # has a php script that generate database schema query 
│   │   └── setup_database.php        # which is required during setup
│   ├── views     
│   │   └── auth_openid.php           # login page view
│   ├── auth_openid.php               # login page endpoint to call by client
│   ├── index.php                     # just checks project is setup correctly and gives welcome message
│   ├── logout_work.php               # logout endpoint to call during logout process
│   └── token.php                     # provides access token
├── src    
│   ├── AuthCustController.php        # Custom Auth Controller exended from BaseAuthorizeController of
│   │                                 # bshaffer library in vendor directory
│   ├── DBAdditional.php              # Additional database crud extended from OAuth2\Storage\Pdo
│   │                                 # to work with auth_session_table and auth_session_request_table
│   └── IdTokenCust.php               # provides customized id token based on our system 
│                                     # requirements
├── .env_example                      # example .env file which houses enviroment variable
│                                     # used by DOTENV package in composer        
├── .gitignore
├── bootstrap.php                     # initializes the databases, openid server and response type for our
│                                     # server
├── composer.json
├── composer.lock
└── README.me                         # basic documentation and getting started doc.
```

### Getting started

---
After project setup start the web server in your apache/ xampp server/ php inbuild server.

For eg start php inbuild server pointing towards public dir inside the project as the base dir
```bash
php -S 127.0.0.1:8000 -t public
```

## TRUE RSA PUBLIC AND PRIVATE KEY GENERATION 

Key length of 1024 is good but 4096 is much better but due to the problem in the database field length in oauth_access_token where the primary key field accesstoken cannot be Text type we use 1024 key length
```bash
openssl genrsa -out rsa.private 4096
openssl rsa -in rsa.private -out rsa.public -pubout -outform PEM
```

## ABOUT JWT SIGNATURE COMPARE 

RSA 256  used in this project

**Signature Checking mechanism**
The receiver of the JWT will then:

take the header and the payload, and hash everything with SHA-256
decrypt the signature using the public key, and obtain the signature hash
the receiver compares the signature hash with the hash that he calculated himself based on the Header and the Payload

## Authors

* **AnishThapa ** - *Initial work* -

See also the list of [contributors]() who participated in this project.

## Acknowledgments

* [Bshaffer's library for implementing an OAuth2 Server in php](https://github.com/bshaffer/oauth2-server-php)
* [Ory Hydra](https://www.ory.sh/hydra/docs/index)
* [AuthO](https://auth0.com/)
* [OAuth Google Developers Playground](https://developers.google.com/oauthplayground/)
* [OAuth 2.0 playground](https://www.oauth.com/playground/)
* [Openid](https://openid.net/)

Read the full Documentation [Here]()


