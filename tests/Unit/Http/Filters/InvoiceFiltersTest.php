<?php

declare(strict_types=1);

namespace Banelsems\LaraSgmefQr\Tests\Unit\Http\Filters;

use Banelsems\LaraSgmefQr\Enums\InvoiceStatusEnum;
use Banelsems\LaraSgmefQr\Http\Filters\InvoiceFilters;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class InvoiceFiltersTest extends TestCase
{
    private InvoiceFilters $filters;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filters = new InvoiceFilters();
    }

    public function test_empty_request_returns_defaults(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest([]));

        $this->assertSame('created_at', $result['sort']);
        $this->assertSame('desc', $result['direction']);
        $this->assertArrayNotHasKey('status', $result);
        $this->assertArrayNotHasKey('type', $result);
    }

    public function test_valid_status_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['status' => InvoiceStatusEnum::PENDING->value]));

        $this->assertSame(InvoiceStatusEnum::PENDING->value, $result['status']);
    }

    public function test_invalid_status_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['status' => 'NOT_A_STATUS']));

        $this->assertArrayNotHasKey('status', $result);
    }

    public function test_valid_type_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['type' => 'FA']));

        $this->assertSame('FA', $result['type']);
    }

    public function test_invalid_type_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['type' => 'XX']));

        $this->assertArrayNotHasKey('type', $result);
    }

    public function test_ifu_is_kept_when_non_empty(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['ifu' => '1234567890123']));

        $this->assertSame('1234567890123', $result['ifu']);
    }

    public function test_empty_ifu_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['ifu' => '   ']));

        $this->assertArrayNotHasKey('ifu', $result);
    }

    public function test_date_range_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest([
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]));

        $this->assertSame('2026-01-01', $result['date_from']);
        $this->assertSame('2026-01-31', $result['date_to']);
    }

    public function test_allowed_sort_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['sort' => 'total_amount']));

        $this->assertSame('total_amount', $result['sort']);
    }

    public function test_disallowed_sort_falls_back_to_default(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['sort' => 'raw_request']));

        $this->assertSame('created_at', $result['sort']);
    }

    public function test_direction_asc_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['direction' => 'asc']));

        $this->assertSame('asc', $result['direction']);
    }

    public function test_invalid_direction_falls_back_to_desc(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['direction' => 'sideways']));

        $this->assertSame('desc', $result['direction']);
    }

    public function test_non_string_ifu_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['ifu' => ['array']]));

        $this->assertArrayNotHasKey('ifu', $result);
    }

    // --- search ---

    public function test_search_is_kept_when_non_empty(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['search' => 'uid-123']));

        $this->assertSame('uid-123', $result['search']);
    }

    public function test_search_is_trimmed(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['search' => '  uid-123  ']));

        $this->assertSame('uid-123', $result['search']);
    }

    public function test_empty_search_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['search' => '   ']));

        $this->assertArrayNotHasKey('search', $result);
    }

    public function test_search_over_max_length_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['search' => str_repeat('a', 256)]));

        $this->assertArrayNotHasKey('search', $result);
    }

    public function test_search_at_max_length_is_kept(): void
    {
        $value = str_repeat('a', 255);
        $result = $this->filters->fromRequest($this->makeRequest(['search' => $value]));

        $this->assertSame($value, $result['search']);
    }

    // --- amount_min / amount_max ---

    public function test_amount_min_integer_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['amount_min' => '1000']));

        $this->assertSame(1000, $result['amount_min']);
    }

    public function test_amount_max_integer_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['amount_max' => '5000']));

        $this->assertSame(5000, $result['amount_max']);
    }

    public function test_amount_min_zero_is_kept(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['amount_min' => '0']));

        $this->assertSame(0, $result['amount_min']);
    }

    public function test_amount_min_negative_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['amount_min' => '-100']));

        $this->assertArrayNotHasKey('amount_min', $result);
    }

    public function test_amount_min_non_numeric_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['amount_min' => 'abc']));

        $this->assertArrayNotHasKey('amount_min', $result);
    }

    public function test_amount_min_null_is_dropped(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest([]));

        $this->assertArrayNotHasKey('amount_min', $result);
    }

    public function test_amount_min_greater_than_max_drops_both(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest([
            'amount_min' => '5000',
            'amount_max' => '1000',
        ]));

        $this->assertArrayNotHasKey('amount_min', $result);
        $this->assertArrayNotHasKey('amount_max', $result);
    }

    public function test_amount_min_equal_to_max_keeps_both(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest([
            'amount_min' => '1000',
            'amount_max' => '1000',
        ]));

        $this->assertSame(1000, $result['amount_min']);
        $this->assertSame(1000, $result['amount_max']);
    }

    public function test_amount_decimal_is_rounded_to_int(): void
    {
        $result = $this->filters->fromRequest($this->makeRequest(['amount_min' => '1000.50']));

        // XOF = 0 décimales, arrondi au plus proche
        $this->assertSame(1001, $result['amount_min']);
    }

    private function makeRequest(array $params): Request
    {
        return Request::create('/invoices', 'GET', $params);
    }
}
