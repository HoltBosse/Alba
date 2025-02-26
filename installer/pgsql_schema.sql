CREATE OR REPLACE FUNCTION update_updated_column()
RETURNS TRIGGER AS $$
BEGIN
NEW.updated = current_timestamp;
RETURN NEW;
END;
$$ language 'plpgsql';

DROP TABLE IF EXISTS "categories";
DROP SEQUENCE IF EXISTS categories_id_seq;
CREATE SEQUENCE categories_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."categories" (
    "id" integer DEFAULT nextval('categories_id_seq') NOT NULL,
    "state" integer DEFAULT '1' NOT NULL,
    "title" character varying(64) NOT NULL,
    "content_type" integer NOT NULL,
    "parent" integer DEFAULT '0' NOT NULL,
    CONSTRAINT "categories_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."categories"."content_type" IS '-1 media, -2 user, -3 tag';


DROP TABLE IF EXISTS "configurations";
CREATE TABLE "public"."configurations" (
    "name" character varying(255) NOT NULL,
    "configuration" text NOT NULL,
    CONSTRAINT "configurations_name" PRIMARY KEY ("name")
) WITH (oids = false);


DROP TABLE IF EXISTS "content_types";
DROP SEQUENCE IF EXISTS content_types_id_seq;
CREATE SEQUENCE content_types_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."content_types" (
    "id" integer DEFAULT nextval('content_types_id_seq') NOT NULL,
    "title" character varying(255) NOT NULL,
    "controller_location" character varying(255) NOT NULL,
    "description" text NOT NULL,
    "updated" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "state" smallint NOT NULL,
    CONSTRAINT "content_types_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "content_versions";
DROP SEQUENCE IF EXISTS content_versions_id_seq;
CREATE SEQUENCE content_versions_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."content_versions" (
    "id" integer DEFAULT nextval('content_versions_id_seq') NOT NULL,
    "content_id" integer NOT NULL,
    "created_by" integer NOT NULL,
    "created" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "fields_json" text NOT NULL,
    CONSTRAINT "content_versions_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "content_views";
DROP SEQUENCE IF EXISTS content_views_id_seq;
CREATE SEQUENCE content_views_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."content_views" (
    "id" integer DEFAULT nextval('content_views_id_seq') NOT NULL,
    "content_type_id" integer NOT NULL,
    "title" character varying(255) NOT NULL,
    "location" character varying(255) NOT NULL,
    "description" text,
    CONSTRAINT "content_views_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "controller_basic_html";
DROP SEQUENCE IF EXISTS controller_basic_html_id_seq;
CREATE SEQUENCE controller_basic_html_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."controller_basic_html" (
    "id" integer DEFAULT nextval('controller_basic_html_id_seq') NOT NULL,
    "state" smallint DEFAULT '1' NOT NULL,
    "ordering" integer DEFAULT '1' NOT NULL,
    "title" character varying(255) NOT NULL,
    "alias" character varying(255) NOT NULL,
    "content_type" integer NOT NULL,
    "start" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "end" timestamp,
    "created_by" integer NOT NULL,
    "updated_by" integer NOT NULL,
    "note" character varying(255) NOT NULL,
    "created" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "category" integer DEFAULT '0' NOT NULL,
    "updated" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "markup" text NOT NULL,
    "og_description" text NOT NULL,
    "seo_keywords" text NOT NULL,
    "og_title" text NOT NULL,
    "og_image" text NOT NULL,
    CONSTRAINT "controller_basic_html_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."controller_basic_html"."content_type" IS 'content_types table';

CREATE TRIGGER "controller_basic_html_update_timestamp" BEFORE UPDATE ON "public"."controller_basic_html" FOR EACH ROW EXECUTE FUNCTION update_updated_column();

DROP TABLE IF EXISTS "groups";
DROP SEQUENCE IF EXISTS groups_id_seq;
CREATE SEQUENCE groups_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."groups" (
    "id" integer DEFAULT nextval('groups_id_seq') NOT NULL,
    "value" character varying(64) NOT NULL,
    "display" character varying(64) NOT NULL,
    CONSTRAINT "groups_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "media";
DROP SEQUENCE IF EXISTS media_id_seq;
CREATE SEQUENCE media_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."media" (
    "id" integer DEFAULT nextval('media_id_seq') NOT NULL,
    "width" integer NOT NULL,
    "height" integer NOT NULL,
    "modified" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "title" character varying(255) NOT NULL,
    "alt" character varying(255) NOT NULL,
    "filename" character varying(255) NOT NULL,
    "mimetype" character varying(255) NOT NULL,
    CONSTRAINT "media_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "page_widget_overrides";
CREATE TABLE "public"."page_widget_overrides" (
    "page_id" integer NOT NULL,
    "position" character varying(255) NOT NULL,
    "widgets" character varying(255) NOT NULL
) WITH (oids = false);

COMMENT ON COLUMN "public"."page_widget_overrides"."widgets" IS 'csv list of widget ids';


DROP TABLE IF EXISTS "pages";
DROP SEQUENCE IF EXISTS pages_id_seq;
CREATE SEQUENCE pages_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."pages" (
    "id" integer DEFAULT nextval('pages_id_seq') NOT NULL,
    "state" smallint NOT NULL,
    "title" character varying(255) NOT NULL,
    "alias" character varying(255) NOT NULL,
    "content_type" integer NOT NULL,
    "content_view" integer NOT NULL,
    "updated" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "parent" integer DEFAULT '-1' NOT NULL,
    "template" integer DEFAULT '1' NOT NULL,
    "content_view_configuration" text NOT NULL,
    "page_options" text NOT NULL,
    "note" character varying(255) NOT NULL,
    CONSTRAINT "pages_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."pages"."page_options" IS 'seo and og settings';


DROP TABLE IF EXISTS "plugins";
DROP SEQUENCE IF EXISTS plugins_id_seq;
CREATE SEQUENCE plugins_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."plugins" (
    "id" integer DEFAULT nextval('plugins_id_seq') NOT NULL,
    "state" smallint DEFAULT '0' NOT NULL,
    "title" character varying(255) NOT NULL,
    "location" character varying(255) NOT NULL,
    "options" text NOT NULL,
    "description" text NOT NULL,
    CONSTRAINT "plugins_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."plugins"."options" IS 'options_json';


DROP TABLE IF EXISTS "redirects";
DROP SEQUENCE IF EXISTS redirects_id_seq;
CREATE SEQUENCE redirects_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."redirects" (
    "id" integer DEFAULT nextval('redirects_id_seq') NOT NULL,
    "state" smallint NOT NULL,
    "old_url" character varying(2048) NOT NULL,
    "new_url" character varying(2048),
    "referer" character varying,
    "note" character varying(255) NOT NULL,
    "hits" integer DEFAULT '0' NOT NULL,
    "created" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "created_by" integer DEFAULT '0' NOT NULL,
    "updated" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "updated_by" integer NOT NULL,
    "header" smallint DEFAULT '301' NOT NULL,
    CONSTRAINT "redirects_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TRIGGER "redirects_update_timestamp" BEFORE UPDATE ON "public"."redirects" FOR EACH ROW EXECUTE FUNCTION update_updated_column();

DROP TABLE IF EXISTS "tag_content_type";
CREATE TABLE "public"."tag_content_type" (
    "content_type_id" integer NOT NULL,
    "tag_id" integer NOT NULL
) WITH (oids = false);

COMMENT ON COLUMN "public"."tag_content_type"."content_type_id" IS '-1 for media';


DROP TABLE IF EXISTS "tagged";
CREATE TABLE "public"."tagged" (
    "tag_id" integer NOT NULL,
    "content_id" integer NOT NULL,
    "content_type_id" integer NOT NULL,
    CONSTRAINT "tag_id_content_id_content_type_id" UNIQUE ("tag_id", "content_id", "content_type_id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."tagged"."content_type_id" IS 'Important: -1 signifies MEDIA, -2 signifies USERS';


DROP TABLE IF EXISTS "tags";
DROP SEQUENCE IF EXISTS tags_id_seq;
CREATE SEQUENCE tags_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."tags" (
    "id" integer DEFAULT nextval('tags_id_seq') NOT NULL,
    "state" integer DEFAULT '1' NOT NULL,
    "public" integer DEFAULT '1' NOT NULL,
    "title" character varying(255) NOT NULL,
    "alias" character varying(255) NOT NULL,
    "image" integer NOT NULL,
    "filter" integer DEFAULT '1' NOT NULL,
    "description" text NOT NULL,
    "note" character varying(255) NOT NULL,
    "parent" integer NOT NULL,
    "category" integer NOT NULL,
    "custom_fields" text NOT NULL,
    CONSTRAINT "tags_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."tags"."filter" IS '0 admin only 1 exclusive 2 inclusive';


DROP TABLE IF EXISTS "templates";
DROP SEQUENCE IF EXISTS templates_id_seq;
CREATE SEQUENCE templates_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."templates" (
    "id" integer DEFAULT nextval('templates_id_seq') NOT NULL,
    "is_default" smallint NOT NULL,
    "title" character varying(255) NOT NULL,
    "folder" character varying(255) NOT NULL,
    "description" text NOT NULL,
    CONSTRAINT "templates_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "user_actions";
DROP SEQUENCE IF EXISTS user_actions_id_seq;
CREATE SEQUENCE user_actions_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."user_actions" (
    "id" integer DEFAULT nextval('user_actions_id_seq') NOT NULL,
    "userid" integer NOT NULL,
    "date" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "type" character varying(255) NOT NULL,
    "json" text NOT NULL,
    CONSTRAINT "user_actions_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "user_groups";
CREATE TABLE "public"."user_groups" (
    "user_id" integer NOT NULL,
    "group_id" integer NOT NULL,
    CONSTRAINT "user_groups_user_id_group_id" UNIQUE ("user_id", "group_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "users";
DROP SEQUENCE IF EXISTS users_id_seq;
CREATE SEQUENCE users_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users" (
    "id" integer DEFAULT nextval('users_id_seq') NOT NULL,
    "username" character varying(255) NOT NULL,
    "password" character varying(255) NOT NULL,
    "email" character varying(255) NOT NULL,
    "state" smallint NOT NULL,
    "created" timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
    "reset_key_expires" timestamp,
    "reset_key" character varying(250),
    CONSTRAINT "users_email" UNIQUE ("email"),
    CONSTRAINT "users_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "widget_types";
DROP SEQUENCE IF EXISTS widget_types_id_seq;
CREATE SEQUENCE widget_types_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."widget_types" (
    "id" integer DEFAULT nextval('widget_types_id_seq') NOT NULL,
    "title" character varying(255) NOT NULL,
    "location" character varying(255) NOT NULL,
    "description" text NOT NULL,
    CONSTRAINT "widget_types_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "widgets";
DROP SEQUENCE IF EXISTS widgets_id_seq;
CREATE SEQUENCE widgets_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."widgets" (
    "id" integer DEFAULT nextval('widgets_id_seq') NOT NULL,
    "state" smallint DEFAULT '1' NOT NULL,
    "type" integer NOT NULL,
    "ordering" integer NOT NULL,
    "title" character varying(255) NOT NULL,
    "position_control" smallint NOT NULL,
    "global_position" character varying(255) NOT NULL,
    "page_list" character varying(255) NOT NULL,
    "note" character varying(255) NOT NULL,
    "options" text NOT NULL,
    CONSTRAINT "widgets_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."widgets"."type" IS 'id from widget_types';

COMMENT ON COLUMN "public"."widgets"."page_list" IS 'page_list';