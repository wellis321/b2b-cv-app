-- Migration to update existing CV header colors to hex format
DO $$
DECLARE
    color_map JSONB := '{
        "slate-700": "#334155",
        "gray-700": "#374151",
        "zinc-700": "#3f3f46",
        "neutral-700": "#404040",
        "stone-700": "#44403c",
        "red-700": "#b91c1c",
        "orange-700": "#c2410c",
        "amber-700": "#b45309",
        "yellow-700": "#a16207",
        "lime-700": "#4d7c0f",
        "green-700": "#15803d",
        "emerald-700": "#047857",
        "teal-700": "#0f766e",
        "cyan-700": "#0e7490",
        "sky-700": "#0369a1",
        "blue-700": "#1d4ed8",
        "indigo-700": "#4338ca",
        "violet-700": "#6d28d9",
        "purple-700": "#7e22ce",
        "fuchsia-700": "#a21caf",
        "pink-700": "#be185d",
        "rose-700": "#be123c"
    }';
BEGIN
    -- Update from_color values
    UPDATE profiles
    SET cv_header_from_color = color_map->cv_header_from_color
    WHERE cv_header_from_color IS NOT NULL
      AND cv_header_from_color != ''
      AND cv_header_from_color NOT LIKE '#%'
      AND color_map ? cv_header_from_color;

    -- Update to_color values
    UPDATE profiles
    SET cv_header_to_color = color_map->cv_header_to_color
    WHERE cv_header_to_color IS NOT NULL
      AND cv_header_to_color != ''
      AND cv_header_to_color NOT LIKE '#%'
      AND color_map ? cv_header_to_color;

    -- Set default values for any remaining non-hex values
    UPDATE profiles
    SET cv_header_from_color = '#4338ca'  -- Default indigo-700
    WHERE cv_header_from_color IS NULL
       OR cv_header_from_color = ''
       OR cv_header_from_color NOT LIKE '#%';

    UPDATE profiles
    SET cv_header_to_color = '#7e22ce'  -- Default purple-700
    WHERE cv_header_to_color IS NULL
       OR cv_header_to_color = ''
       OR cv_header_to_color NOT LIKE '#%';
END $$;