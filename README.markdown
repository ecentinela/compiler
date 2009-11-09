Problem
=======
 - Javascript files are too large because they are not optimized
 - Users & your bandwidth is wasted for useless data
 - local javascript optimization requires tons of programs / libaries / knowledge

Solution
========
 - *Google Closure Compiler* size reduction (10-97% size reduction) in the cloud
 - optmizes all javascript files from a given folder

Usage
=====
Compiles a single javascript file or a whole folder in the cloud.

Usage:
    php compile.php /javascripts [options]
    php compile.php /javascripts/myfile.js [options]

Options are:
    -q, --quiet                      no output
    -p, --pretend                    no changes are made
    -r, --recursive                  execute the action on all subdirectories
    -w, --supress_warnings           don't show compilation warnings
    -l, --compilation_level          compilation level
                                     WHITESPACE_ONLY
                                     SIMPLE_OPTIMIZATIONS (default)
                                     ADVANCED_OPTIMIZATIONS
    -c, --combine                    if folder is given js files are combined in all.min.js
    -h, --help                       show this


Protection
==========
Any file that returns a failure code, is larger than before, or is empty will not be saved.

Example
=======
    php compile.php /javascripts
      compiling /javascripts/applications.js
      2887 -> 132 (102 gzip)                   = 4% (3% gzip)

      compiling /javascripts/social/file.js
      3136 -> 282 (240 gzip)                   = 9% (7% gzip)

      compiling /javascripts/social/app.js
      5045 -> 4 (3 gzip)                       = 0% (0% gzip)
      ...

Author
======
[Javier Martinez Fernandez](http://ecentinela.com)  
ecentinela@gmail.com