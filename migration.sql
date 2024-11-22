-- -------------------------------------------------------------
-- Database: testorderprocessor
-- Generation Time: 2024-11-23 01:54:05.9900
-- -------------------------------------------------------------


-- This script only contains the table creation statements and does not fully represent the table in the database. Do not use it as a backup.

-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS event_prices_id_seq;

-- Table Definition
CREATE TABLE "public"."event_prices" (
    "id" int4 NOT NULL DEFAULT nextval('event_prices_id_seq'::regclass),
    "event_id" int4 NOT NULL,
    "ticket_type_id" int4 NOT NULL,
    "price" int4 NOT NULL,
    "valid_to" timestamp,
    "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

-- This script only contains the table creation statements and does not fully represent the table in the database. Do not use it as a backup.

-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS events_id_seq;

-- Table Definition
CREATE TABLE "public"."events" (
    "id" int4 NOT NULL DEFAULT nextval('events_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "description" text NOT NULL,
    "date" timestamp NOT NULL,
    "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

-- This script only contains the table creation statements and does not fully represent the table in the database. Do not use it as a backup.

-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS orders_id_seq;

-- Table Definition
CREATE TABLE "public"."orders" (
    "id" int4 NOT NULL DEFAULT nextval('orders_id_seq'::regclass),
    "event_id" int4 NOT NULL,
    "user_id" int4 NOT NULL,
    "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

-- This script only contains the table creation statements and does not fully represent the table in the database. Do not use it as a backup.

-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS ticket_types_id_seq;

-- Table Definition
CREATE TABLE "public"."ticket_types" (
    "id" int4 NOT NULL DEFAULT nextval('ticket_types_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

-- This script only contains the table creation statements and does not fully represent the table in the database. Do not use it as a backup.

-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS tickets_id_seq;

-- Table Definition
CREATE TABLE "public"."tickets" (
    "id" int4 NOT NULL DEFAULT nextval('tickets_id_seq'::regclass),
    "order_id" int4 NOT NULL,
    "event_price_id" int4 NOT NULL,
    "barcode" varchar(120) NOT NULL,
    "used" bool NOT NULL DEFAULT false,
    "created" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

ALTER TABLE "public"."event_prices" ADD FOREIGN KEY ("event_id") REFERENCES "public"."events"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."event_prices" ADD FOREIGN KEY ("ticket_type_id") REFERENCES "public"."ticket_types"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."orders" ADD FOREIGN KEY ("event_id") REFERENCES "public"."events"("id");
ALTER TABLE "public"."tickets" ADD FOREIGN KEY ("order_id") REFERENCES "public"."orders"("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."tickets" ADD FOREIGN KEY ("event_price_id") REFERENCES "public"."event_prices"("id") ON DELETE CASCADE ON UPDATE CASCADE;
