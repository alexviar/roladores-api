<?php

use Illuminate\Console\Scheduling\Schedule;

Schedule::command('activitylog:clean')->daily();
