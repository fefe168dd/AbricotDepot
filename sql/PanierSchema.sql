DROP TABLE IF EXISTS "panier";
CREATE TABLE "public"."panier"(
    "id" uuid NOT NULL,
    "user_id" uuid NOT NULL,
    CONSTRAINT "id" PRIMARY KEY ("id"),
    CONSTRAINT "user_fk" FOREIGN KEY ("user_id") REFERENCES "public"."users"("id") ON DELETE CASCADE
);

DROP TABLE IF EXISTS "panier_item";
CREATE TABLE "public"."panier_item"(
    "id" uuid NOT NULL,
    "panier_id" uuid NOT NULL,
    "outil_id" uuid NOT NULL,
    "quantity" INT NOT NULL,
    CONSTRAINT "id" PRIMARY KEY ("id"),
    CONSTRAINT "panier_fk" FOREIGN KEY ("panier_id") REFERENCES "public"."panier"("id") ON DELETE CASCADE,
    CONSTRAINT "outil_fk" FOREIGN KEY ("outil_id") REFERENCES "public"."outil"("id") ON DELETE CASCADE
);