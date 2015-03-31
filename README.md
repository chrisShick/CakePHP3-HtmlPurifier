# CakePHP3-HtmlPurifier plugin
This plugin is a sanitizer for entity data that uses the Html Purifier Library: http://htmlpurifier.org/

HTML Purifier is a standards-compliant HTML filter library written in PHP. HTML Purifier will not only remove all malicious code (better known as XSS) with a thoroughly audited, secure yet permissive whitelist, it will also make sure your documents are standards compliant, something only achievable with a comprehensive knowledge of W3C's specifications.
## Requirements
  - CakePHP 3.x
  - PHP >= 5.4.16
  
## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require chrisShick/CakePHP3-HtmlPurifier
```
or add the plugin to your project's ``` composer.json ``` like this:
```
    {
        "require": {
            "chrisshick/cakephp3-html-purifier": "dev-master"
        }
    }
```
##Enable the Plugin
In 3.x all you need to do to enable the plugin is: 
```
    Plugin::load('ChrisShick/CakePHP3HtmlPurifier');
```
If you are already using ``` Plugin::loadAll(); ```, then you do not need to do the above step.

##Usage
To start sanitizing your data, you need to attach the behavior to your table in the initialization function and pass in the fields that you want to be sanitized: 
```
    $this->addBehavior('chrisShick/CakePHP3HtmlPurifier.HtmlPurifier', [
        'fields'=>['title','description']
    ]);
```

##License

The MIT License (MIT)

Copyright (c) 2015 Chris Hickingbottom

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
