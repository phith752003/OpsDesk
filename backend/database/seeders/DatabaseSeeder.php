<?php

namespace Database\Seeders;

use App\Enums\ActivityAction;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Setting;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departments
        $deptInfra = Department::create(['name' => 'IT Infrastructure & Security', 'code' => 'IT-INFRA', 'description' => 'Core networking, servers, and security operations']);
        $deptSupport = Department::create(['name' => 'IT Desktop Support', 'code' => 'IT-DESK', 'description' => 'End-user hardware and workstation support']);
        $deptEng = Department::create(['name' => 'Software Engineering', 'code' => 'ENG', 'description' => 'Product development and technical services']);
        $deptFinance = Department::create(['name' => 'Finance & Operations', 'code' => 'FIN-OPS', 'description' => 'Accounting, payroll, and facility operations']);
        $deptHR = Department::create(['name' => 'People & Culture (HR)', 'code' => 'HR', 'description' => 'Employee onboarding and human resources']);

        // 2. SLA Policies
        $slaCritical = SlaPolicy::create([
            'name' => 'Critical Incident Policy',
            'priority' => TicketPriority::CRITICAL,
            'response_time_minutes' => 15,
            'resolution_time_minutes' => 120,
            'is_default' => true,
        ]);

        $slaHigh = SlaPolicy::create([
            'name' => 'High Priority Policy',
            'priority' => TicketPriority::HIGH,
            'response_time_minutes' => 60,
            'resolution_time_minutes' => 480,
            'is_default' => true,
        ]);

        $slaMedium = SlaPolicy::create([
            'name' => 'Standard Priority Policy',
            'priority' => TicketPriority::MEDIUM,
            'response_time_minutes' => 240,
            'resolution_time_minutes' => 1440,
            'is_default' => true,
        ]);

        $slaLow = SlaPolicy::create([
            'name' => 'Low Priority Service Request',
            'priority' => TicketPriority::LOW,
            'response_time_minutes' => 480,
            'resolution_time_minutes' => 2880,
            'is_default' => true,
        ]);

        $slaMap = [
            TicketPriority::CRITICAL->value => $slaCritical,
            TicketPriority::HIGH->value => $slaHigh,
            TicketPriority::MEDIUM->value => $slaMedium,
            TicketPriority::LOW->value => $slaLow,
        ];

        // 3. Categories
        $catNetwork = Category::create(['name' => 'Network & VPN Access', 'slug' => 'network-vpn', 'description' => 'Firewall rules, WiFi access, and remote VPN gateways']);
        $catHardware = Category::create(['name' => 'Hardware & Workstations', 'slug' => 'hardware', 'description' => 'Laptops, monitors, peripherals, and physical repair']);
        $catSoftware = Category::create(['name' => 'Software & SaaS Licensing', 'slug' => 'software-saas', 'description' => 'License provisioning, IDEs, and office tools']);
        $catIAM = Category::create(['name' => 'Security & Identity (IAM)', 'slug' => 'iam-security', 'description' => 'SSO, MFA resets, permission escalation, and account unlock']);
        $catCloud = Category::create(['name' => 'Cloud & DevOps Infrastructure', 'slug' => 'cloud-devops', 'description' => 'AWS/GCP accounts, Kubernetes clusters, and staging environments']);

        // 4. Seeded Core Role Users (Fixed credentials)
        $admin = User::create([
            'name' => 'Elena Rostova',
            'email' => 'admin@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::ADMIN,
            'department_id' => $deptInfra->id,
            'is_active' => true,
        ]);

        $agentAlex = User::create([
            'name' => 'Alex Mercer',
            'email' => 'agent@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::AGENT,
            'department_id' => $deptSupport->id,
            'is_active' => true,
        ]);

        $requesterSarah = User::create([
            'name' => 'Sarah Jenkins',
            'email' => 'requester@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::REQUESTER,
            'department_id' => $deptFinance->id,
            'is_active' => true,
        ]);

        $managerDavid = User::create([
            'name' => 'David Vance',
            'email' => 'manager@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::MANAGER,
            'department_id' => $deptSupport->id,
            'is_active' => true,
        ]);

        // Additional Agents & Requesters for realistic workload metrics
        $agentPriya = User::create([
            'name' => 'Priya Sharma',
            'email' => 'priya.sharma@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::AGENT,
            'department_id' => $deptSupport->id,
            'is_active' => true,
        ]);

        $agentMarcus = User::create([
            'name' => 'Marcus Reed',
            'email' => 'marcus.reed@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::AGENT,
            'department_id' => $deptInfra->id,
            'is_active' => true,
        ]);

        $reqMichael = User::create([
            'name' => 'Michael Chang',
            'email' => 'michael.c@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::REQUESTER,
            'department_id' => $deptEng->id,
            'is_active' => true,
        ]);

        $reqOlivia = User::create([
            'name' => 'Olivia Hayes',
            'email' => 'olivia.h@opsdesk.local',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::REQUESTER,
            'department_id' => $deptHR->id,
            'is_active' => true,
        ]);

        // 5. System Settings
        Setting::set('app_organization', 'Acme Global Operations');
        Setting::set('sla_warning_threshold_minutes', 30, 'integer');
        Setting::set('allow_public_registration', false, 'boolean');
        Setting::set('default_ticket_priority', 'medium', 'string');

        // 6. Realistic Seed Tickets
        $now = Carbon::now();

        // Ticket 1: Critical incident - currently in progress and at risk
        $t1 = Ticket::create([
            'reference_no' => 'OPS-2026-1001',
            'title' => 'EMEA VPN Gateway dropping packet traffic intermittently',
            'description' => 'Users across Frankfurt and London offices report disconnection every 5 to 10 minutes when transmitting over the IPsec tunnel. Critical incident impacting regional sales operations.',
            'priority' => TicketPriority::CRITICAL,
            'status' => TicketStatus::IN_PROGRESS,
            'category_id' => $catNetwork->id,
            'requester_id' => $reqMichael->id,
            'agent_id' => $agentAlex->id,
            'sla_policy_id' => $slaCritical->id,
            'response_due_at' => $now->copy()->subMinutes(20),
            'responded_at' => $now->copy()->subMinutes(25),
            'resolution_due_at' => $now->copy()->addMinutes(22), // <30m remaining => At Risk!
            'is_sla_breached' => false,
            'created_at' => $now->copy()->subMinutes(40),
        ]);

        Comment::create([
            'ticket_id' => $t1->id,
            'user_id' => $agentAlex->id,
            'body' => 'Investigating edge router logs. Seeing tunnel renegotiation spikes on gateway eu-west-core.',
            'is_internal' => false,
            'created_at' => $now->copy()->subMinutes(25),
        ]);

        Comment::create([
            'ticket_id' => $t1->id,
            'user_id' => $agentAlex->id,
            'body' => 'INTERNAL NOTE: Found IKEv2 lifetime parameter mismatch between secondary firewall and ISP lease. Rolling back staging firmware update.',
            'is_internal' => true,
            'created_at' => $now->copy()->subMinutes(12),
        ]);

        ActivityLog::create([
            'ticket_id' => $t1->id,
            'user_id' => $reqMichael->id,
            'action' => ActivityAction::CREATED,
            'metadata' => ['priority' => 'critical'],
            'created_at' => $now->copy()->subMinutes(40),
        ]);

        ActivityLog::create([
            'ticket_id' => $t1->id,
            'user_id' => $admin->id,
            'action' => ActivityAction::ASSIGNED,
            'metadata' => ['agent' => 'Alex Mercer'],
            'created_at' => $now->copy()->subMinutes(30),
        ]);

        ActivityLog::create([
            'ticket_id' => $t1->id,
            'user_id' => $agentAlex->id,
            'action' => ActivityAction::STATUS_CHANGED,
            'metadata' => ['from' => 'assigned', 'to' => 'in_progress'],
            'created_at' => $now->copy()->subMinutes(25),
        ]);

        // Ticket 2: High priority - Open & unassigned
        $t2 = Ticket::create([
            'reference_no' => 'OPS-2026-1002',
            'title' => 'Okta SSO provisioning failure for incoming engineering batch',
            'description' => 'Four new hires starting in DevOps today cannot access GitHub Enterprise or AWS staging consoles. SCIM provisioning failed with error 409.',
            'priority' => TicketPriority::HIGH,
            'status' => TicketStatus::OPEN,
            'category_id' => $catIAM->id,
            'requester_id' => $olivia = $reqOlivia->id,
            'agent_id' => null,
            'sla_policy_id' => $slaHigh->id,
            'response_due_at' => $now->copy()->addMinutes(18), // Response SLA at risk!
            'responded_at' => null,
            'resolution_due_at' => $now->copy()->addHours(6),
            'is_sla_breached' => false,
            'created_at' => $now->copy()->subMinutes(42),
        ]);

        ActivityLog::create([
            'ticket_id' => $t2->id,
            'user_id' => $reqOlivia->id,
            'action' => ActivityAction::CREATED,
            'metadata' => ['priority' => 'high'],
            'created_at' => $now->copy()->subMinutes(42),
        ]);

        // Ticket 3: Medium priority - Requester Sarah Jenkins (Laptop replacement)
        $t3 = Ticket::create([
            'reference_no' => 'OPS-2026-1003',
            'title' => 'Battery swelling reported on MacBook Pro 16" (Finance)',
            'description' => 'Noticed trackpad physical clicking resistance is jammed and lower chassis is slightly bulging. Requesting emergency hardware inspection and temporary loaner.',
            'priority' => TicketPriority::MEDIUM,
            'status' => TicketStatus::ASSIGNED,
            'category_id' => $catHardware->id,
            'requester_id' => $requesterSarah->id,
            'agent_id' => $agentPriya->id,
            'sla_policy_id' => $slaMedium->id,
            'response_due_at' => $now->copy()->addHours(2),
            'responded_at' => $now->copy()->subMinutes(15),
            'resolution_due_at' => $now->copy()->addHours(18),
            'is_sla_breached' => false,
            'created_at' => $now->copy()->subHours(1),
        ]);

        Comment::create([
            'ticket_id' => $t3->id,
            'user_id' => $agentPriya->id,
            'body' => 'Hi Sarah, please power down the machine immediately and do not plug in the charger. A loaner M3 Pro has been staged at Helpdesk Kiosk B for pickup.',
            'is_internal' => false,
            'created_at' => $now->copy()->subMinutes(15),
        ]);

        // Ticket 4: Breached ticket (demonstrating SLA breach tracking)
        $t4 = Ticket::create([
            'reference_no' => 'OPS-2026-1004',
            'title' => 'Staging DB read-replica latency exceeding 4500ms',
            'description' => 'Nightly benchmark pipelines failed due to replication delay in us-east-1 staging cluster.',
            'priority' => TicketPriority::HIGH,
            'status' => TicketStatus::IN_PROGRESS,
            'category_id' => $catCloud->id,
            'requester_id' => $reqMichael->id,
            'agent_id' => $agentMarcus->id,
            'sla_policy_id' => $slaHigh->id,
            'response_due_at' => $now->copy()->subHours(10),
            'responded_at' => $now->copy()->subHours(9),
            'resolution_due_at' => $now->copy()->subHours(2), // Past resolution deadline => Breached!
            'is_sla_breached' => true,
            'created_at' => $now->copy()->subHours(12),
        ]);

        Comment::create([
            'ticket_id' => $t4->id,
            'user_id' => $agentMarcus->id,
            'body' => 'INTERNAL NOTE: Root cause is heavy full-table scan triggered by new analytics query. Optimizing indexes before clearing replication lag.',
            'is_internal' => true,
            'created_at' => $now->copy()->subHours(3),
        ]);

        // Ticket 5: Resolved ticket within SLA
        $t5 = Ticket::create([
            'reference_no' => 'OPS-2026-1005',
            'title' => 'IntelliJ IDEA Ultimate license renewal for Backend Team',
            'description' => 'Subscription expired yesterday on JetBrains Toolbox organization account.',
            'priority' => TicketPriority::LOW,
            'status' => TicketStatus::RESOLVED,
            'category_id' => $catSoftware->id,
            'requester_id' => $requesterSarah->id,
            'agent_id' => $agentAlex->id,
            'sla_policy_id' => $slaLow->id,
            'response_due_at' => $now->copy()->subHours(14),
            'responded_at' => $now->copy()->subHours(18),
            'resolution_due_at' => $now->copy()->subHours(2),
            'resolved_at' => $now->copy()->subHours(4), // Resolved before deadline!
            'is_sla_breached' => false,
            'created_at' => $now->copy()->subHours(24),
        ]);

        Comment::create([
            'ticket_id' => $t5->id,
            'user_id' => $agentAlex->id,
            'body' => 'Added 5 additional enterprise seats to JetBrains portal and assigned your team license pool.',
            'is_internal' => false,
            'created_at' => $now->copy()->subHours(4),
        ]);

        // Ticket 6: Closed ticket
        $t6 = Ticket::create([
            'reference_no' => 'OPS-2026-1006',
            'title' => 'Yubikey hardware token registration for production SSH',
            'description' => 'Received new hardware security key, need administrative binding in YubiHSM directory.',
            'priority' => TicketPriority::MEDIUM,
            'status' => TicketStatus::CLOSED,
            'category_id' => $catIAM->id,
            'requester_id' => $reqMichael->id,
            'agent_id' => $agentPriya->id,
            'sla_policy_id' => $slaMedium->id,
            'response_due_at' => $now->copy()->subDays(2),
            'responded_at' => $now->copy()->subDays(2),
            'resolution_due_at' => $now->copy()->subDays(1),
            'resolved_at' => $now->copy()->subDays(1)->subHours(3),
            'closed_at' => $now->copy()->subDays(1),
            'is_sla_breached' => false,
            'created_at' => $now->copy()->subDays(3),
        ]);

        // Ticket 7: Sarah Jenkins another open ticket
        $t7 = Ticket::create([
            'reference_no' => 'OPS-2026-1007',
            'title' => 'Payroll export Excel macro failing due to OneDrive sync lock',
            'description' => 'End of month report cannot acquire write lock on shared SharePoint directory.',
            'priority' => TicketPriority::HIGH,
            'status' => TicketStatus::IN_PROGRESS,
            'category_id' => $catSoftware->id,
            'requester_id' => $requesterSarah->id,
            'agent_id' => $agentAlex->id,
            'sla_policy_id' => $slaHigh->id,
            'response_due_at' => $now->copy()->addMinutes(120),
            'responded_at' => $now->copy()->subMinutes(30),
            'resolution_due_at' => $now->copy()->addHours(4),
            'is_sla_breached' => false,
            'created_at' => $now->copy()->subHours(2),
        ]);
    }
}
