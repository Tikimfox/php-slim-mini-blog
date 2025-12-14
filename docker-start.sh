#!/bin/bash
set -e

# Ensure only mpm_prefork is enabled
echo "Configuring Apache MPM modules..."

# Disable all MPM modules
a2dismod mpm_event mpm_worker 2>/dev/null || true

# Enable only mpm_prefork
a2enmod mpm_prefork

# Verify configuration
echo "Checking Apache configuration..."
apache2ctl -t

# Start Apache
echo "Starting Apache..."
exec apache2-foreground

