<?php

namespace App\Enums;

enum JobStatus: string
{
    case WAITING_FOR_CHECKIN = 'waiting_for_checkin';
    case CHECKED_IN = 'checked_in';
    case INSPECTION_PENDING = 'inspection_pending';
    case INSPECTION_COMPLETED = 'inspection_completed';
    case CUSTOMER_APPROVAL_PENDING = 'customer_approval_pending';
    case APPROVED = 'approved';
    case WAITING_FOR_PARTS = 'waiting_for_parts';
    case IN_SERVICE = 'in_service';
    case QUALITY_CHECK = 'quality_check';
    case READY_FOR_PAYMENT = 'ready_for_payment';
    case PAID = 'paid';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';

    public function getLabel(): string
    {
        return match($this) {
            self::WAITING_FOR_CHECKIN => 'Waiting for Check-in',
            self::CHECKED_IN => 'Checked In',
            self::INSPECTION_PENDING => 'Inspection Pending',
            self::INSPECTION_COMPLETED => 'Inspection Completed',
            self::CUSTOMER_APPROVAL_PENDING => 'Waiting Approval',
            self::APPROVED => 'Approved',
            self::WAITING_FOR_PARTS => 'Waiting for Parts',
            self::IN_SERVICE => 'In Service',
            self::QUALITY_CHECK => 'Quality Check',
            self::READY_FOR_PAYMENT => 'Ready for Payment',
            self::PAID => 'Paid',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::ON_HOLD => 'On Hold',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::WAITING_FOR_CHECKIN, self::INSPECTION_PENDING, self::CUSTOMER_APPROVAL_PENDING => 'yellow',
            self::CHECKED_IN, self::APPROVED, self::IN_SERVICE => 'blue',
            self::INSPECTION_COMPLETED, self::QUALITY_CHECK => 'purple',
            self::WAITING_FOR_PARTS, self::ON_HOLD => 'orange',
            self::READY_FOR_PAYMENT => 'cyan',
            self::PAID, self::DELIVERED => 'green',
            self::CANCELLED => 'red',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        $transitions = [
            self::WAITING_FOR_CHECKIN->value => [self::CHECKED_IN, self::CANCELLED],
            self::CHECKED_IN->value => [self::INSPECTION_PENDING, self::IN_SERVICE, self::CANCELLED, self::ON_HOLD],
            self::INSPECTION_PENDING->value => [self::INSPECTION_COMPLETED, self::CANCELLED, self::ON_HOLD],
            self::INSPECTION_COMPLETED->value => [self::CUSTOMER_APPROVAL_PENDING, self::APPROVED, self::ON_HOLD],
            self::CUSTOMER_APPROVAL_PENDING->value => [self::APPROVED, self::ON_HOLD, self::CANCELLED],
            self::APPROVED->value => [self::WAITING_FOR_PARTS, self::IN_SERVICE, self::ON_HOLD],
            self::WAITING_FOR_PARTS->value => [self::IN_SERVICE, self::ON_HOLD, self::CANCELLED],
            self::IN_SERVICE->value => [self::QUALITY_CHECK, self::ON_HOLD],
            self::QUALITY_CHECK->value => [self::READY_FOR_PAYMENT, self::IN_SERVICE],
            self::READY_FOR_PAYMENT->value => [self::PAID], // Cashier can mark as paid
            self::PAID->value => [self::DELIVERED],
            self::DELIVERED->value => [],
            self::CANCELLED->value => [],
            self::ON_HOLD->value => [self::CHECKED_IN, self::INSPECTION_COMPLETED, self::APPROVED, self::IN_SERVICE, self::WAITING_FOR_PARTS, self::CANCELLED],
        ];

        return in_array($status, $transitions[$this->value] ?? []);
    }

    public function getAvailableTransitions(): array
    {
        $transitions = [
            self::WAITING_FOR_CHECKIN->value => [self::CHECKED_IN, self::CANCELLED],
            self::CHECKED_IN->value => [self::INSPECTION_PENDING, self::IN_SERVICE, self::CANCELLED, self::ON_HOLD],
            self::INSPECTION_PENDING->value => [self::INSPECTION_COMPLETED, self::CANCELLED, self::ON_HOLD],
            self::INSPECTION_COMPLETED->value => [self::CUSTOMER_APPROVAL_PENDING, self::APPROVED, self::ON_HOLD],
            self::CUSTOMER_APPROVAL_PENDING->value => [self::APPROVED, self::ON_HOLD, self::CANCELLED],
            self::APPROVED->value => [self::WAITING_FOR_PARTS, self::IN_SERVICE, self::ON_HOLD],
            self::WAITING_FOR_PARTS->value => [self::IN_SERVICE, self::ON_HOLD, self::CANCELLED],
            self::IN_SERVICE->value => [self::QUALITY_CHECK, self::ON_HOLD],
            self::QUALITY_CHECK->value => [self::READY_FOR_PAYMENT, self::IN_SERVICE],
            self::READY_FOR_PAYMENT->value => [self::PAID], // Cashier can mark as paid
            self::PAID->value => [self::DELIVERED],
            self::DELIVERED->value => [],
            self::CANCELLED->value => [],
            self::ON_HOLD->value => [self::CHECKED_IN, self::INSPECTION_COMPLETED, self::APPROVED, self::IN_SERVICE, self::WAITING_FOR_PARTS, self::CANCELLED],
        ];

        return $transitions[$this->value] ?? [];
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED]);
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::DELIVERED, self::CANCELLED]);
    }

    public function getWhatsAppMessage(string $jobNumber, string $vehicleReg, string $vehicleMake, string $vehicleModel, ?string $customNotes = null): string
    {
        $vehicleInfo = "{$vehicleReg} ({$vehicleMake} {$vehicleModel})";
        $customerName = ''; // Will be added from job context

        $message = match($this) {
            self::WAITING_FOR_CHECKIN => "🚗 *Vehicle Arrived for Service*\n\n"
                . "Your vehicle {$vehicleInfo} has arrived at our facility.\n"
                . "Job Number: {$jobNumber}\n"
                . "We'll begin the check-in process shortly.",

            self::CHECKED_IN => "✅ *Vehicle Checked In Successfully*\n\n"
                . "Great news! Your vehicle {$vehicleInfo} has been checked in.\n"
                . "Job Number: {$jobNumber}\n"
                . "Our team is now preparing for the inspection process.",

            self::INSPECTION_PENDING => "🔍 *Inspection Scheduled*\n\n"
                . "Your vehicle {$vehicleInfo} is scheduled for inspection.\n"
                . "Job Number: {$jobNumber}\n"
                . "Our technicians will thoroughly examine your vehicle to identify any issues.",

            self::INSPECTION_COMPLETED => "🔧 *Inspection Completed*\n\n"
                . "Good news! The inspection for your vehicle {$vehicleInfo} is complete.\n"
                . "Job Number: {$jobNumber}\n"
                . "Our team has identified the necessary work and will provide you with details.",

            self::CUSTOMER_APPROVAL_PENDING => "📋 *Approval Required*\n\n"
                . "We've completed the assessment for your vehicle {$vehicleInfo}.\n"
                . "Job Number: {$jobNumber}\n"
                . "Please review the proposed work and provide your approval to proceed.",

            self::APPROVED => "✅ *Work Approved - Starting Soon*\n\n"
                . "Thank you for your approval! Your vehicle {$vehicleInfo} is now authorized for service.\n"
                . "Job Number: {$jobNumber}\n"
                . "Our team will begin working on your vehicle shortly.",

            self::WAITING_FOR_PARTS => "📦 *Parts Ordered*\n\n"
                . "We've ordered the necessary parts for your vehicle {$vehicleInfo}.\n"
                . "Job Number: {$jobNumber}\n"
                . "We'll notify you as soon as the parts arrive and work can continue.",

            self::IN_SERVICE => "🔨 *Work In Progress*\n\n"
                . "Our technicians are now actively working on your vehicle {$vehicleInfo}.\n"
                . "Job Number: {$jobNumber}\n"
                . "We're making good progress and will keep you updated on the status.",

            self::QUALITY_CHECK => "✨ *Quality Check In Progress*\n\n"
                . "Your vehicle {$vehicleInfo} is undergoing our final quality inspection.\n"
                . "Job Number: {$jobNumber}\n"
                . "We're ensuring everything meets our high standards before completion.",

            self::READY_FOR_PAYMENT => "💰 *Ready for Payment & Collection*\n\n"
                . "Excellent news! Your vehicle {$vehicleInfo} service is complete.\n"
                . "Job Number: {$jobNumber}\n"
                . "Please visit our facility for payment and vehicle collection.",

            self::PAID => "💳 *Payment Received - Thank You!*\n\n"
                . "Payment received for your vehicle {$vehicleInfo} service.\n"
                . "Job Number: {$jobNumber}\n"
                . "Your vehicle is ready for collection. Please visit us to pick it up.",

            self::DELIVERED => "🎉 *Vehicle Delivered Successfully*\n\n"
                . "Your vehicle {$vehicleInfo} has been delivered back to you.\n"
                . "Job Number: {$jobNumber}\n"
                . "Thank you for choosing our service! Drive safely.",

            self::CANCELLED => "❌ *Service Cancelled*\n\n"
                . "The service for your vehicle {$vehicleInfo} has been cancelled.\n"
                . "Job Number: {$jobNumber}\n"
                . "If you have any questions, please contact our customer service.",

            self::ON_HOLD => "⏸️ *Service On Hold*\n\n"
                . "The service for your vehicle {$vehicleInfo} has been temporarily placed on hold.\n"
                . "Job Number: {$jobNumber}\n"
                . "We'll notify you when work can resume.",
        };

        if ($customNotes) {
            $message .= "\n\n*Additional Notes:* {$customNotes}";
        }

        $message .= "\n\nThank you for choosing our service!";

        return $message;
    }
}
