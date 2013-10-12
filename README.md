TuneKSP 0.02
=====================

https://github.com/Conti/TuneKSP


Requirements
----------------

- PHP CLI 5.3.6 or later
- OSX/Win/Linux
- KSP 0.21+


Installation
----------------

**PHP**

- PHP 5.3.6+ Comes with OS X 10.8+ - For 10.7 or earlier version of OS X you will need to use macports or other options to install a compatable version of php.
- For windows download the php installer here: http://windows.php.net/downloads/releases/php-5.3.27-Win32-VC9-x86.msi (if you do not already have php installed).
- For linux, use your distribution package manager or install from source (if you do not already have php installed, or need to upgrade to a compatable version of php).

**TuneKSP**

Copy tuneksp.php to your KSP root directory. (The directory where the KSP application, GameData and saves directories are all located)


Usage
----------------

- Open a terminal (or cmd in windows) and browse to your KSP root directory.
- On my osx system this is `cd ~/KSP`
- On my test windows system this is `cd "C:\Program Files\KSP"`
- Type: `php tuneksp.php -h` and press enter, this will show you the following usage information:

```
$ php tuneksp.php -h

TuneKSP version 0.02

Usage:
-b  --tune-building    : Tune for VAB/SPH, Disable all IVA, Enable all Parts, Disable UR
-m  --tune-mission     : Tune for mission, Disable unused IVA/Parts, Tune UR
-e  --enable-all       : Enable all parts and iva.
-p  --disable-parts    : Disable unused parts.
-i  --disable-iva      : Disable unused iva.
-a  --disable-all-iva  : Disable all iva.
-ut --tune-universe    : Tune active universe replacer textures.
-ud --disable-universe : Disable all universe replacer textures.
-v  --verbose          : Verbose Output.
-h  --help             : Display this message.
```

Note: The universe replacer functionality will only appear if the script detects `~/KSP/GameData/UniverseReplacer`

For most people all that you will need to use is the -b (--tune-building) flag for when you want to create new .craft files in the VAB/SPH and the -m (--tune-mission) flag for when you want to fly a mission. 

These are the only options I use personally but the other options are provided for people who may want to do something like `tuneksp.php -ut -a` (which would enable all parts, but disable all IVA and tune universe replacer). These kinds of configurations may be desirable for some people who want to build and run test flights with some eye candy enabled still.


About
----------------

This is a script I wrote to help manage the rather large number of mods I have installed at any given time. It is compatible with most KSP mods which work in KSP v0.21+ and are installed to the KSP/GameData directory. It may not function properly with some older mods or mods which use uncommon configurations, mods with uncommon configurations will require mod-specific code to be added. There is mod-specific code for two such mods currently: UniverseReplacer4 and KAS4.2.


How It Works
----------------

The script will recursively scan your GameData directory and compile a list of all parts and assets. It will then extract part information from your saved .craft files and generate used/unused parts/assets arrays. It will then disable all unused part cfg's, meshes, and texture assets for the unused parts. Additionally it will notify you of any missing parts (parts which are defined in your .craft files but do not exist in GameData). It does the same for unused IVA's but when disabling all IVA views it will comment out any IVA definitions within command module cfg files, or (optionally) redefine a single IVA for all command module cfg files which have an IVA definition.


Todo
----------------

- Add code to parse persistance files to prevent disabling parts which are used but are not currently defined in any .craft files, and to warn of any missing parts which are not in .craft files
- Add option to parse craft/persistance data from only one save game at a time (it currently will parse all .craft files under /saves/)
