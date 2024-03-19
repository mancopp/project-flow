# Project Flow website

## Setup

1. Create `.env` based on `.env.example`.
2. Add db credentials in the `.env` file.
3. Start docker containers:
   - for dev: `make dev`

## Symfony cli

Access to Symfony cli inside the container:

1. `make ssh`
2. `php bin/console <...>`
