<?php

namespace App\Repositories\CompanyModule\Interfaces;

interface CompanyRepositoryInterface
{
    /**
     * Get all companies for a user
     */
    public function getUserCompanies($userId);

    /**
     * Get a company by ID
     */
    public function find($id);

    /**
     * Get company by ID with relationships
     */
    public function findWithRelations($id, array $relations = []);

    /**
     * Create a new company
     */
    public function create(array $data);

    /**
     * Update a company
     */
    public function update($id, array $data);

    /**
     * Delete a company (soft delete)
     */
    public function delete($id);

    /**
     * Get active companies for a user
     */
    public function getActiveCompanies($userId);

    /**
     * Get companies with incomplete profiles
     */
    public function getIncompleteProfiles($userId);

    /**
     * Check if user owns a company
     */
    public function userOwnsCompany($userId, $companyId);

    /**
     * Check if user has access to company
     */
    public function userHasAccess($userId, $companyId);

    /**
     * Get company by tax ID
     */
    public function findByTaxId($taxId);

    /**
     * Update profile completion
     */
    public function updateProfileCompletion($companyId);

    /**
     * Get companies needing profile reminder
     */
    public function getCompaniesNeedingReminder();
}