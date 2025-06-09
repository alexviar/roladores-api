<?php

namespace App\Enums;

enum RentalPeriodStatuses: string
{
  case Unpaid = 'unpaid';
  case Paid = 'paid';
  case Overdue = 'overdue';
}
