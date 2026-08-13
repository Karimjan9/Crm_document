<?php

namespace App\Policies;

use App\Models\DocumentsModel;
use App\Models\User;

class DocumentsPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_manager']) ? true : null;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'admin_filial']);
    }

    public function workflowViewAny(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'admin_filial', 'admin_manager', 'super_admin']);
    }

    public function workflowView(User $user, DocumentsModel $document): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return true;
        }

        if ($user->hasRole('admin_filial')) {
            return (int) $document->filial_id === (int) $user->filial_id;
        }

        return $user->hasRole('employee')
            && (int) $document->filial_id === (int) $user->filial_id
            && ((int) $document->user_id === (int) $user->id
                || (int) $document->assigned_to_id === (int) $user->id
                || (int) $document->qa_user_id === (int) $user->id);
    }

    public function workflowUpdate(User $user, DocumentsModel $document): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return true;
        }

        if ($user->hasRole('admin_filial')) {
            return (int) $document->filial_id === (int) $user->filial_id;
        }

        return $user->hasRole('employee')
            && (int) $document->filial_id === (int) $user->filial_id
            && ((int) $document->user_id === (int) $user->id
                || (int) $document->assigned_to_id === (int) $user->id
                || (int) $document->qa_user_id === (int) $user->id);
    }

    public function workflowAssign(User $user, DocumentsModel $document): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_manager', 'admin_filial'])) {
            return $user->hasAnyRole(['super_admin', 'admin_manager'])
                || (int) $document->filial_id === (int) $user->filial_id;
        }

        return $user->hasRole('employee') && (int) $document->assigned_to_id === (int) $user->id;
    }

    public function checklistUpdate(User $user, DocumentsModel $document): bool
    {
        return $this->workflowUpdate($user, $document);
    }

    public function qaReview(User $user, DocumentsModel $document): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_manager'])) {
            return true;
        }

        if ($user->hasRole('admin_filial')) {
            return (int) $document->filial_id === (int) $user->filial_id;
        }

        return $user->hasRole('employee')
            && (int) $document->filial_id === (int) $user->filial_id
            && (int) $document->qa_user_id === (int) $user->id;
    }

    public function view(User $user, DocumentsModel $document): bool
    {
        if ($user->hasRole('admin_filial')) {
            return (int) $document->filial_id === (int) $user->filial_id;
        }

        if ($user->hasRole('employee')) {
            return (int) $document->user_id === (int) $user->id;
        }

        if ($user->hasRole('courier')) {
            return (int) $document->courierAssignment?->courier_id === (int) $user->id;
        }

        return false;
    }

    public function update(User $user, DocumentsModel $document): bool
    {
        return $this->canManageBranchDocument($user, $document);
    }

    public function pay(User $user, DocumentsModel $document): bool
    {
        return $this->canManageBranchDocument($user, $document);
    }

    public function complete(User $user, DocumentsModel $document): bool
    {
        return $this->canManageBranchDocument($user, $document);
    }

    public function sendToCourier(User $user, DocumentsModel $document): bool
    {
        return $this->canManageBranchDocument($user, $document);
    }

    protected function canManageBranchDocument(User $user, DocumentsModel $document): bool
    {
        if ($user->hasRole('admin_filial')) {
            return (int) $document->filial_id === (int) $user->filial_id;
        }

        return $user->hasRole('employee')
            && (int) $document->user_id === (int) $user->id;
    }
}
