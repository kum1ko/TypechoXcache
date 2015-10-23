# TypechoXcache
Typecho的文章缓存插件，基于xcache

目前Typecho的缓存插件不是很多，MostCache是SAE的Memcached和MySQL的缓存，而FileCache是基于文件的，虽说即插即用，很方便，但是留着内存还是浪费，于是稍微改了一下，用xcache做了缓存。

默认TTL=600，缓存内容为所有文章页面。

目前插件还不完善，可以自定义Key前缀，如果TTL要做动态判定，性能略低，还在完善中。

感谢原作者http://www.shionco.com
