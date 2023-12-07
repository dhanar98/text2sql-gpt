-- FUNCTION: public.generate_create_table_statement(text)

-- DROP FUNCTION IF EXISTS public.generate_create_table_statement(text);

CREATE OR REPLACE FUNCTION public.generate_create_table_statement(
	p_table_name text)
    RETURNS text
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
AS $BODY$
DECLARE
    create_statement text;
BEGIN
    SELECT 
        'CREATE TABLE IF NOT EXISTS public.' || p_table_name || ' ( ' || 
        string_agg(column_definition, ', ' || E'\n') ||
        E'\n);'
    INTO create_statement
    FROM (
        SELECT 
            column_name || ' ' || data_type || 
            CASE 
                WHEN character_maximum_length IS NOT NULL THEN '(' || character_maximum_length || ')'
                ELSE ''
            END AS column_definition
        FROM information_schema.columns
        WHERE table_name = p_table_name
        ORDER BY ordinal_position
    ) AS columns;

    RETURN create_statement;
END;
$BODY$;

ALTER FUNCTION public.generate_create_table_statement(text)
    OWNER TO postgres;
