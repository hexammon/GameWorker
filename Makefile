build-docker-base:
	docker-compose -f docker-compose-base.yml -f docker-compose-build.yml build

build-docker-dev:
	docker-compose -f docker-compose-base.yml -f docker-compose-build.yml -f docker-compose-dev.yml build

run-docker-dev:
	docker-compose -f docker-compose-base.yml -f docker-compose-dev.yml up -d
