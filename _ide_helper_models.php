<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLead> $eduLeads
 * @property-read int|null $edu_leads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Branch active()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch query()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereUpdatedAt($value)
 */
	class Branch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $programme_id
 * @property string $name
 * @property string|null $country
 * @property string|null $duration
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLead> $eduLeads
 * @property-read int|null $edu_leads_count
 * @property-read \App\Models\Programme|null $programme
 * @method static \Illuminate\Database\Eloquent\Builder|Course active()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereProgrammeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 */
	class Course extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $edu_lead_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $call_datetime
 * @property string $call_status
 * @property string|null $interest_level
 * @property string|null $remarks
 * @property string|null $next_action
 * @property \Illuminate\Support\Carbon|null $followup_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\EduLead $eduLead
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereCallDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereCallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereEduLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereFollowupDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereInterestLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereNextAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduCallLog withoutTrashed()
 */
	class EduCallLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $lead_code
 * @property int $created_by
 * @property int|null $assigned_to
 * @property int $lead_source_id
 * @property int|null $course_id
 * @property string $name
 * @property string|null $email
 * @property string $phone
 * @property string|null $whatsapp_number
 * @property string|null $description
 * @property string|null $course_interested
 * @property string|null $country
 * @property string|null $college
 * @property string|null $college_department
 * @property string|null $institution_type
 * @property string|null $school
 * @property string|null $school_department
 * @property \Illuminate\Support\Carbon|null $call_date
 * @property string|null $call_status
 * @property string|null $interest_level
 * @property \Illuminate\Support\Carbon|null $followup_date
 * @property string $followup_status
 * @property string|null $remarks
 * @property string|null $next_action
 * @property string $final_status
 * @property \Illuminate\Support\Carbon|null $admitted_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $branch_id
 * @property-read \App\Models\User|null $assignedTo
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduCallLog> $callLogs
 * @property-read int|null $call_logs_count
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadFollowup> $followups
 * @property-read int|null $followups_count
 * @property-read string $institution_summary
 * @property-read string $institution_type_badge
 * @property-read string $interest_level_badge
 * @property-read string $program_label
 * @property-read string $status_badge
 * @property-read \App\Models\EduLeadSource $leadSource
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadNote> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadStatusHistory> $statusHistory
 * @property-read int|null $status_history_count
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead admitted()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead cold()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead fromCollege()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead fromSchool()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead hot()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead notInterested()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead pending()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead query()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead warm()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereAdmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCallDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCollege($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCollegeDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCourseInterested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereFinalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereFollowupDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereFollowupStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereInstitutionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereInterestLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereLeadCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereLeadSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereNextAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereSchoolDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead whereWhatsappNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLead withoutTrashed()
 */
	class EduLead extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $edu_lead_id
 * @property int $assigned_to
 * @property \Illuminate\Support\Carbon $followup_date
 * @property string|null $followup_time
 * @property string $priority
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $assignedToUser
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\EduLead $eduLead
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup completed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup overdue()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup pending()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup query()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup today()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereEduLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereFollowupDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereFollowupTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadFollowup withoutTrashed()
 */
	class EduLeadFollowup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $filename
 * @property int $total_rows
 * @property int $processed_rows
 * @property int $successful_rows
 * @property int $failed_rows
 * @property string $status
 * @property array|null $errors
 * @property array|null $failed_rows_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport completed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport failed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport processing()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport query()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereFailedRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereFailedRowsData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereProcessedRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereSuccessfulRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereTotalRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadImport whereUserId($value)
 */
	class EduLeadImport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $edu_lead_id
 * @property int $created_by
 * @property string $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\EduLead $eduLead
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereEduLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadNote withoutTrashed()
 */
	class EduLeadNote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLead> $eduLeads
 * @property-read int|null $edu_leads_count
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource active()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource query()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadSource whereUpdatedAt($value)
 */
	class EduLeadSource extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\EduLead|null $eduLead
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadStatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadStatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EduLeadStatusHistory query()
 */
	class EduLeadStatusHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course> $courses
 * @property-read int|null $courses_count
 * @method static \Illuminate\Database\Eloquent\Builder|Programme active()
 * @method static \Illuminate\Database\Eloquent\Builder|Programme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Programme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Programme query()
 * @method static \Illuminate\Database\Eloquent\Builder|Programme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Programme whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Programme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Programme whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Programme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Programme whereUpdatedAt($value)
 */
	class Programme extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property int|null $branch_id
 * @property string $role
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $remember_token
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadFollowup> $assignedEduFollowups
 * @property-read int|null $assigned_edu_followups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLead> $assignedEduLeads
 * @property-read int|null $assigned_edu_leads_count
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadFollowup> $createdEduFollowups
 * @property-read int|null $created_edu_followups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLead> $createdEduLeads
 * @property-read int|null $created_edu_leads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduCallLog> $eduCallLogs
 * @property-read int|null $edu_call_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadImport> $eduLeadImports
 * @property-read int|null $edu_lead_imports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadNote> $eduLeadNotes
 * @property-read int|null $edu_lead_notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EduLeadStatusHistory> $eduLeadStatusChanges
 * @property-read int|null $edu_lead_status_changes_count
 * @property-read string $role_label
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|User active()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User inBranch($id)
 * @method static \Illuminate\Database\Eloquent\Builder|User leadManagers()
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

