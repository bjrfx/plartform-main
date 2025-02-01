# ReCo Anywhere Cloud Platform

### All rights are reserved!
### No part of this software may be reproduced, distributed, or transmitted in any form or by any means, including photocopying, recording, or other electronic or mechanical methods, without the prior written permission of the author, except as permitted by explicit agreement.

---

# Project Setup and Execution Guide

This README file provides detailed instructions for setting up and executing the project, including configuring the database and resolving common issues during development.

---

## Getting Started

### Prerequisites
Ensure you have the following tools installed on your local machine:
- **Docker** and **Docker Compose**
- **DBeaver IDE** (optional for database management)

---

## Running the Project

1. **Start the Docker Containers**  
   From the root of the project, execute the following commands in your terminal:
   ```bash
   docker-compose down
   docker-compose up -d --build
   ```
   - This will build and start all required containers in detached mode.
   - The docker-compose down command ensures that any previously running containers are stopped to avoid conflicts.

2. **Nginx Routing**
      The Nginx container is configured to:
      - Route all domains and subdomains to the frontend.
      -	Inspect requests for the /api path and automatically map them to the backend as /api requests.

---

## Database Configuration

**Setting Up Environment Files**

The database runs in its own container and requires configuration in two separate .env files:

1. **Root** .env **File**

    Define your database environment variables for Docker Compose in the root .env file.

2. **Backend** .env **File**

    For Laravel usage, define the same database environment variables in the backend/.env file.

## Running Database Migrations

**Execute Migrations**

Run the following command from the project root to execute Laravel migrations:
```bash
docker-compose exec backend php artisan migrate
```
## Local Development Note
For local development, a “patch” is included in the AppServiceProvider to allow running migrations directly under the backend folder:
```bash
cd backend
php artisan migrate
```

### DBeaver IDE Configuration (Optional)

If you’re using **DBeaver IDE** to connect to the database locally, you might encounter the following error:

**“Public Key Retrieval is not allowed”**

**Resolving the Error**

1. Right-click your database connection and select **Edit Connection**.
2. On the **Connection Settings** screen:
    - Navigate to the **Driver Properties** tab.
3. Update the following properties:
   - allowPublicKeyRetrieval: Set this to true.
4. If you still see this error:
   - useSSL: Set this to false.

---

<br/>
<br/>
<br/>
<br/>
<br/>
