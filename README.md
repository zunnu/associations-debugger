# AssociationsDebugger plugin for CakePHP

AssociationsDebugger is a simple plugin that is made for debugging associations.
The point of this plugin is to quickly show different associations of your CakePHP application without you needing to browse through the model files.


## Requirements
* CakePHP 3.x
* PHP 7.2 >

## Installing Using [Composer][composer]

`cd` to the root of your app folder (where the `composer.json` file is) and run the following command:

```
composer require zunnu/AssociationsDebugger
```

Then load the plugin by using CakePHP's console:

```
./bin/cake plugin load AssociationsDebugger
```

## Usage
You can see the tree by going to
http://app-address/associations-debugger
Here you can filter by **association type**.
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