<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('title', 10)->nullable();
            $table->string('first_name', 100);
            $table->string('surname', 100);
            $table->string('known_as', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('ni_number', 9)->nullable();
            $table->string('passport_number', 50)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('address_line_3')->nullable();
            $table->string('city_town', 100)->nullable();
            $table->string('county', 100)->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('country', 100)->default('uk');
            $table->string('primary_phone', 20)->nullable();
            $table->string('secondary_phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->enum('starter_type', ['before_tax_year', 'during_tax_year', 'dont_know'])->nullable();
            $table->date('employment_start_date')->nullable();
            $table->enum('starter_type_hmrc', ['existing', 'with_p45', 'without_p45', 'p45_later', 'seconded', 'pension', 'unknown'])->nullable();
            $table->enum('hmrc_declaration', ['option_a', 'option_b', 'option_c', 'option_d'])->nullable();
            $table->boolean('has_p45')->default(false);
            $table->enum('student_loan', ['none', 'type_1', 'type_2', 'type_4'])->default('none');
            $table->boolean('postgrad_loan')->default(false);
            $table->string('tax_code_preview')->nullable();
            $table->string('ni_category_letter', 5)->default('A');
            $table->string('job_title', 100)->nullable();
            $table->string('work_department', 100)->nullable();
            $table->string('work_hours', 50)->nullable();
            $table->string('works_number', 50)->nullable();
            $table->string('ni_number_work', 9)->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_left')->nullable();
            $table->boolean('no_employer_nic')->default(false);
            $table->boolean('exclude_nmw')->default(false);
            $table->boolean('holiday_fund_free')->default(false);
            $table->decimal('employee_widows_orphans', 10)->nullable();
            $table->date('veteran_first_day')->nullable();
            $table->boolean('off_payroll_worker')->default(false);
            $table->string('workplace_postcode', 20)->nullable();
            $table->boolean('director_flag')->default(false);
            $table->boolean('was_director')->default(false);
            $table->date('director_start_date')->nullable();
            $table->date('director_end_date')->nullable();
            $table->enum('director_nic_method', ['standard', 'alternative'])->nullable();
            $table->enum('pay_frequency', ['weekly', '2-weekly', '4-weekly', 'monthly'])->nullable();
            $table->enum('pay_method', ['bacs', 'bacs_hash', 'cash', 'cheque', 'direct_debit', 'other'])->nullable();
            $table->decimal('annual_pay', 10)->nullable();
            $table->decimal('pay_per_period', 10)->nullable();
            $table->enum('delivery_method', ['print', 'email', 'both'])->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('sort_code', 8)->nullable();
            $table->string('account_number', 8)->nullable();
            $table->string('account_name', 100)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('building_society_ref', 100)->nullable();
            $table->boolean('exclude_from_assessment')->default(false);
            $table->string('auto_enrolment_pension', 100)->nullable();
            $table->string('employee_group', 100)->nullable();
            $table->enum('assessment', ['eligible', 'non_eligible', 'entitled', 'unknown'])->nullable();
            $table->date('defer_postpone_until')->nullable();
            $table->date('date_joined')->nullable();
            $table->date('date_opted_out')->nullable();
            $table->date('date_opted_in')->nullable();
            $table->boolean('do_not_reassess')->default(false);
            $table->boolean('continue_to_assess')->default(false);
            $table->date('auto_enrolled_letter_date')->nullable();
            $table->date('not_enrolled_letter_date')->nullable();
            $table->date('postponement_letter_date')->nullable();
            $table->string('contribution_percentages', 100)->nullable();
            $table->decimal('hours_per_week', 5)->nullable();
            $table->boolean('paid_overtime')->default(false);
            $table->integer('weeks_notice')->nullable();
            $table->integer('days_sickness_full_pay')->nullable();
            $table->integer('retirement_age')->nullable();
            $table->boolean('may_join_pension')->default(false);
            $table->decimal('days_holiday_per_year', 5, 1)->nullable();
            $table->decimal('max_days_carry_over', 5, 1)->nullable();
            $table->boolean('is_archive')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
