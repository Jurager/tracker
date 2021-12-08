<?php

namespace Jurager\Tracker\Commands;

use Jurager\Tracker\Traits\Expirable;
use Jurager\Tracker\Models\PersonalAccessToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;


class PurgeCommand extends Command
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
		$this->line('');
		$this->comment('Deleting expired records...');
		$this->line('');

		if (in_array(Expirable::class, class_uses_recursive(PersonalAccessToken::class))) {

			$total = call_user_func(PersonalAccessToken::class . '::onlyExpired')->forceDelete();

			if ($total > 0) {
				$this->info($total . ' ' . Str::plural('record', $total) . ' deleted.');
			} else {
				$this->comment('Nothing to delete.');
			}

		} else {
			$this->error('This model is not expirable! (Expirable trait not found)');
		}

		$this->line('');
		$this->info('Purge completed!');
		$this->line('');
	}
}