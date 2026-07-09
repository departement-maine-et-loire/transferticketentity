#!/bin/bash

# Only strings with domain specified are extracted (use Xt args of keyword param to set number of args needed)

find . -name '*.php' | xargs xgettext \
  --copyright-holder='Transferticketentity Development Team' \
  --package-name='GLPI - Transferticketentity plugin' \
  --package-version='1.1.4' \
  -o locales/transferticketentity-php.pot \
  -L PHP \
  --add-comments=TRANS \
  --from-code=UTF-8 \
  --force-po \
  --keyword=_n:1,2,4t \
  --keyword=__s:1,2t \
  --keyword=__:1,2t \
  --keyword=_e:1,2t \
  --keyword=_x:1c,2,3t \
  --keyword=_ex:1c,2,3t \
  --keyword=_nx:1c,2,3,5t \
  --keyword=_sx:1c,2,3t

find . -name '*.js' | xargs xgettext \
  --copyright-holder='Transferticketentity Development Team' \
  --package-name='GLPI - Transferticketentity plugin' \
  --package-version='1.1.4' \
  -o locales/transferticketentity-js.pot \
  -L JavaScript \
  --add-comments=TRANS \
  --from-code=UTF-8 \
  --force-po \
  --keyword=__:1,2t \
  --keyword=_x:1c,2t \
  --keyword=_nx:1,2,3,5t

msgcat --use-first locales/transferticketentity-php.pot locales/transferticketentity-js.pot -o locales/transferticketentity.pot
