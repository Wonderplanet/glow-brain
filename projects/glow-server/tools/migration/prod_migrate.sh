#!/bin/bash

MIGRATION_ENV="prod"

source "$(dirname "$0")/migrate_common.sh"
parse_options "$@"
run_migration
