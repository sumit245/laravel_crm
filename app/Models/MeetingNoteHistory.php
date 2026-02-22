<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Version history for meeting notes. Tracks changes to meeting notes over time, allowing
 * stakeholders to see how notes were edited and by whom.
 *
 * Data Flow:
 *   Meeting notes edited → Previous version stored → Change history available → Audit
 *   trail maintained
 *
 * @depends-on Meet, User
 * @business-domain Meetings & Collaboration
 * @package App\Models
 */
class MeetingNoteHistory extends Model
{
    use HasFactory;
}
