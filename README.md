![GitHub release](https://flat.badgen.net/github/release/arthurperton/statamic-popular)
![Statamic](https://flat.badgen.net/badge/Statamic/6.0+/FF269E)

# Popular – addon for Statamic

Want to track popular content or show a top 5 of your most read blog posts? Popular is the missing page view tracker for your Statamic website. Fast, batteries included and build for scale.

Popular registers all page views for individual pages and entries.

- Always know which content is the most popular with your visitors
- Create lists for popular items, for example top 5 most popular blog posts
- Show a read count for each article on your frontend

## Features

- Easy setup
- Automatically adds a Pageviews field to your publish forms in the Control Panel
- Adds a computed field to your entries
- Includes a dedicated tag
- Dashboard widget

## Documentation

Have a look at the full [documentation](https://statamic.com/addons/arthurperton/popular/docs) for more details.

## Templating Example

To show a top 5 of your most read blog posts, simply do this:

```antlers
<ol>
    {{ collection from="blog" limit="5" sort="pageviews:desc" }}
        <li>{{ title }} ({{ pageviews }})</li>
    {{ /collection }}
</ol>
```

## Requirements

- PHP 8.3+
- Statamic v6+

Popular uses the SQLite3 PHP extension, which is enabled by default.

## License

Popular is **commercial software** but has an open-source codebase. If you want to use it in production, you'll need to [buy a license from the Statamic Marketplace](https://statamic.com/addons/arthurperton/popular).

## Credits

Developed by [Arthur Perton](https://github.com/arthurperton)
