# CakePHP3-HtmlPurifier plugin
[![Build Status](https://travis-ci.org/chrisShick/CakePHP3-HtmlPurifier.svg?branch=master)](https://travis-ci.org/chrisShick/CakePHP3-HtmlPurifier)

This plugin is a sanitizer for entity data that uses the Html Purifier Library: http://htmlpurifier.org/

HTML Purifier is a standards-compliant HTML filter library written in PHP. HTML Purifier will not only remove all malicious code (better known as XSS) with a thoroughly audited, secure yet permissive whitelist, it will also make sure your documents are standards compliant, something only achievable with a comprehensive knowledge of W3C's specifications.

## Recognition
  I have to give credit to @josegonzalez for giving me the inspiration to write this based on his Purifiable Behavior.
## Requirements
  - CakePHP 3.1.x
  - PHP >= 5.4.16
  
## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
  composer require chrisshick/cakephp3-html-purifier
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
    $this->addBehavior('ChrisShick/CakePHP3HtmlPurifier.HtmlPurifier', [
        'fields'=>['title','description']
    ]);
```
By default the behavior purifies only on the beforeMarshal Event. To disable this, you should do the following:
```
   $this->addBehavior('ChrisShick/CakePHP3HtmlPurifier.HtmlPurifier', [
        'events' => [
           Model.beforeMarshal => false,
           // you can also uncomment the line below to turn on the purifier only on the beforeSave event
           //Model.beforeSave => true,
        ]
   ]);
```
You can also have the purifier called on a custom event: 
```
    $this->addBehavior('ChrisShick/CakePHP3HtmlPurifier.HtmlPurifier', [
        'events' => [
           Model.myCustomEvent => true,
        ]
   ]);
```
You can adjust the HtmlPurifier configuration by passing in the config key into the configuration:
```
   $this->addBehavior('ChrisShick/CakePHP3HtmlPurifier.HtmlPurifier', [
        'config' => [
            'HTML' => [
                'DefinitionID' => 'purifiable',
                'DefinitionRev' => 1,
                'TidyLevel' => 'heavy',
                'Doctype' => 'XHTML 1.0 Transitional'
            ],
            'Core' => [
                'Encoding' => 'UTF-8'
            ],
            'AutoFormat' => [
                'RemoveSpansWithoutAttributes' => true,
                'RemoveEmpty' => true
            ],
        ],
   ]);
```
You can find all the configurable options and custom filters on the http://htmlpurifier.org/ website.


##License

The MIT License (MIT)

Copyright (c) 2015 Chris Hickingbottom

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
