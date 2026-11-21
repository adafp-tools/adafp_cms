#!/usr/bin/env bash
# Download self-hosted fonts for Party Sites CMS.
# Run once from the project root: bash scripts/download-fonts.sh
# Requires: curl, unzip
# Fonts are OFL-licensed (open source, may be bundled and self-hosted).

set -euo pipefail

FONTS_DIR="public/fonts"
mkdir -p "$FONTS_DIR/inter" "$FONTS_DIR/playfair"

echo "Downloading Inter variable font..."
curl -fsSL "https://github.com/rsms/inter/releases/download/v4.1/Inter-4.1.zip" \
    -o /tmp/inter.zip
unzip -q /tmp/inter.zip "InterVariable.woff2" "InterVariable-Italic.woff2" \
    -d "$FONTS_DIR/inter" || \
  unzip -q /tmp/inter.zip -j "*/InterVariable.woff2" "*/InterVariable-Italic.woff2" \
    -d "$FONTS_DIR/inter"
rm /tmp/inter.zip
echo "  Inter done."

echo "Downloading Playfair Display variable font..."
# Google Fonts CSS API — download static woff2 files
# Variable woff2 from the GitHub repo
curl -fsSL \
  "https://github.com/clauseggers/Playfair-Display/raw/master/fonts/variable/PlayfairDisplay%5Bwdth%2Cwght%5D.woff2" \
  -o "$FONTS_DIR/playfair/PlayfairDisplay-Variable.woff2" || {
    echo "  WARNING: Could not auto-download Playfair Display."
    echo "  Download manually from https://fonts.google.com/specimen/Playfair+Display"
    echo "  and save as: $FONTS_DIR/playfair/PlayfairDisplay-Variable.woff2"
    echo "                $FONTS_DIR/playfair/PlayfairDisplay-Italic-Variable.woff2"
  }
echo "  Playfair Display done (or see warning above)."

echo ""
echo "Fonts written to $FONTS_DIR/"
ls -lh "$FONTS_DIR/inter/" "$FONTS_DIR/playfair/" 2>/dev/null || true
