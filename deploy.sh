#!/bin/bash

# ========= CONFIG =========
LOCAL_PATH="/mnt/e/ProgramingWorkspace/WebWS/WPress/LocalSites/piano-nam-dinh/app/public"
      # e.g. ~/LocalSites/mysite/app/public
LOCAL_DB="local"
LOCAL_DB_USER="root"
LOCAL_DB_PASS="root"
LOCAL_DB_PORT="10041"

REMOTE_USER="pianonam"
REMOTE_HOST="pianonamdinh.com.vn"                   # HawkHost domain or server IP
REMOTE_PATH="/home/pianonam/public_html"

# HawkHost DB
REMOTE_DB="pianonam_wp_eeq3f"
REMOTE_DB_USER="pianonam_pianonam_wp_vxgbn"
REMOTE_DB_PASS="xKjRu{@qbDm$,7^o"
REMOTE_DB_HOST="localhost:3306"

LOCAL_URL="http://pianonamdinh.com.vn.local"
REMOTE_URL="https://pianonamdinh.com.vn"
# ==========================

CODE_ONLY=false
if [[ "$1" == "--code-only" ]]; then
  CODE_ONLY=true
fi

echo "📂 Syncing wp-content..."
rsync -avz --delete "$LOCAL_PATH/wp-content/" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/wp-content/"

# echo "💾 Exporting local database..."
# mysqldump -h "$LOCAL_DB_HOST" -P "$LOCAL_DB_PORT" -u "$LOCAL_DB_USER" --password="$LOCAL_DB_PASS" "$LOCAL_DB_NAME" > local_db.sql
# if [ $? -ne 0 ]; then
#   echo "❌ Database export failed!"
#   exit 1
# fi

echo "📤 Uploading DB dump..."
scp local_db.sql $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/local_db.sql

echo "🛠 Importing DB on HawkHost..."
ssh $REMOTE_USER@$REMOTE_HOST "mysql -u'$REMOTE_DB_USER' -p'$REMOTE_DB_PASS' $REMOTE_DB < $REMOTE_PATH/local_db.sql && rm $REMOTE_PATH/local_db.sql"

echo "🔍 Running WP-CLI search-replace..."
ssh $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_PATH && wp search-replace '$LOCAL_URL' '$REMOTE_URL' --skip-columns=guid --allow-root"

echo "✅ Deployment complete!"
