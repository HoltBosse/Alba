echo "build script init"

#nuke stuff not needed
#keep admin-controllers-core-plugins-widgets
rm -rf images
rm -rf installer
rm -rf user_classes
rm -rf .gitignore
rm -rf README.md
rm -rf configTESTBACKUP.php
rm -rf configADMINTESTBACKUP.php
rm -rf home.svg
rm -rf htaccess.txt
rm -rf testform.json
rm -rf testrepeatform.json
rm -rf setupfiles.sh
rm -rf biome.json
rm -rf phpstan.neon
rm -rf .git
rm -rf .github
echo "finished removing fluff"

#zip all of it
zip -r latest.zip .

#debug what is present
ls