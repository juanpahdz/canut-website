#!/bin/bash
# A script for moving all dev files out of the theme for testing with Theme Check plugin
txtbold=$(tput bold)
boldyellow=${txtbold}$(tput setaf 3)
boldgreen=${txtbold}$(tput setaf 2)
boldwhite=${txtbold}$(tput setaf 7)
yellow=$(tput setaf 3)
green=$(tput setaf 2)
white=$(tput setaf 7)
txtreset=$(tput sgr0)

echo "${YELLOW}Moving dev files out...${TXTRESET}"
mkdir -p $HOME/air-temp
find . -name '.DS_Store' -type f -delete
find ../ -name '.DS_Store' -type f -delete
mv /var/www/airdev/content/themes/air-light/.vscode $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/.git $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/.gitignore $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/.github $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/content $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/__MACOSX $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/front-page.php $HOME/air-temp/
mv /var/www/airdev/content/themes/air-light/CLAUDE.md $HOME/air-temp/

# All dev tooling, source and docs live under _dev/ - move it out as one unit
# (covers configs, package.json, composer files, node_modules, vendor, parcel/, bin/, assets/src/, README.md, CHANGELOG.md)
sudo mv /var/www/airdev/content/themes/air-light/_dev $HOME/air-temp/

mkdir -p $HOME/air-temp/template-parts
mkdir -p $HOME/air-temp/template-parts/header
mkdir -p $HOME/air-temp/template-parts/footer

# Remove custom stuff that are not part of an official WordPress theme in WP theme directory,
# Because:
# REQUIRED: The theme uses the register_taxonomy() function, which is plugin-territory functionality.
# REQUIRED: The theme uses the register_post_type() function, which is plugin-territory functionality.
rm /var/www/airdev/content/themes/air-light/inc/includes/taxonomy.php
rm /var/www/airdev/content/themes/air-light/inc/includes/post-type.php

# Screenshot, related to: https://themes.trac.wordpress.org/ticket/100180#comment:2
mv /var/www/airdev/content/themes/air-light/screenshot.png $HOME/air-temp/
cd /var/www/airdev/content/themes/air-light/
wget https://storage.googleapis.com/dude-dropshare/dKdyP0MyhiVPKL3GkDvNi8VTLWComTdxuUiHVbdjGoVNgyTNrVpNFlgrWRQ1QI5gwLgbWaXHY0ZzXCkEbJD9PFQrV4W4Etby2OTa.png
mv -v dKdyP0MyhiVPKL3GkDvNi8VTLWComTdxuUiHVbdjGoVNgyTNrVpNFlgrWRQ1QI5gwLgbWaXHY0ZzXCkEbJD9PFQrV4W4Etby2OTa.png screenshot.png

# Moving to bin dir
cd $HOME/air-temp/_dev/bin

echo "
${boldgreen}Done! Next steps:${TXTRESET}"
echo "
${boldwhite}1. Click the Check it -button: http://airdev.test/wp/wp-admin/themes.php?page=themecheck
2. Run: sh air-pack.sh (this also runs air-move-in.sh)
3. Follow instructions
${TXTRESET} "
