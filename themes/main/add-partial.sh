#!/usr/bin/env bash
# Copies a partial from partials/blocks-library/<name> into
# partials/blocks/<name>, so extra components aren't bundled by default
# but are one command away when needed.
#
# Run from inside the theme directory: ./add-partial.sh <name>
set -euo pipefail

NAME="${1:?Usage: ./add-partial.sh <name>}"

SRC="partials/blocks-library/${NAME}"
DEST="partials/blocks/${NAME}"

if [ ! -d "$SRC" ]; then
  echo "ERROR: no such partial in library: $SRC" >&2
  echo "Available partials:" >&2
  ls partials/blocks-library/ >&2
  exit 1
fi

if [ -d "$DEST" ]; then
  echo "ERROR: $DEST already exists, not overwriting" >&2
  exit 1
fi

cp -R "$SRC" "$DEST"
echo "==> Copied $SRC -> $DEST"
echo "    Remember to add the {% partial %} include wherever you want it used."
