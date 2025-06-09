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
  "updated_at" datetime,
  "role" varchar check("role" in('admin', 'agent', 'customer')) not null default 'customer',
  "avatar_path" varchar
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
CREATE TABLE IF NOT EXISTS "office_user"(
  "id" integer primary key autoincrement not null,
  "office_id" integer not null,
  "user_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("office_id") references "offices"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "office_user_office_id_user_id_unique" on "office_user"(
  "office_id",
  "user_id"
);
CREATE TABLE IF NOT EXISTS "offices"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "is_internal" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "attachments"(
  "id" integer primary key autoincrement not null,
  "filename" varchar not null,
  "path" varchar not null,
  "mime_type" varchar not null,
  "attachable_type" varchar not null,
  "attachable_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "attachments_attachable_type_attachable_id_index" on "attachments"(
  "attachable_type",
  "attachable_id"
);
CREATE TABLE IF NOT EXISTS "ticket_priorities"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "color" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "sort_order" integer not null default '0'
);
CREATE TABLE IF NOT EXISTS "ticket_replies"(
  "id" integer primary key autoincrement not null,
  "content" text not null,
  "is_internal_note" tinyint(1) not null default '0',
  "user_id" integer not null,
  "ticket_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "is_internal" tinyint(1) not null default '0',
  foreign key("user_id") references "users"("id"),
  foreign key("ticket_id") references "tickets"("id") on delete cascade
);
CREATE INDEX "ticket_replies_ticket_id_created_at_index" on "ticket_replies"(
  "ticket_id",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "ticket_statuses"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "color" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "ticket_timelines"(
  "id" integer primary key autoincrement not null,
  "entry" text not null,
  "ticket_id" integer not null,
  "user_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ticket_id") references "tickets"("id") on delete cascade,
  foreign key("user_id") references "users"("id")
);
CREATE INDEX "ticket_timelines_ticket_id_created_at_index" on "ticket_timelines"(
  "ticket_id",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "f_a_q_s"(
  "id" integer primary key autoincrement not null,
  "question" varchar not null,
  "answer" text not null,
  "office_id" integer,
  "sort_order" integer not null default '0',
  "is_published" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("office_id") references "offices"("id") on delete set null
);
CREATE INDEX "f_a_q_s_is_published_sort_order_index" on "f_a_q_s"(
  "is_published",
  "sort_order"
);
CREATE INDEX "f_a_q_s_office_id_is_published_index" on "f_a_q_s"(
  "office_id",
  "is_published"
);
CREATE TABLE IF NOT EXISTS "searchable_indexes"(
  "id" integer primary key autoincrement not null,
  "searchable_type" varchar not null,
  "searchable_id" integer not null,
  "content" text not null,
  "type" varchar not null,
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "searchable_indexes_searchable_type_searchable_id_index" on "searchable_indexes"(
  "searchable_type",
  "searchable_id"
);
CREATE INDEX "searchable_indexes_type_index" on "searchable_indexes"("type");
CREATE TABLE IF NOT EXISTS "s_l_a_s"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "office_id" integer not null,
  "ticket_priority_id" integer not null,
  "response_time_minutes" integer not null,
  "resolution_time_minutes" integer not null,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("office_id") references "offices"("id") on delete cascade,
  foreign key("ticket_priority_id") references "ticket_priorities"("id") on delete cascade
);
CREATE UNIQUE INDEX "s_l_a_s_office_id_ticket_priority_id_unique" on "s_l_a_s"(
  "office_id",
  "ticket_priority_id"
);
CREATE INDEX "ticket_replies_is_internal_index" on "ticket_replies"(
  "is_internal"
);
CREATE TABLE IF NOT EXISTS "canned_responses"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "content" text not null,
  "category" varchar,
  "variables" text,
  "user_id" integer not null,
  "is_public" tinyint(1) not null default '0',
  "is_active" tinyint(1) not null default '1',
  "usage_count" integer not null default '0',
  "last_used_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "canned_responses_category_is_active_index" on "canned_responses"(
  "category",
  "is_active"
);
CREATE INDEX "canned_responses_user_id_is_active_index" on "canned_responses"(
  "user_id",
  "is_active"
);
CREATE INDEX "canned_responses_is_public_index" on "canned_responses"(
  "is_public"
);
CREATE TABLE IF NOT EXISTS "saved_searches"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "search_params" text not null,
  "user_id" integer not null,
  "is_public" tinyint(1) not null default '0',
  "usage_count" integer not null default '0',
  "last_used_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "saved_searches_user_id_is_public_index" on "saved_searches"(
  "user_id",
  "is_public"
);
CREATE INDEX "saved_searches_usage_count_index" on "saved_searches"(
  "usage_count"
);
CREATE TABLE IF NOT EXISTS "tickets"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "subject" varchar not null,
  "content" text not null,
  "creator_id" integer not null,
  "assigned_to_id" integer,
  "office_id" integer not null,
  "ticket_status_id" integer not null,
  "ticket_priority_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "sla_id" integer,
  "sla_response_due_at" datetime,
  "sla_resolution_due_at" datetime,
  "first_response_at" datetime,
  "resolved_at" datetime,
  "sla_response_breached" tinyint(1) not null default('0'),
  "sla_resolution_breached" tinyint(1) not null default('0'),
  "merged_into_id" varchar,
  "merged_at" datetime,
  "merged_by_id" integer,
  "merge_reason" text,
  "is_merged" tinyint(1) not null default '0',
  foreign key("sla_id") references s_l_a_s("id") on delete set null on update no action,
  foreign key("creator_id") references users("id") on delete no action on update no action,
  foreign key("assigned_to_id") references users("id") on delete no action on update no action,
  foreign key("office_id") references offices("id") on delete no action on update no action,
  foreign key("ticket_status_id") references ticket_statuses("id") on delete no action on update no action,
  foreign key("ticket_priority_id") references ticket_priorities("id") on delete no action on update no action,
  foreign key("merged_into_id") references "tickets"("uuid") on delete set null,
  foreign key("merged_by_id") references "users"("id") on delete set null
);
CREATE INDEX "tickets_assigned_to_id_ticket_status_id_index" on "tickets"(
  "assigned_to_id",
  "ticket_status_id"
);
CREATE INDEX "tickets_office_id_ticket_status_id_index" on "tickets"(
  "office_id",
  "ticket_status_id"
);
CREATE INDEX "tickets_ticket_status_id_updated_at_index" on "tickets"(
  "ticket_status_id",
  "updated_at"
);
CREATE UNIQUE INDEX "tickets_uuid_unique" on "tickets"("uuid");
CREATE INDEX "tickets_merged_into_id_index" on "tickets"("merged_into_id");
CREATE INDEX "tickets_is_merged_index" on "tickets"("is_merged");
CREATE TABLE IF NOT EXISTS "email_templates"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "subject" varchar not null,
  "content" text not null,
  "type" varchar not null default 'markdown',
  "category" varchar not null default 'general',
  "is_active" tinyint(1) not null default '1',
  "is_default" tinyint(1) not null default '0',
  "variables" text,
  "language" varchar not null default 'en',
  "description" text,
  "created_by_id" integer,
  "updated_by_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("created_by_id") references "users"("id") on delete set null,
  foreign key("updated_by_id") references "users"("id") on delete set null
);
CREATE INDEX "email_templates_name_language_index" on "email_templates"(
  "name",
  "language"
);
CREATE INDEX "email_templates_category_is_active_index" on "email_templates"(
  "category",
  "is_active"
);
CREATE INDEX "email_templates_is_default_index" on "email_templates"(
  "is_default"
);
CREATE UNIQUE INDEX "email_templates_name_language_unique" on "email_templates"(
  "name",
  "language"
);
CREATE TABLE IF NOT EXISTS "faq_usage_tracking"(
  "id" integer primary key autoincrement not null,
  "faq_id" integer not null,
  "ticket_id" integer not null,
  "user_id" integer not null,
  "context" varchar not null default 'reply_insertion',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("faq_id") references "f_a_q_s"("id") on delete cascade,
  foreign key("ticket_id") references "tickets"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE INDEX "faq_usage_tracking_faq_id_created_at_index" on "faq_usage_tracking"(
  "faq_id",
  "created_at"
);
CREATE INDEX "faq_usage_tracking_ticket_id_created_at_index" on "faq_usage_tracking"(
  "ticket_id",
  "created_at"
);
CREATE INDEX "faq_usage_tracking_user_id_created_at_index" on "faq_usage_tracking"(
  "user_id",
  "created_at"
);
CREATE INDEX "faq_usage_tracking_context_index" on "faq_usage_tracking"(
  "context"
);
CREATE INDEX "faq_usage_tracking_created_at_index" on "faq_usage_tracking"(
  "created_at"
);
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

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_06_06_053428_add_role_to_users_table',2);
INSERT INTO migrations VALUES(5,'2025_06_06_053434_create_office_user_table',2);
INSERT INTO migrations VALUES(6,'2025_06_06_053434_create_offices_table',2);
INSERT INTO migrations VALUES(7,'2025_06_06_053435_create_attachments_table',2);
INSERT INTO migrations VALUES(8,'2025_06_06_053435_create_ticket_priorities_table',2);
INSERT INTO migrations VALUES(9,'2025_06_06_053435_create_ticket_replies_table',2);
INSERT INTO migrations VALUES(10,'2025_06_06_053435_create_ticket_statuses_table',2);
INSERT INTO migrations VALUES(11,'2025_06_06_053435_create_ticket_timelines_table',2);
INSERT INTO migrations VALUES(12,'2025_06_06_053435_create_tickets_table',2);
INSERT INTO migrations VALUES(14,'2025_06_06_061154_create_f_a_q_s_table',3);
INSERT INTO migrations VALUES(15,'2025_06_09_013527_create_searchable_indexes',4);
INSERT INTO migrations VALUES(16,'2025_06_09_014656_create_s_l_a_s_table',5);
INSERT INTO migrations VALUES(17,'2025_06_09_014707_add_sla_fields_to_tickets_table',5);
INSERT INTO migrations VALUES(18,'2025_06_09_020637_add_avatar_path_to_users_table',6);
INSERT INTO migrations VALUES(20,'2025_06_09_031046_add_is_internal_to_ticket_replies_table',7);
INSERT INTO migrations VALUES(21,'2025_06_09_034159_create_canned_responses_table',8);
INSERT INTO migrations VALUES(22,'2025_06_09_040734_create_saved_searches_table',9);
INSERT INTO migrations VALUES(23,'2025_06_09_042945_add_merging_fields_to_tickets_table',10);
INSERT INTO migrations VALUES(24,'2025_06_09_044958_create_email_templates_table',11);
INSERT INTO migrations VALUES(25,'2025_06_09_045827_update_email_templates_unique_constraint',12);
INSERT INTO migrations VALUES(26,'2025_06_09_060507_create_faq_usage_tracking_table',13);
INSERT INTO migrations VALUES(27,'2025_06_09_062712_create_personal_access_tokens_table',14);
INSERT INTO migrations VALUES(28,'2025_06_09_100939_add_sort_order_to_ticket_priorities_table',15);
