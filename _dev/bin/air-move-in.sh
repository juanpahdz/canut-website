#!/bin/bash
# A script for moving all dev files back to the theme
txtbold=$(tput bold)
boldyellow=${txtbold}$(tput setaf 3)
boldgreen=${txtbold}$(tput setaf 2)
boldwhite=${txtbold}$(tput setaf 7)
yellow=$(tput setaf 3)
green=$(tput setaf 2)
white=$(tput setaf 7)
txtreset=$(tput sgr0)

mkdir -p $HOME/air-temp
mv $HOME/air-temp/.vscode /var/www/airdev/content/themes/air-light/
mv $HOME/air-temp/.git /var/www/airdev/content/themes/air-light/
mv $HOME/air-temp/.gitignore /var/www/airdev/content/themes/air-light/
mv $HOME/air-temp/.github /var/www/airdev/content/themes/air-light/
mv $HOME/air-temp/content /var/www/airdev/content/themes/air-light/content
mv $HOME/air-temp/front-page.php /var/www/airdev/content/themes/air-light/
mv $HOME/air-temp/CLAUDE.md /var/www/airdev/content/themes/air-light/

# All dev tooling, source and docs live under _dev/ - move it back as one unit
sudo mv $HOME/air-temp/_dev /var/www/airdev/content/themes/air-light/

# Move the original starter screenshot back in, related to: https://themes.trac.wordpress.org/ticket/100180#comment:2
rm /var/www/airdev/content/themes/air-light/screenshot.png
mv $HOME/air-temp/screenshot.png /var/www/airdev/content/themes/air-light/

# Restore repository state before move
cd /var/www/airdev/content/themes/air-light/ && git stash
git status

echo "
${boldgreen}Air files moved in and github repository restored, now just do the following:${TXTRESET}"
echo "
1. Upload: https://wordpress.org/themes/upload/
2. Create new release: https://github.com/digitoimistodude/air-light/releases
3. Update version to https://airwptheme.com
4. All done!
${TXTRESET} "
