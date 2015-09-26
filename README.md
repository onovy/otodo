# otodo #

![Travis CI](https://travis-ci.org/onovy/otodo.svg?branch=master)

Powerfull TUI for todo.txt.

![Screenshot](https://raw.githubusercontent.com/onovy/otodo/master/screenshot.png "Screenshot")

## Requirements ##
otodo is written in PHP (yes, in PHP!). So you need PHP interpreter. I'm using PHP 5.4, but it should work on older version. No extension is requred. It should work on almost any Unix-like system, tested on Mac OS X.

## Installation ##
There isn't any releases, I consider master branch at Github as stable.

Just make a copy of repository:
```
git clone https://github.com/onovy/otodo.git
```

## Usage ##
Just type
```
./otodo
```

It uses default config file placed in same directory as otodo. If you want another config file just put filename as argument.

## Config ##
Whole configuration is in config.ini, there isn't any GUI for editing it. Just open your favorite editor.

## Development ##
Project is divided into few parts:
* **Config**: class for configuration loading
* **Exception**: all exception classes
* **Gui**: TUI implementation
* **ReadLine**: own Readline-like implemenation
* **Recurrent**: class for recurrent tasks parsing and handling
* **Todo**: class for parsing and generating one todo.txt line
* **Todos**: class for parsing and generating whole todo.txt file
* **TodoEx**: extended Todo class with due date and recurrent support
* **TodosEx**: extended Todos class using TodoEx
* **tests**: PHPUnit test

All pull requests are welcome.

## FAQ ##
#### Why is it written in PHP? ####
Because I speak PHP and it doesn't matter.

#### Is it possible to translate it to my language? ####
Not now but patches for localization are welcome.

#### Can I change todo.txt file when otodo is running? ####
Yes, otodo detect changes on filesystem and it's usable on multiple computers synced with Dropbox, etc. Just tune gui.reload_timeout option for pooling timer.
