# warhol
command line client for [warholcdn.com](http://warholcdn.com)

# Requirements
* Perl v5.8.8
* CPAN Modules
 * JSON
 * Digest::MD5
 * MIME::Base64
 * OAuth::Lite::Consumer

# Install

## The Installer
1. Download the installer `curl -sL http://warholcdn.com/install.pl | perl`

## Old School
1. Download the tar `curl -L http://warholcdn.com/install/download > warhol.tar`
2. Unpack the tar `tar -xzf warhol.tar`
3. Move the script to your favorite `bin` directory `mv warhol /you/fav/bin/dir`

## From GitHub using drib
1. Clone the repo `git clone git://github.com/traviskuhl/warhol.git`
2. Move into warhol `cd warhol/`
3. Install a dev build `drib create -t s -i -c`

# Usage
`warhol [options] command`

# Commands
	help 	Display a help message
	init	Create an asset folder
	push	Send updated asset to the server
	pull	Pull any updated assets from the server
	build	retrieve information about a build
	
# Examples

## Create an Asset Folder
	
	warhol init . --key="{$apiKey}" --secret="{$apiSecret}"
	
## Push asset folder
	
	warhol push .
	
	
# Get Help
* IRC: [#warholcdn](irc://irc.oftc.net/#warholcdn) - irc://irc.oftc.net/#warholcdn
* Bugs: [github issues](https://github.com/traviskuhl/warhol/issues?labels=cli)

# LICENSE
The MIT License

Copyright (c) 2011 the.kuhl.co llc

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.	