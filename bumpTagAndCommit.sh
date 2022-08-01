#!/bin/bash
set -e

LASTVERSION=$(git describe --tags --abbrev=0 | sed -n "s/^\(.*\)$/\1/p" | awk -F. '{OFS="."; printf("%d.%d.%d", $0, $1, $NF)}')

NEWVERSION=$(git describe --tags --abbrev=0 | sed -n "s/^\(.*\)$/\1/p" | awk -F. '{OFS="."; printf("%d.%d.%d", $0, $1, $NF+1)}')

echo "last version is $LASTVERSION, going to $NEWVERSION"

sed -i '' "s/${LASTVERSION}/${NEWVERSION}/" app/release_metadata.json

COMMIT_MSG=
while [ -z "${COMMIT_MSG}" ]; do
    read -p "Enter commit message: " COMMIT_MSG
done


echo ""
echo "Version:\t$NEWVERSION"
echo "Commit:\t\t$COMMIT_MSG"
echo ""

read -p "Continue (y/n)? " CONT
if [ "$CONT" = "y" ]; then

    git add .
    git commit -m "$COMMIT_MSG"
    git tag $NEWVERSION
    git push origin $NEWVERSION
    git push origin $(git branch --show-current)
fi
