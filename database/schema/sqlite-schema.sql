CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" varchar not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE TABLE IF NOT EXISTS "clients"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "name" varchar not null,
  "email" varchar not null,
  "contact_person" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "phone" varchar,
  "address" text,
  "hourly_rate" numeric,
  "status" varchar check("status" in('active', 'inactive')) not null default 'active',
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "clients_user_id_created_at_index" on "clients"(
  "user_id",
  "created_at"
);
CREATE UNIQUE INDEX "clients_email_unique" on "clients"("email");
CREATE TABLE IF NOT EXISTS "projects"(
  "id" integer primary key autoincrement not null,
  "client_id" integer not null,
  "title" varchar not null,
  "description" text,
  "status" varchar check("status" in('active', 'completed')) not null default 'active',
  "deadline" date,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("client_id") references "clients"("id") on delete cascade
);
CREATE INDEX "projects_client_id_status_index" on "projects"(
  "client_id",
  "status"
);
CREATE INDEX "projects_status_created_at_index" on "projects"(
  "status",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "time_logs"(
  "id" integer primary key autoincrement not null,
  "project_id" integer not null,
  "start_time" datetime not null,
  "end_time" datetime,
  "description" text,
  "hours" numeric,
  "is_billable" tinyint(1) not null default '1',
  "tags" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("project_id") references "projects"("id") on delete cascade
);
CREATE INDEX "time_logs_project_id_start_time_index" on "time_logs"(
  "project_id",
  "start_time"
);
CREATE INDEX "time_logs_start_time_end_time_index" on "time_logs"(
  "start_time",
  "end_time"
);
CREATE INDEX "time_logs_is_billable_start_time_index" on "time_logs"(
  "is_billable",
  "start_time"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_05_28_083559_create_personal_access_tokens_table',1);
INSERT INTO migrations VALUES(5,'2025_05_28_083605_create_clients_table',1);
INSERT INTO migrations VALUES(6,'2025_05_28_083606_create_projects_table',1);
INSERT INTO migrations VALUES(7,'2025_05_28_083606_create_time_logs_table',1);
INSERT INTO migrations VALUES(8,'2025_05_28_093833_add_additional_fields_to_clients_table',1);
