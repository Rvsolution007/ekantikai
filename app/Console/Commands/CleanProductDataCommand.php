<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\CustomerQuestionnaireState;
use Illuminate\Console\Command;

class CleanProductDataCommand extends Command
{
    protected $signature = 'data:clean-products {--admin-id= : Clean only for specific admin}';
    protected $description = 'Clean product data from customers, leads and questionnaire states while keeping customer info';

    // Product fields that should be cleared (NOT customer info)
    private $productFieldKeys = ['category', 'model', 'size', 'finish', 'quantity', 'material', 'product_type', 'color', 'packaging'];

    public function handle()
    {
        $adminId = $this->option('admin-id');

        $this->info('Starting product data cleanup...');

        // 1. Clean customer global_fields and global_asked
        $this->cleanCustomers($adminId);

        // 2. Clean customer_questionnaire_states
        $this->cleanQuestionnaireStates($adminId);

        // 3. Clean leads collected_data and product_confirmations
        $this->cleanLeads($adminId);

        $this->info('Product data cleanup completed!');

        return 0;
    }

    private function cleanCustomers($adminId)
    {
        $query = Customer::query();
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $count = 0;
        $query->chunkById(100, function ($customers) use (&$count) {
            foreach ($customers as $customer) {
                $changed = false;

                // Filter global_fields - remove product fields
                if ($customer->global_fields) {
                    $filtered = array_filter($customer->global_fields, function ($key) {
                        return !in_array(strtolower($key), $this->productFieldKeys);
                    }, ARRAY_FILTER_USE_KEY);

                    if (count($filtered) !== count($customer->global_fields)) {
                        $customer->global_fields = $filtered ?: null;
                        $changed = true;
                    }
                }

                // Filter global_asked - remove product field tracking
                if ($customer->global_asked) {
                    $filtered = array_filter($customer->global_asked, function ($key) {
                        return !in_array(strtolower($key), $this->productFieldKeys);
                    }, ARRAY_FILTER_USE_KEY);

                    if (count($filtered) !== count($customer->global_asked)) {
                        $customer->global_asked = $filtered ?: null;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $customer->save();
                    $count++;
                }
            }
        });

        $this->info("Cleaned {$count} customers");
    }

    private function cleanQuestionnaireStates($adminId)
    {
        $query = CustomerQuestionnaireState::query();
        if ($adminId) {
            $query->whereHas('customer', function ($q) use ($adminId) {
                $q->where('admin_id', $adminId);
            });
        }

        $count = 0;
        $query->chunkById(100, function ($states) use (&$count) {
            foreach ($states as $state) {
                $changed = false;

                // Filter completed_fields
                if ($state->completed_fields) {
                    $filtered = array_filter($state->completed_fields, function ($key) {
                        return !in_array(strtolower($key), $this->productFieldKeys);
                    }, ARRAY_FILTER_USE_KEY);

                    if (count($filtered) !== count($state->completed_fields)) {
                        $state->completed_fields = $filtered ?: null;
                        $changed = true;
                    }
                }

                // Clear workflow_data and pending_items for fresh start
                if ($state->workflow_data || $state->pending_items) {
                    $state->workflow_data = null;
                    $state->pending_items = null;
                    $changed = true;
                }

                if ($changed) {
                    $state->save();
                    $count++;
                }
            }
        });

        $this->info("Cleaned {$count} questionnaire states");
    }

    private function cleanLeads($adminId)
    {
        $query = Lead::query();
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $count = 0;
        $query->chunkById(100, function ($leads) use (&$count) {
            foreach ($leads as $lead) {
                $changed = false;

                // Filter collected_data.global_questions
                if ($lead->collected_data) {
                    $data = $lead->collected_data;

                    if (isset($data['global_questions'])) {
                        $filtered = array_filter($data['global_questions'], function ($key) {
                            return !in_array(strtolower($key), $this->productFieldKeys);
                        }, ARRAY_FILTER_USE_KEY);

                        if (count($filtered) !== count($data['global_questions'])) {
                            $data['global_questions'] = $filtered ?: null;
                            $changed = true;
                        }
                    }

                    // Clear workflow_questions (product data)
                    if (isset($data['workflow_questions'])) {
                        unset($data['workflow_questions']);
                        $changed = true;
                    }

                    if ($changed) {
                        $lead->collected_data = $data;
                    }
                }

                // Clear product_confirmations
                if ($lead->product_confirmations) {
                    $lead->product_confirmations = null;
                    $changed = true;
                }

                if ($changed) {
                    $lead->save();
                    $count++;
                }
            }
        });

        $this->info("Cleaned {$count} leads");
    }
}
