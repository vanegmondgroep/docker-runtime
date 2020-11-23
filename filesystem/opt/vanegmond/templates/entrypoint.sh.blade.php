#!/bin/bash

set -a
set -e
set -o pipefail

# ----- Filesystem ----- #

@if(env('UPDATE_FILE_PERMISSIONS', false))
runtime log "Updating file permissions"
sudo chown -R www-data:www-data /opt/vanegmond/app
@endif

mkdir -p $COMPOSER_HOME
mkdir -p $APP_PATH_PUBLIC
mkdir -p $APP_PATH_AUTH
mkdir -p $APP_PATH_LOGS
mkdir -p $APP_PATH_DEPLOY
mkdir -p $APP_PATH_DEPLOY_DATA

# ----- SSH-Keys ----- #

@if($user['privateKey'])
runtime log "Saving user private key"
echo "{{ $user['privateKey'] }}" > $APP_PATH_AUTH/id_rsa
chmod 600 $APP_PATH_AUTH/id_rsa
@endif

@if(count($user['authorizedKeys']) > 0)
runtime log "Saving user authorized keys"
echo "{{ implode("\n", $user['authorizedKeys']) }}" > $APP_PATH_AUTH/id_rsa
chmod 600 $APP_PATH_AUTH/id_rsa
@endif

# ----- Basic Auth ----- #

@foreach($server['basicAuth'] as $key=>$auth) 
runtime log "Setup htpasswd file for path '{{ $auth['location'] }}'"
echo '{{ $auth['users'] }}' > $APP_PATH_AUTH/{{ $key }}-htpasswd
@endforeach

# ----- User Mods ----- #

if [ "$1" != '--skip-usermods' ]; then
@if($user['password'])
runtime log "Updating user password"
echo 'www-data:{{ $user['password'] }}' | sudo -S chpasswd -e
@endif

@if($user['name'])
runtime log "Updating user name"
sudo usermod -l {{ $user['name'] }} -s /bin/bash www-data
@else
runtime log "Removing sudo privileges"
sudo sed -i '$ d' /etc/sudoers
@endif
fi

# ----- End ----- #