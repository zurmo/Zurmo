rem Mercurial does not store empty directories.
rem This script will create the directories
rem needed by the application that were
rem empty when the webapp was created.

mkdir app\assets
mkdir app\protected\data
mkdir app\protected\messages
mkdir app\protected\runtime
