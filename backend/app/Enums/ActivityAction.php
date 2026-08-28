<?php

namespace App\Enums;

enum ActivityAction: string
{
    case CREATED = 'created';
    case ASSIGNED = 'assigned';
    case STATUS_CHANGED = 'status_changed';
    case PRIORITY_CHANGED = 'priority_changed';
    case COMMENT_ADDED = 'comment_added';
    case INTERNAL_NOTE_ADDED = 'internal_note_added';
    case ATTACHMENT_UPLOADED = 'attachment_uploaded';
    case SLA_WARNING = 'sla_warning';
    case SLA_BREACHED = 'sla_breached';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
}
