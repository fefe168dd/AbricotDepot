DROP TABLE IF EXISTS "panier";
CREATE TABLE "public"."panier" (
    "id" UUID NOT NULL,
    "user_id" UUID NOT NULL,
    "outil_id" UUID NOT NULL,
    "quantity" INT NOT NULL,
    "datedebut" TIMESTAMP NOT NULL,
    "datefin" TIMESTAMP NOT NULL,
    CONSTRAINT "panier_item_pk" PRIMARY KEY ("id")
);