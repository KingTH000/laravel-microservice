#!/bin/bash

# A simple script to stop all running containers.

echo "Stopping all microservice containers..."
docker compose down -v
echo "All services stopped."