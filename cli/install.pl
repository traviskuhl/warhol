#!/usr/bin/perl
#
#  wargolcdn
# =================================
#  (c) Copyright the.kuhl.co llc 2011
#  
#
# This is free software. You may redistribute copies of it under the terms of
# the GNU General Public License <http://www.gnu.org/licenses/gpl.html>.
# There is NO WARRANTY, to the extent permitted by law.
# 

use File::Temp qw/ tempdir /;
use POSIX;
use FindBin qw($Bin);

# temp dir
my $tmp = tempdir();

# pull down the latest tar from our src
`curl https://$(host)/install/download/current.tar > $tmp/current.tar`;

# wd
my $pwd = getcwd();

# move into tmp
chdir($tmp);

# untar 
`tar -xf current.tar`;

# move our warhol script into the bin dir
`mv warhol $Bin`;

# and done
print "Done!\n";
