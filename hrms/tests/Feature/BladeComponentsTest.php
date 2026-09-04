<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BladeComponentsTest extends TestCase
{
    public function test_badge_component_renders_correctly(): void
    {
        $rendered = Blade::render('<x-badge variant="emerald" :dot="true">Active Permanent</x-badge>');

        $this->assertStringContainsString('Active Permanent', $rendered);
        $this->assertStringContainsString('bg-emerald-50', $rendered);
        $this->assertStringContainsString('text-emerald-700', $rendered);
        $this->assertStringContainsString('animate-pulse', $rendered);
    }

    public function test_stat_card_component_renders_correctly(): void
    {
        $rendered = Blade::render('<x-stat-card title="Workforce Strength" value="1,250" icon="bx bx-group" color="indigo" change="+5%" />');

        $this->assertStringContainsString('Workforce Strength', $rendered);
        $this->assertStringContainsString('1,250', $rendered);
        $this->assertStringContainsString('bx bx-group', $rendered);
        $this->assertStringContainsString('+5%', $rendered);
    }

    public function test_button_component_renders_correctly(): void
    {
        $renderedButton = Blade::render('<x-button variant="primary" icon="bx bx-save">Save Record</x-button>');
        $this->assertStringContainsString('Save Record', $renderedButton);
        $this->assertStringContainsString('bg-indigo-600', $renderedButton);
        $this->assertStringContainsString('bx bx-save', $renderedButton);

        $renderedLink = Blade::render('<x-button href="/dashboard" variant="secondary">Go Back</x-button>');
        $this->assertStringContainsString('href="/dashboard"', $renderedLink);
        $this->assertStringContainsString('Go Back', $renderedLink);
    }

    public function test_card_component_renders_with_header_and_slot(): void
    {
        $rendered = Blade::render('<x-card title="Department Summary" subtitle="Subtext info">Card Body Content</x-card>');

        $this->assertStringContainsString('Department Summary', $rendered);
        $this->assertStringContainsString('Subtext info', $rendered);
        $this->assertStringContainsString('Card Body Content', $rendered);
    }

    public function test_alert_component_renders_with_variant_and_icon(): void
    {
        $rendered = Blade::render('<x-alert type="warning" title="Notice">Approaching threshold limit</x-alert>');

        $this->assertStringContainsString('Notice', $rendered);
        $this->assertStringContainsString('Approaching threshold limit', $rendered);
        $this->assertStringContainsString('bg-amber-50', $rendered);
    }

    public function test_input_component_renders_with_label_and_icon(): void
    {
        $rendered = Blade::render('<x-input label="Full Legal Name" name="legal_name" icon="bx bx-user" placeholder="e.g. John Doe" required />');

        $this->assertStringContainsString('Full Legal Name', $rendered);
        $this->assertStringContainsString('name="legal_name"', $rendered);
        $this->assertStringContainsString('bx bx-user', $rendered);
        $this->assertStringContainsString('required', $rendered);
    }

    public function test_modal_component_renders_with_title_and_container(): void
    {
        $rendered = Blade::render('<x-modal name="test-modal" title="Modal Window Header" subtitle="Sub text">Modal Body Content</x-modal>');

        $this->assertStringContainsString('id="modal-test-modal"', $rendered);
        $this->assertStringContainsString('Modal Window Header', $rendered);
        $this->assertStringContainsString('Sub text', $rendered);
        $this->assertStringContainsString('Modal Body Content', $rendered);
    }

    public function test_table_component_renders_with_headers_and_slot(): void
    {
        $rendered = Blade::render('<x-table :headers="[\'Code\', \'Employee Name\', [\'label\' => \'Status\', \'align\' => \'center\']]"><tr><td>EMP-001</td><td>John</td><td>Active</td></tr></x-table>');

        $this->assertStringContainsString('Code', $rendered);
        $this->assertStringContainsString('Employee Name', $rendered);
        $this->assertStringContainsString('text-center', $rendered);
        $this->assertStringContainsString('EMP-001', $rendered);
    }

    public function test_filter_toolbar_component_renders_search_and_slots(): void
    {
        $rendered = Blade::render('<x-filter-toolbar title="Personnel Search" searchPlaceholder="Find employee..." action="/employees"><x-slot:filters><div>Custom Department Filter</div></x-slot:filters></x-filter-toolbar>');

        $this->assertStringContainsString('Personnel Search', $rendered);
        $this->assertStringContainsString('Find employee...', $rendered);
        $this->assertStringContainsString('action="/employees"', $rendered);
        $this->assertStringContainsString('Custom Department Filter', $rendered);
        $this->assertStringContainsString('Apply Filters', $rendered);
    }

    public function test_select_component_without_search_renders(): void
    {
        $rendered = Blade::render('<x-select label="Choose Status" name="status"><option value="active">Active</option></x-select>');

        $this->assertStringContainsString('Choose Status', $rendered);
        $this->assertStringContainsString('name="status"', $rendered);
        $this->assertStringContainsString('Active', $rendered);
        $this->assertStringContainsString('bx-chevron-down', $rendered);
    }

    public function test_searchable_select_component_with_search_renders(): void
    {
        $options = [
            ['value' => '1', 'label' => 'Engineering', 'sub' => 'ENG'],
            ['value' => '2', 'label' => 'Marketing', 'sub' => 'MKT'],
        ];

        $rendered = Blade::render('<x-searchable-select label="Assigned Department" name="department_id" :options="$options" placeholder="Select department..." />', ['options' => $options]);

        $this->assertStringContainsString('Assigned Department', $rendered);
        $this->assertStringContainsString('Engineering', $rendered);
        $this->assertStringContainsString('Marketing', $rendered);
        $this->assertStringContainsString('Search options...', $rendered);
        $this->assertStringContainsString('name="department_id"', $rendered);
    }

    public function test_pagination_component_renders_with_per_page_limit(): void
    {
        $items = collect(range(1, 45));
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage(1, 10),
            $items->count(),
            10,
            1,
            ['path' => '/employees']
        );

        $rendered = Blade::render('<x-pagination :paginator="$paginator" />', ['paginator' => $paginator]);

        $this->assertStringContainsString('Show', $rendered);
        $this->assertStringContainsString('per page', $rendered);
        $this->assertStringContainsString('name="per_page"', $rendered);
        $this->assertStringContainsString('Showing', $rendered);
        $this->assertStringContainsString('45', $rendered);
    }

    public function test_date_picker_component_renders(): void
    {
        $rendered = Blade::render('<x-date-picker label="Joining Date" name="joining_date" value="2026-09-01" required />');

        $this->assertStringContainsString('Joining Date', $rendered);
        $this->assertStringContainsString('type="date"', $rendered);
        $this->assertStringContainsString('name="joining_date"', $rendered);
        $this->assertStringContainsString('2026-09-01', $rendered);
        $this->assertStringContainsString('bx bx-calendar', $rendered);
    }

    public function test_time_picker_component_renders(): void
    {
        $rendered = Blade::render('<x-time-picker label="Shift Start Time" name="start_time" value="09:00" required />');

        $this->assertStringContainsString('Shift Start Time', $rendered);
        $this->assertStringContainsString('type="time"', $rendered);
        $this->assertStringContainsString('name="start_time"', $rendered);
        $this->assertStringContainsString('09:00', $rendered);
        $this->assertStringContainsString('bx bx-time-five', $rendered);
    }

    public function test_datetime_picker_component_renders(): void
    {
        $rendered = Blade::render('<x-datetime-picker label="Interview Slot" name="interview_at" value="2026-09-10T14:30" />');

        $this->assertStringContainsString('Interview Slot', $rendered);
        $this->assertStringContainsString('type="datetime-local"', $rendered);
        $this->assertStringContainsString('name="interview_at"', $rendered);
        $this->assertStringContainsString('2026-09-10T14:30', $rendered);
    }

    public function test_circle_action_button_component_renders_as_button_and_link(): void
    {
        $renderedLink = Blade::render('<x-circle-action-button icon="bx bx-show" variant="indigo" size="sm" href="/profile" title="View Profile" />');
        $this->assertStringContainsString('href="/profile"', $renderedLink);
        $this->assertStringContainsString('bx bx-show', $renderedLink);
        $this->assertStringContainsString('rounded-full', $renderedLink);
        $this->assertStringContainsString('bg-indigo-50', $renderedLink);
        $this->assertStringContainsString('title="View Profile"', $renderedLink);

        $renderedBtn = Blade::render('<x-circle-action-button icon="bx bx-trash" variant="rose" size="md" type="submit" />');
        $this->assertStringContainsString('<button', $renderedBtn);
        $this->assertStringContainsString('type="submit"', $renderedBtn);
        $this->assertStringContainsString('bx bx-trash', $renderedBtn);
        $this->assertStringContainsString('bg-rose-50', $renderedBtn);
    }

    public function test_confirm_dialog_component_renders(): void
    {
        $rendered = Blade::render('<x-confirm-dialog name="delete-item" title="Delete Verification" message="Cannot be undone." confirmText="Yes, Delete" variant="danger" />');

        $this->assertStringContainsString('id="modal-confirm-delete-item"', $rendered);
        $this->assertStringContainsString('Delete Verification', $rendered);
        $this->assertStringContainsString('Cannot be undone.', $rendered);
        $this->assertStringContainsString('Yes, Delete', $rendered);
        $this->assertStringContainsString('openConfirmDialog', $rendered);
    }
}
