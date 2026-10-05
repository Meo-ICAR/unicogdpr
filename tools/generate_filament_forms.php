<?php

// Simple generator to populate Filament Schema form files from model $fillable/$casts
// Usage: php tools/generate_filament_forms.php

$base = __DIR__.'/../app/Filament/Resources';
$modelsPath = __DIR__.'/../app/Models';

function snakeToLabel($s)
{
    return ucwords(str_replace('_', ' ', $s));
}

$dirs = glob($base.'/*', GLOB_ONLYDIR);
$modified = [];

foreach ($dirs as $dir) {
    $resourceName = basename($dir);
    $resourceFileCandidates = glob($dir.'/*Resource.php');
    if (! count($resourceFileCandidates)) {
        continue;
    }
    $resourceFile = $resourceFileCandidates[0];
    $content = file_get_contents($resourceFile);
    if (! preg_match('/protected\s+static\s+\?string\s+\$model\s*=\s*([A-Za-z0-9_\\]+)::class;/', $content, $m)) {
        // try different pattern
        if (! preg_match('/protected\s+static\s+\$model\s*=\s*([A-Za-z0-9_\\]+)::class;/', $content, $m)) {
            continue;
        }
    }
    $modelClassFull = $m[1];
    $modelClass = basename(str_replace('\\', '/', $modelClassFull));
    $modelFile = $modelsPath.'/'.$modelClass.'.php';
    if (! file_exists($modelFile)) {
        continue;
    }
    $modelCode = file_get_contents($modelFile);

    // parse $fillable
    $fillable = [];
    if (preg_match('/protected\s+\$fillable\s*=\s*\[([^\]]*)\]/s', $modelCode, $fm)) {
        $inside = $fm[1];
        if (preg_match_all("/'([A-Za-z0-9_]+)'|\"([A-Za-z0-9_]+)\"/", $inside, $names)) {
            foreach ($names[1] as $k => $v) {
                $field = $v ?: $names[2][$k];
                if ($field) {
                    $fillable[] = $field;
                }
            }
        }
    }

    // parse $casts
    $casts = [];
    if (preg_match('/protected\s+\$casts\s*=\s*\[([^\]]*)\]/s', $modelCode, $cm)) {
        $inside = $cm[1];
        if (preg_match_all("/'([A-Za-z0-9_]+)'\s*=>\s*'([A-Za-z0-9_\\]+)'|\"([A-Za-z0-9_]+)\"\s*=>\s*\"([A-Za-z0-9_\\]+)\"/", $inside, $cs)) {
            for ($i = 0; $i < count($cs[0]); $i++) {
                $k = $cs[1][$i] ?: $cs[3][$i];
                $v = $cs[2][$i] ?: $cs[4][$i];
                if ($k) {
                    $casts[$k] = $v;
                }
            }
        }
    }

    // parse relations (belongsTo) by searching 'function X()' with 'belongsTo('
    $relations = [];
    if (preg_match_all('/public function ([a-zA-Z0-9_]+)\s*\(.*\)\s*:\s*[^{]+{([\s\S]*?)}/', $modelCode, $fms, PREG_SET_ORDER)) {
        foreach ($fms as $fn) {
            $body = $fn[2];
            if (strpos($body, 'belongsTo(') !== false) {
                // try get related class
                if (preg_match("/belongsTo\(\s*([A-Za-z0-9_\\']+)\s*::class|belongsTo\(\s*'([A-Za-z0-9_\\']+)'/", $body, $rel)) {
                    $relClass = $rel[1] ?: $rel[2];
                    $relClass = trim($relClass, "'\\\n\r\t ");
                    $relName = $fn[1];
                    $relations[$relName] = $relClass;
                } else {
                    $relations[$fn[1]] = null;
                }
            }
        }
    }

    // find schema file
    $schemaFiles = glob($dir.'/Schemas/*Form.php');
    foreach ($schemaFiles as $schemaFile) {
        $schemaBasename = basename($schemaFile);
        // generate components
        $components = [];
        foreach ($fillable as $field) {
            // if field is relation foreign key, skip (belongsTo select will use relation)
            $isForeign = false;
            foreach ($relations as $relName => $relClass) {
                $fk = $relName.'_id';
                if ($field === $fk || $field === $relName.'able_id') {
                    $isForeign = true;
                    break;
                }
            }
            if ($isForeign) {
                continue;
            }

            $label = snakeToLabel($field);
            $type = $casts[$field] ?? null;
            if (in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            if ($type === 'boolean') {
                $components[] = "Toggle::make('$field')->label('$label'),";
            } elseif (in_array($type, ['date', 'datetime', 'datetime_interface', 'immutable_datetime'])) {
                $components[] = "DatePicker::make('$field')->label('$label'),";
            } elseif ($type === 'array' || $type === 'json') {
                $components[] = "Textarea::make('$field')->label('$label')->rows(3),";
            } elseif (in_array($type, ['integer', 'float', 'double', 'numeric'])) {
                $components[] = "TextInput::make('$field')->label('$label')->numeric(),";
            } else {
                // default: if name contains 'description' or 'text' or 'body' or 'note' use Textarea
                if (preg_match('/description|text|body|note|content|message/i', $field)) {
                    $components[] = "Textarea::make('$field')->label('$label')->rows(3),";
                } else {
                    $components[] = "TextInput::make('$field')->label('$label')->maxLength(255),";
                }
            }
        }

        // add belongsTo selects for relations
        foreach ($relations as $relName => $relClass) {
            $label = ucwords($relName);
            $components[] = "BelongsToSelect::make('{$relName}_id')->relationship('{$relName}', 'id')->label('$label')->nullable(),";
        }

        $componentsText = implode("\n                ", $components);

        $newContent = "<?php\n\nnamespace ".str_replace('/', '\\', trim(str_replace(__DIR__.'/../app/Filament/Resources', '', $dir), '/'))."\Schemas;\n\nuse Filament\\Schemas\\Schema;\nuse Filament\\Forms\\Components\\TextInput;\nuse Filament\\Forms\\Components\\Textarea;\nuse Filament\\Forms\\Components\\Toggle;\nuse Filament\\Forms\\Components\\DatePicker;\nuse Filament\\Forms\\Components\\BelongsToSelect;\n\nclass ".pathinfo($schemaFile, PATHINFO_FILENAME)."\n{\n    public static function configure(Schema $schema): Schema\n    {\n        return $schema\n            ->components([\n                $componentsText\n            ]);\n    }\n}\n";

        // write file
        file_put_contents($schemaFile, $newContent);
        $modified[] = $schemaFile;
    }
}

echo 'Updated '.count($modified)." schema files:\n";
foreach ($modified as $f) {
    echo " - $f\n";
}
