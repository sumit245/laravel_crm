#!/bin/bash
# ============================================================
# EC2 Nginx + PHP-FPM fix for large pole import uploads
# Run this on the EC2 server as sudo:
#   sudo bash deployment/ec2-nginx-fix.sh
# ============================================================

NGINX_CONF="/etc/nginx/nginx.conf"
SITE_CONF=$(ls /etc/nginx/sites-enabled/ 2>/dev/null | head -1)
SITE_CONF_PATH="/etc/nginx/sites-enabled/${SITE_CONF}"

echo "=== Applying Nginx timeout + upload fixes ==="

# Detect site config (sites-enabled or conf.d)
if [ -z "$SITE_CONF" ]; then
    SITE_CONF_PATH=$(ls /etc/nginx/conf.d/*.conf 2>/dev/null | head -1)
fi

echo "Nginx global config: $NGINX_CONF"
echo "Site config: $SITE_CONF_PATH"

# 1. Patch nginx.conf (http block) — increase client body size
if grep -q "client_max_body_size" "$NGINX_CONF"; then
    sed -i 's/client_max_body_size.*/client_max_body_size 600m;/' "$NGINX_CONF"
else
    sed -i '/http {/a\\tclient_max_body_size 600m;' "$NGINX_CONF"
fi

# 2. Patch site config — increase proxy/fastcgi timeouts
if [ -f "$SITE_CONF_PATH" ]; then
    # Add timeout directives inside the location block if not already present
    if ! grep -q "fastcgi_read_timeout" "$SITE_CONF_PATH"; then
        sed -i '/fastcgi_pass/a\\t\tfastcgi_read_timeout 3600;\n\t\tfastcgi_send_timeout 3600;\n\t\tfastcgi_connect_timeout 3600;' "$SITE_CONF_PATH"
    else
        sed -i 's/fastcgi_read_timeout.*/fastcgi_read_timeout 3600;/' "$SITE_CONF_PATH"
        sed -i 's/fastcgi_send_timeout.*/fastcgi_send_timeout 3600;/' "$SITE_CONF_PATH"
    fi

    if ! grep -q "client_max_body_size" "$SITE_CONF_PATH"; then
        sed -i '/server_name/a\\tclient_max_body_size 600m;' "$SITE_CONF_PATH"
    fi

    if ! grep -q "proxy_read_timeout" "$SITE_CONF_PATH"; then
        sed -i '/fastcgi_pass/a\\t\tproxy_read_timeout 3600;\n\t\tproxy_connect_timeout 3600;\n\t\tproxy_send_timeout 3600;' "$SITE_CONF_PATH"
    fi
fi

# 3. Patch PHP-FPM max_execution_time + upload limits
PHP_INI=$(find /etc/php -name php.ini 2>/dev/null | grep fpm | head -1)
echo "PHP FPM ini: $PHP_INI"
if [ -n "$PHP_INI" ]; then
    sed -i 's/^max_execution_time.*/max_execution_time = 0/' "$PHP_INI"
    sed -i 's/^upload_max_filesize.*/upload_max_filesize = 600M/' "$PHP_INI"
    sed -i 's/^post_max_size.*/post_max_size = 600M/' "$PHP_INI"
    sed -i 's/^memory_limit.*/memory_limit = 2048M/' "$PHP_INI"
    echo "PHP-FPM ini updated."
fi

# 4. Test nginx config
nginx -t && echo "Nginx config OK"

# 5. Reload
systemctl reload nginx && echo "Nginx reloaded OK"
PHP_FPM_SERVICE=$(systemctl list-units --type=service | grep php | grep fpm | awk '{print $1}' | head -1)
if [ -n "$PHP_FPM_SERVICE" ]; then
    systemctl reload "$PHP_FPM_SERVICE" && echo "PHP-FPM reloaded: $PHP_FPM_SERVICE"
fi

echo "=== Done. Gateway timeouts should be resolved. ==="
