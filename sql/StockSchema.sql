DROP TABLE IF EXISTS "stock";
CREATE TABLE "public"."stock"(
    "id" uuid NOT NULL,
    "outil_id" uuid NOT NULL,
    "quantity" INT NOT NULL,
    "quantity_reserved" INT NOT NULL,
    "available" INT NOT NULL,
    CONSTRAINT "id" PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "stock_reservations";
CREATE TABLE "public"."stock_reservations"(
    "id" uuid NOT NULL,
    "stock_id" uuid NOT NULL,
    "order_id" uuid NOT NULL,
    "quantity" INT NOT NULL,
    "datedebut" TIMESTAMP NOT NULL,
    "datefin" TIMESTAMP NOT NULL,
    CONSTRAINT "id" PRIMARY KEY ("id"),
    CONSTRAINT "fk_stock" FOREIGN KEY ("stock_id") REFERENCES "public"."stock"("id")
);