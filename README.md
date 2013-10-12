TuneKSP 0.01
=====================
https://github.com/Conti/TuneKSP

Requirements
----------------
- PHP CLI 5.3.6 or later

Installation
----------------
Copy tuneksp.php to your KSP root directory. (The directory where GameData and saves directories are located)
On my system I have ~/KSP/GameData and ~/KSP/saves - so tuneksp.php is located at ~/KSP/tuneksp.php

Advanced users: Optionally, if you feel comfortable editing the variables at the top of the php file, you may define your own absolute or relative paths to GameData and GameData/UniverseReplacer.

Useage
----------------
Open a terminal and browse to your KSP root directory. On my system this is cd ~/KSP
Type: "php tuneksp.php -h" (without the quotes) and press enter, this will show you the following usage information:

```
$ php tuneksp.php -h

TuneKSP version 0.01

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

Note: The universe replacer help and functionality will only appear if the script detects ./GameData/UniverseReplacer/

For most people all that you will need to use is the -b (--tune-building) flag for when you want to create new .craft files in the VAB/SPH and the -m (--tune-mission) flag for when you want to fly a mission. These are the only options I use personally but the other options are provided for people who may want to do something like tuneksp.php -ut -a (which would enable all parts, but disable all IVA and tune universe replacer). These kinds of configurations may be desirable for some people who want to build and run test flights with some eye candy enabled still.


About
----------------
This is a php script I wrote to help manage the rather large number of mods I have installed at any given time. It will function with most KSP mods which work in KSP v0.21+ and are installed to the KSP/GameData directory. It may not function properly with some older mods or mods which use uncommon configurations which will require mod-specific code to be added. There is mod-specific code for two such mods currently: UniverseReplacer4 and KAS4.2. 

*PLEASE NOTE:* This was written on OSX 10.9, and it should work on OSX 10.8 without any modifications to the OS. OSX 10.7 would need a newer version of php installed. It should work on linux so long as the minimum php version is met, but I do not believe it will work on windows (even with php installed) without modifications. If someone would like to contribute these modifications and send me a pull request I would appreciate it, otherwise I may setup a windows dev environment at some point to do so, my time is limited however and I do not normally do development on windows systems.

How It Works
----------------
It will recursively scan your GameData directory and compile a list of all parts and assets. It will then extract part information from your saved .craft files and generate used/unused parts/assets arrays. It will then disable all unused part cfg's, meshes, and texture assets for the unused parts. Additionally it will notify you of any missing parts (parts which are defined in your .craft files but do not exist in GameData). It does the same for unused IVA's but when disabling all IVA views it will comment out any IVA definitions within command module cfg files, or (optionally) redefine a single IVA for all command module cfg files which have an IVA definition. 



Todo
----------------
- Add code to parse persistance files to prevent disabling parts which are used but are not currently defined in any .craft files, and to warn of any missing parts which are not in .craft files
- Add option to parse craft/persistance data from only one save game at a time (it currently will parse all .craft files under /saves/)
