<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Initial categories from the supplied counter wireframes. Existing edits are preserved.
        foreach (['Research Paper', 'Participants Permission / Compliance', 'For Book', 'Appointment Letter', 'Certification / Endorsement / Approval', 'Other Documents to Claim', 'College Organization Letter', 'Parental / Guardian Consent', 'Room Utilization / Faculty Loading', 'Other Forms', 'Change of Matriculation Form', 'Routing Form', 'Research Validation Sheet', 'Assignment of Research Personnel', 'Book Slips'] as $name) {
            DocumentType::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
        // Drawer count and labels are maintained by the administrator; no invented faculty or accounts.
    }
}
