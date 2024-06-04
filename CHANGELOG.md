## CHANGELOG

### v4.0 - released June 4th, 2024
- Allow parsing WEBP image files
- Switch to cURL as primary method of feed fetching
- Allow overriding sub-template when working on a dev template in Joomla
- Add order option for feed items (date desc, date asc, none, random)
- Bypass SSL validation when using file_get_contents to fetch a feed
- Add cachemode field in XML params (improves caching in J3)
- Properly handle content collisions in the created feed index
