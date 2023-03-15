#!/bin/bash
# Read the input JSON object
eval "$(jq -r '@sh "QUERY=\(.id)"')"
# Do some processing based on the query value
branch=$(git symbolic-ref -q --short HEAD)
if [ -n "$branch" ]; then
  RESULT=$branch
else
  tag=$(git describe --exact-match --tags 2> /dev/null)
  if [ -n "$tag" ]; then
    RESULT=$tag
  else
    RESULT=""
  fi
fi
# Write the output JSON object
jq -n --arg tag "$tag" --arg branch "$branch" '{"tag":$tag, "branch":$branch}'