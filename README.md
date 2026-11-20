Missions.dev
========

[Missions.dev](https://www.missions.dev/) (Hugmyndaþorp) is a platform for co-creation created by [East of Moon]([https://www.missions.dev/](https://www.eastofmoon.com/))  (Austan mána ehf) 

## Installation

Tests still need to be reworked.

**Run it locally (OrbStack / Docker):** `make up`, then open http://localhost:5173. See [docs/local-development.md](docs/local-development.md).

## Documentation

Architecture, API reference, domain model and known issues are in [docs/](docs/README.md).
  
## Structure
 
#### /backend

Where the API lives. Written in PHP, loosely uses Silex a light framework.

#### /frontend

The front end, based on Vue3.

#### /public_html

The publicly accessible part which pulls from the backend and frontend.
