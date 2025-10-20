DROP TABLE IF EXISTS "users";
CREATE TABLE "public"."users"(
    "id" uuid NOT NULL,
    "username" VARCHAR(50) NOT NULL ,
    "password_hash" VARCHAR(255) NOT NULL,
    "email" VARCHAR(100) NOT NULL ,
    CONSTRAINT "users_email" UNIQUE ("email"),
    CONSTRAINT "users_username" UNIQUE ("username"),
    CONSTRAINT "id" PRIMARY KEY ("id")
);