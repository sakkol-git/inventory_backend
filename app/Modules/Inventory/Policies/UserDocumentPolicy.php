<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Policies;

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\UserDocument;

class UserDocumentPolicy
{
    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view a specific document.
     * Users can view their own documents or admins can view any.
     */
    public function view(User $user, UserDocument $document): bool
    {
        if ($user->hasPermissionTo('documents.view', 'api')) {
            return true;
        }

        return $user->id === $document->user_id;
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('documents.create', 'api');
    }

    /**
     * Determine if the user can update a document.
     * Users can only update their own documents, or admins can update any.
     */
    public function update(User $user, UserDocument $document): bool
    {
        if ($user->hasPermissionTo('documents.edit', 'api')) {
            return true;
        }

        return $user->id === $document->user_id;
    }

    /**
     * Determine if the user can delete a document.
     * Users can only delete their own documents, or admins can delete any.
     */
    public function delete(User $user, UserDocument $document): bool
    {
        if ($user->hasPermissionTo('documents.delete', 'api')) {
            return true;
        }

        return $user->id === $document->user_id;
    }

    /**
     * Determine if the user can download a document.
     */
    public function download(User $user, UserDocument $document): bool
    {
        if ($user->hasPermissionTo('documents.view', 'api')) {
            return true;
        }

        return $user->id === $document->user_id;
    }
}
