# CHANGES.md - Samuel Parente

## Part 1 — Code Review & Fixes

### 1. Database Configuration & Scalability
* **Issue:** The project was initially configured to use SQLite with commented-out MySQL variables, which contradicts the technical requirements for production-level scalability and the specific trial constraints.
* **Why it matters:** The challenge requires MySQL 8 / MariaDB 11. SQLite is not suitable for high-concurrency systems like email campaign dispatchers where multiple Queue Workers might write to the database simultaneously.
* **Fix:** Updated the environment configuration to MySQL 8, uncommented the necessary DB variables, and defined the database name as `trial-campaigns-db`. This ensures support for proper indexing, foreign key constraints, and performance. Also updated the .env.example
