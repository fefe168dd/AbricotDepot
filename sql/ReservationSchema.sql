DROP TABLE IF EXISTS "reservations" CASCADE;
CREATE TABLE "public"."reservations" (
    "id" UUID PRIMARY KEY,
    "user_id" UUID NOT NULL,
    "outil_id" UUID NOT NULL,
    "quantity" INT NOT NULL,
    "start_date" TIMESTAMP NOT NULL,
    "end_date" TIMESTAMP NOT NULL,
    "status" VARCHAR(50) NOT NULL,
    CONSTRAINT fk_user FOREIGN KEY("user_id") REFERENCES public.users(id) ON DELETE CASCADE,
    CONSTRAINT fk_outil FOREIGN KEY("outil_id") REFERENCES public.outil(id) ON DELETE CASCADE
);