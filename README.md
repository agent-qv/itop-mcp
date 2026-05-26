# itop-mcp
**Prototype** of a MCP server for iTop

This prototype is based on the Symfony "mcp-bundle". The project is a full blown Symfony web application, so the usual Symfony configuration rules apply (especially it requires `mod_rewrite` if you use apache as the web server).

The MCP server interacts with iTop using the standard REST/JSON webservices.

The datamodel (`datamodel-production.xml`) of the target iTop instance MUST BE copied to the data folder of the project.

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
