# php-SSL-Curl
PHP使用SSL 证书请求curl、P12证书转pem证书
最近对接了一个来自于深圳***的JAVA接口、他们给了一个p12 的证书，貌似对PHP来说不达标，需要将该证转换为PEM格式。
使用Linux命令直接转换：

openssl pkcs12 -clcerts -nokeys -out cbip0151.pem -in cbip0151.p12
或
openssl pkcs12 -in cbip0151.p12 -out cbip0151.pem -nodes 
————————————————

另外一种转换方法也可以尝试一下：
找个软件https://myssl.com/cert_convert.html将其转换后获得三个文件
