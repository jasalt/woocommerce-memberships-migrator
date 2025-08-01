# Helper script defining project REPL command (experimental)

# Using:
# - VVV https://github.com/Varying-Vagrant-Vagrants/VVV/
# - bvv tool https://codeberg.org/jasalt/bvv
~/.local/bin/bvv ssh vendor/bin/phel repl

# To use with docker compose see docs https://github.com/jasalt/phel-wp-plugin e.g. (not tested):
# docker compose exec -w /var/www/html/wp-content/plugins/woocommerce-memberships-migrator wordpress vendor/bin/phel repl
