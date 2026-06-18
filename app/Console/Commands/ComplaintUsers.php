<?php

namespace App\Console\Commands;

use App\Models\ComplaintUser;
use Illuminate\Console\Command;
use DB;

class ComplaintUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:complaint-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = DB::connection('hr-portal')
            ->table('candidate_enquiries')
            ->get()
            ->toArray();

        foreach ($users as $user) {
            ComplaintUser::create($user);
        }
    }
}
