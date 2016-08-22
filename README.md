WordPress Simple Search Operators
=================================

A simple WordPress plugin that allows you to use syntax like `author:jimmy tag:personal sunday` to search WordPress, and restrict your results to those written by Author (slug) 'jimmy', tagged with 'personal' and containing the string 'sunday'.

No UI, no configuration, just drop it in (works in mu-plugins if you'd like to set-and-forget), and it will spice up your searching experience a little.

Supported operators:

- `author:` match the slug of an author to list their posts
- `category:` to show a specific category
- `tag:` to show posts tagged with a specific tag
- `format:` or `type:` to restrict results to a specific "post format" (e.g. image, quote, aside, etc)
- `not:` to only show results that _don't_ contain a string (native support in WP by preceeding strings with `-`)

You can combine operators and string search (e.g. 'tag:burrito type:image carnitas').
