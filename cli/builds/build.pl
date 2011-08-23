#!/usr/bin/perl
#
#

use File::Temp qw/ tempdir /;
use POSIX;
use Data::Dumper;

# this is simple
# just create a dir
# and move some shit
my $pwd = getcwd();

# make a tmp dir
my $tmp = tempdir();

# make our package
`sudo /usr/local/bin/drib create ../cli.dpf`;

# find it and move it
my $pkg = `find ./ -maxdepth 1 -name '*.tar.gz'`; chomp($pkg);

	# if this pkg exists we stop
	if ( -e "pkg/$pkg") {
		print "already exists... bitch!\n"; exit;
	}

# move it
`cp $pkg ./pkg/cli-current.tar.gz`;
`mv $pkg ./pkg/`;

# copy in warhol, LICENSE, README.md
`cp ../src/warhol $tmp/`;
`cp ../LICENSE $tmp/`;
`cp ../README.md $tmp/`;

# get version from changelog
my $v = `egrep -m 1 "Version [0-9\.]+" ../changelog | sed 's/Version //'`; chomp($v);

# move to tmp
chdir($tmp);

# name
my $name = "warhol-$v.tar";

# tar me up
`tar -cf $name .`;

# move back to pwd
chdir($pwd);

# move into tar
`mv $tmp/$name ./tar`;

# copy to current
`cp ./tar/$name ./tar/warhol-current.tar`;

# done
print "DONE\n"; exit;