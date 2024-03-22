# Build local dev environment.
dev-build:
	docker compose build

# Start local dev environment.
up:
	docker compose up -d

# Stop local dev environment.
dev-down:
	docker compose down

# Enter web container cli.
ssh:
	docker exec -it project-flow-web-1 bash