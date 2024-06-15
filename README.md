# AssociationsDebugger plugin for [CakePHP][cakephp]

AssociationsDebugger is a plugin that is made for debugging associations.
The point of this plugin is to quickly show different associations of your CakePHP application without you needing to browse through the model files.

CakePHP 3.x version can be found [here](https://github.com/zunnu/associations-debugger/tree/3.x)
CakePHP 4.x version can be found [here](https://github.com/zunnu/associations-debugger/tree/4.x)

![Example](https://i.giphy.com/media/LylyHkulR8xTpDrapb/giphy.webp)

## Requirements
* CakePHP 5.x
* PHP 8.1 >

## Installing Using [Composer][composer]

`cd` to the root of your app folder (where the `composer.json` file is) and run the following command:

```
composer require --dev zunnu/associations-debugger
```
Then load the plugin by using CakePHP's console. This plugin is not needed in production environment, so it's recommended to be loaded using:

```
./bin/cake plugin load AssociationsDebugger --only-debug
```
To see the Associations panel in DebugKit this plugin needs to be loaded before DebugKit

## Usage
You can see the tree by going to
http://app-address/associations-debugger
Here you can filter by **association type and model**.
<img src="https://i.imgur.com/aEqreKN.png" alt="Tree">

This same tree can be also viewed in the CakePHP debugKit.
<img src="https://i.imgur.com/NoHfOQp.png" alt="DebugKit tree">

Association structure is explained here:
<img src="https://i.imgur.com/fYxikgt.png" alt="Structure">

## License

Licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
