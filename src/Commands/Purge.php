<?php

namespace Jurager\Tracker\Commands;

use Jurager\Tracker\Traits\Expirable;
use Jurager\Tracker\Models\PersonalAccessToken;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;


class Purge extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'tracker:purge';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete the expired records.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		if(config('tracker.expires')) {
			
			$this->line('');
			$this->comment('Deleting expired records...');
			$this->line('');
	
			$total = PersonalAccessToken::where('last_used_at', '>=', Carbon::now()->addDays(config('tracker.expires')))->delete();
	
			if ($total > 0) {
				$this->info($total . ' ' . Str::plural('record', $total) . ' deleted.');
			} else {
				$this->comment('Nothing to delete.');
			}
	
	
			$this->line('');
			$this->info('Purge completed!');
			$this->line('');

			return true;
		}
		
		$this->info('The cleenup feature is disabled');
	}
}
