<?php

namespace CloudMediaSolutions\LaravelScoutOpenSearch\Commands;

use CloudMediaSolutions\LaravelScoutOpenSearch\Engines\OpenSearchEngine;
use Illuminate\Console\Command;
use OpenSearch\Client;
use stdClass;

class Migrate extends Command
{
    private const INDEX_PREFIX = "scout";

    protected $signature = "opensearch:migrate";

    protected $description = "Zero downtime migrations for Opensearch";

    public function __construct(
        private Client $opensearch,
        private OpenSearchEngine $scoutEngine
    ) {

    }

    public function handle()
    {
        $alias     = (new $model)->searchableAs();
        $indexName = sprintf("%s_%s", self::INDEX_PREFIX, time());

        $this->scoutEngine->createIndex(
            $indexName,
            ["aliases" => [$alias => new stdClass]]
        );

        $indexes = $this->opensearch->cat()->aliases(["name" => $alias, "format" => "json"]);

        dd($indexes);

        /*
            Тут ищем более поздний индекс по time()
            делаем его reindex в новый индекс,
            удаляем старый индекс
        */
    }
}