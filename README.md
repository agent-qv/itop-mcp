# itop-mcp
A MCP server for iTop.

This MCP server is a stand-alone application which interacts with iTop using the standard REST/JSON webservices. For now the project is still a **prototype**, use it at your own risks!

The project is based on the Symfony "mcp-bundle". It is a full blown Symfony web application, so the usual Symfony configuration rules apply (especially it requires `mod_rewrite` if you use apache as the web server).

The datamodel (`datamodel-production.xml`) of the target iTop instance MUST BE copied to the data folder of the project.

The complete dopcumentation is available here: https://www.itophub.io/wiki/page?id=extensions:itop-mcp

# Installation

1) Run:

```
composer install
```

2) Grab the file `data/datamodel-production.xml` from your iTop instance and copy it to the `data` folder of the project.

# Configuration

Make sure that your webserver can serve the `/public` folder

Then browse to the URL serving `/public` and follow the on-screen instructions !

Configure the `itop-mcp` server in your favorite IA agent...

# Tips

The datamodel as well as the list of available webservices are cached on the MCP side (with a TTL of 3600 s).

To clear the cache run the CLI command:

```
bin/console cache:pool:clear --all
```

# Contributing

You are welcome to play with the iTop MCP server and contribute to it. Please use the "Issues" feature of GitHub to report your findings (bugs, ideas for enhancements, etc.) **before** creating a Pull Request.
