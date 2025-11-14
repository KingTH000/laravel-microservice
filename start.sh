#!/bin/bash

# A script to build, deploy, and migrate the entire microservice application.
# This is a "destructive" script, perfect for a clean reset.

# --- Define Colors ---
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Stop the script if any command fails
set -e

# --- 1. STOP & DESTROY ---
echo -e "${YELLOW}Step 1/7: Stopping all running containers and removing old volumes...${NC}"
docker compose down -v

# --- 2. DELETE OLD DB MOUNT ---
echo -e "${YELLOW}Step 2/7: Deleting old database bind mount to force re-initialization...${NC}"
rm -rf ./db-data

# --- 3. BUILD & START ---
echo -e "${YELLOW}Step 3/7: Building and starting all containers in detached mode except auth-worker...${NC}"
docker compose up --build -d --scale auth_worker=0

# --- 4. WAIT FOR MYSQL ---
echo -e "${YELLOW}Step 4/7: Waiting 10 seconds for MySQL to fully initialize...${NC}"
# This is crucial. We must wait for the database container
# to be ready before we can run migrations.
sleep 10

# --- 5. CLEAR CACHES ---
echo -e "${YELLOW}Step 5/7: Clearing all Laravel config caches...${NC}"
docker compose exec gateway php artisan config:clear
docker compose exec auth php artisan config:clear
docker compose exec profile php artisan config:clear

# --- 6. RUN MIGRATIONS ---
echo -e "${YELLOW}Step 6/7: Running migrations for all services...${NC}"
echo "Running migrations for 'gateway'..."
docker compose exec gateway php artisan migrate

echo "Running migrations for 'auth'..."
docker compose exec auth php artisan migrate

echo "Running migrations for 'profile'..."
docker compose exec profile php artisan migrate

echo -e "${YELLOW}Step 7/7: Starting auth_worker now that database is ready...${NC}"
docker compose up -d auth_worker

# --- 7. DONE ---
echo -e "\n${GREEN}--- SUCCESS! ---${NC}"
echo -e "All services are built, migrated, and running."
echo -e "Your application is ready at: ${GREEN}http://localhost${NC}\n"
docker compose ps