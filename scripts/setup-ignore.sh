#!/bin/bash

# This script is designed to ignore modifications to specific tracked files.
# Data files are versioned in the repository as a "backup" in case they become unavailable via the API.
# However, these files are automatically updated multiple times per week (either via the site's cron job or GitHub Actions automation).
# Without this script, every project user would frequently see diffs on these files, which would be very inconvenient.
# This script should be executed during project initialization.

FILES=(
    "web/modules/custom/jpf_store/assets/doc/v1/loto.csv"
    "web/modules/custom/jpf_store/assets/doc/v2/nouveau_loto.csv"
    "web/modules/custom/jpf_store/assets/doc/v3/loto2017.csv"
    "web/modules/custom/jpf_store/assets/doc/v4/loto_201902.csv"
    "web/modules/custom/jpf_store/assets/doc/v5/loto_201911.csv"
)

for FILE in "${FILES[@]}"; do
    git update-index --skip-worktree "$FILE"
done
