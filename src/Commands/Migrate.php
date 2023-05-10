<?php

namespace CloudMediaSolutions\LaravelScoutOpenSearch\Commands;

use CloudMediaSolutions\LaravelScoutOpenSearch\Engines\OpenSearchEngine;
use Illuminate\Console\Command;
use OpenSearch\Client;
use stdClass;

class Migrate extends Command
{
    private const INDEX_PREFIX = "scout";

    protected $signature = "opensearch:migrate
                            {model : Class name of model}";

    protected $description = "Zero downtime migrations for Opensearch";

    public function handle(
        Client $opensearch, 
        OpenSearchEngine $scout
    ) {
        $model = $this->argument("model");

        $alias     = (new $model)->searchableAs();
        $indexName = sprintf("%s_%s_%s", self::INDEX_PREFIX, $alias, time());

        $scout->createIndex(
            $indexName,
            ["aliases" => [$alias => new stdClass]]
        );

        $indexes = $opensearch->cat()->aliases(["name" => $alias, "format" => "json"]);

        $indexes = array_filter($indexes, function (array $index) use ($indexName) {
            return $index["index"] !== $indexName;
        });

        $latest = array_pop($indexes);

        if (empty($latest)) {
            return;
        }

        $latest = $latest['index'];

        $opensearch->reindex([
            'body' => [
                'source' => [
                    'index' => $latest
                ],
                'dest' => [
                    'index' => $indexName
                ]
            ]
        ]);

        $opensearch->indices()->deleteAlias(['index' => $latest, 'name' => $alias]);

        $this->info("Index {$latest} can be deleted");

        /*
            Тут ищем более поздний индекс по time()
            делаем его reindex в новый индекс,
            удаляем старый индекс
        */
    }
}