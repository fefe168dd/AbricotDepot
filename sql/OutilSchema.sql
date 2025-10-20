DROP TABLE IF EXISTS "outil";
CREATE TABLE "public"."outil"(
    "id" uuid NOT NULL,
    "name" VARCHAR(100) NOT NULL ,
    "description" TEXT,
    "prix" DECIMAL(10,2) NOT NULL,
    "image_url" VARCHAR(255),
    CONSTRAINT "id" PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "categorie";
CREATE TABLE "public"."categorie"(
    "id" uuid NOT NULL,
    "name" VARCHAR(100) NOT NULL ,
    CONSTRAINT "id" PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "outil_categorie";
CREATE TABLE "public"."outil_categorie"(
    "outil_id" uuid NOT NULL,
    "categorie_id" uuid NOT NULL,
    CONSTRAINT "outil_categorie_pk" PRIMARY KEY ("outil_id", "categorie_id"),
    CONSTRAINT "outil_fk" FOREIGN KEY ("outil_id") REFERENCES "public"."outil"("id") ON DELETE CASCADE,
    CONSTRAINT "categorie_fk" FOREIGN KEY ("categorie_id") REFERENCES "public"."categorie"("id") ON DELETE CASCADE
);

