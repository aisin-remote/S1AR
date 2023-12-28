<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowMysqlVersion extends Command
{
    protected $signature = 'mysql:version';

    protected $description = 'Show MySQL version';

    public function handle()
    {
        $version = DB::connection('mysql2')->select('select version() as version')[0]->version;
        $this->info("MySQL version: $version");
    }
}
