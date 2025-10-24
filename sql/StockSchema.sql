DROP TABLE IF EXISTS "stock";
CREATE TABLE "public"."stock"(
    "id" uuid NOT NULL,
    "outil_id" uuid NOT NULL,
    "quantity" INT NOT NULL,
    "quantity_reserved" INT NOT NULL,
    "available" INT NOT NULL,
    CONSTRAINT "id" PRIMARY KEY ("id")
);
