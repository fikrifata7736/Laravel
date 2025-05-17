**ERD Diagram and Project Setup**
Entity Relationship Diagram (ERD)
+-------------------+          +-------------------+          +-------------------+
|       User        |          |       Task        |          |    ActivityLog    |
+-------------------+          +-------------------+          +-------------------+
| id (UUID)         |<---------| assigned_to (UUID)|          | id (UUID)         |
| name              |          | created_by (UUID) |--------->| user_id (UUID)    |
| email             |          | id (UUID)         |          | action            |
| password          |          | title             |          | description       |
| role              |          | description       |          | logged_at         |
| status            |          | status            |          +-------------------+
+-------------------+          | due_date          |
                               +-------------------+


**Setup**

PHP version 8.2.4
Composer version 2.8.9 
MySQL (Phpmyadmin)
Laravel 10+

