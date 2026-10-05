<?php

// Improved generator: safer parsing for model class and $fillable/$casts
// Usage: php tools/generate_filament_forms_fixed.php

$base = __DIR__.'/../app/Filament/Resources';
$modelsPath = __DIR__.'/../app/Models';

function snakeToLabel($s)
{
    return ucwords(str_replace('_', ' ', $s));
}

function extractModelClassFromResource($content)
{
    $lines = preg_split('/\r?\n/', $content);
    foreach ($lines as $line) {
        if (strpos($line, 'protected') !== false && strpos($line, '::class') !== false) {
            // find the = and ::class
            $eqPos = strpos($line, '=');
            $classPos = strpos($line, '::class');
            if ($eqPos !== false && $classPos !== false && $classPos > $eqPos) {
                $substr = substr($line, $eqPos + 1, $classPos - $eqPos - 1);
                // remove non-class chars
                $substr = trim($substr, " \t\n\r\0\x0B;\\");
                // remove leading/backslash and quotes
                $substr = trim($substr, "\\ \t\n\r\0\x0B'");
                $substr = trim($substr, '"');
                // if contains ::class already handled
                // return class name (last segment)
                $parts = preg_split('~[\\\\/]~', $substr);

                return end($parts);
            }
        }
    }

    return null;
}

function extractArrayContent($code, $varName)
{
    $pos = strpos($code, '$'.$varName);
    if ($pos === false) {
        return null;
    }
    $bracketPos = strpos($code, '[', $pos);
    if ($bracketPos === false) {
        return null;
    }
    $level = 0;
    $len = strlen($code);
    for ($i = $bracketPos; $i < $len; $i++) {
        $c = $code[$i];
        if ($c === '[') {
            $level++;
        } elseif ($c === ']') {
            $level--;
        }
        if ($level === 0) {
            // return content inside brackets
            return substr($code, $bracketPos + 1, $i - $bracketPos - 1);
        }
    }

    return null;
}

$dirs = glob($base.'/*', GLOB_ONLYDIR);
$modified = [];

foreach ($dirs as $dir) {
    $resourceFileCandidates = glob($dir.'/*Resource.php');
    if (! count($resourceFileCandidates)) {
        continue;
    }
    $resourceFile = $resourceFileCandidates[0];
    $content = file_get_contents($resourceFile);
    $modelClass = extractModelClassFromResource($content);
    if (! $modelClass) {
        continue;
    }
    $modelFile = $modelsPath.'/'.$modelClass.'.php';
    if (! file_exists($modelFile)) {
        continue;
    }
    $modelCode = file_get_contents($modelFile);

    // extract fillable
    $fillableContent = extractArrayContent($modelCode, 'fillable');
    $fillable = [];
    if ($fillableContent !== null) {
        if (preg_match_all('/["\']([A-Za-z0-9_]+)["\']/', $fillableContent, $m)) {
            $fillable = $m[1];
        }
    }

    // extract casts
    $castsContent = extractArrayContent($modelCode, 'casts');
    $casts = [];
    if ($castsContent !== null) {
        if (preg_match_all('/["\']([A-Za-z0-9_]+)["\']\s*=>\s*["\']([A-Za-z0-9_\\]+)["\']/', $castsContent, $m)) {
            for ($i = 0; $i < count($m[1]); $i++) {
                $casts[$m[1][$i]] = $m[2][$i];
            }
        }
    }

    // find relations by searching for "belongsTo(" occurrences and function name
    $relations = [];
    if (preg_match_all('/public function\s+([a-zA-Z0-9_]+)\s*\(.*\)\s*\{([\s\S]*?)\}/', $modelCode, $fms, PREG_SET_ORDER)) {
        foreach ($fms as $fn) {
            $body = $fn[2];
            if (strpos($body, 'belongsTo(') !== false) {
                // try to extract relation name
                $relations[$fn[1]] = true;
            }
        }
    }

    // find schema files
    $schemaFiles = glob($dir.'/Schemas/*Form.php');
    foreach ($schemaFiles as $schemaFile) {
        $components = [];
        foreach ($fillable as $field) {
            if (in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            // skip foreign key fields that match relation + _id
            $isForeign = false;
            foreach ($relations as $relName => $_) {
                if ($field === $relName.'_id' || strpos($field, $relName.'_') === 0) {
                    $isForeign = true;
                    break;
                }
            }
            if ($isForeign) {
                continue;
            }
            $label = snakeToLabel($field);
            $type = $casts[$field] ?? null;
            if ($type === 'boolean') {
                $components[] = "Toggle::make('$field')->label('$label'),";
            } elseif (in_array($type, ['date', 'datetime'])) {
                $components[] = "DatePicker::make('$field')->label('$label'),";
            } elseif ($type === 'array' || $type === 'json') {
                $components[] = "Textarea::make('$field')->label('$label')->rows(3),";
            } elseif (in_array($type, ['integer', 'float', 'double'])) {
                $components[] = "TextInput::make('$field')->label('$label')->numeric(),";
            } else {
                if (preg_match('/description|text|body|note|content|message/i', $field)) {
                    $components[] = "Textarea::make('$field')->label('$label')->rows(3),";
                } else {
                    $components[] = "TextInput::make('$field')->label('$label')->maxLength(255),";
                }
            }
        }
        // add BelongsToSelect for relations
        foreach ($relations as $relName => $_) {
            $label = ucwords($relName);
            $components[] = "BelongsToSelect::make('{$relName}_id')->relationship('{$relName}', 'id')->label('$label')->nullable(),";
        }

        $componentsText = implode("\n                ", $components);

        // build namespace from dir
        $nsPart = str_replace(__DIR__.'/../app/Filament/Resources', '', $dir);
        $nsPart = trim($nsPart, '/\\');
        $nsPart = str_replace('/', '\\', $nsPart);
        if ($nsPart) {
            $namespace = "App\\Filament\\Resources\\$nsPart\\Schemas";
        } else {
            $namespace = 'App\\Filament\\Resources\\Schemas';
        }

        $className = pathinfo($schemaFile, PATHINFO_FILENAME);
        $newContent = "<?php\n\nnamespace $namespace;\n\nuse Filament\\Schemas\\Schema;\nuse Filament\\Forms\\Components\\TextInput;\nuse Filament\\Forms\\Components\\Textarea;\nuse Filament\\Forms\\Components\\Toggle;\nuse Filament\\Forms\\Components\\DatePicker;\nuse Filament\\Forms\\Components\\BelongsToSelect;\n\nclass $className\n{\n    public static function configure(Schema $schema): Schema\n    {\n        return $schema\n            ->components([\n                $componentsText\n            ]);\n    }\n}\n";

        file_put_contents($schemaFile, $newContent);
        $modified[] = $schemaFile;
    }
}

echo 'Updated '.count($modified)." schema files:\n";
foreach ($modified as $f) {
    echo " - $f\n";
}
