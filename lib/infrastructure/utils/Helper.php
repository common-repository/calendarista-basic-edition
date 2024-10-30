<?php
class Calendarista_Helper
{
	public static function stripTax($total, $tax)
	{
		if($total > 0 && $tax > 0){
			$result = $total - (($total / (100 + $tax)) * $tax);
			return $result;
		}
		return $total;
	}
	public static function getDiscountValue($total, $discount, $discountMode/* 0 = percentage, 1 = fixed*/){
		if($discountMode){
			return $discount;
		}
		if($total > 0 && $discount > 0){
			$result = $total / ((100 - $discount) / 100);
			return $result - $total;
		}
		return $discount;
	}
	public static function stripTaxGetDiscountValue($total, $taxPercentage, $discount, $discountMode/* 0 = percentage, 1 = fixed*/){
		if($discount > 0){
			if($discountMode){
				return $discount;
			}
			if($taxPercentage > 0){
				$total = self::stripTax($total, $taxPercentage);
			}
			if($total > 0){
				$result = $total / ((100 - $discount) / 100);
				return $result - $total;
			}
		}
		return 0;
	}
	public static function getTotalBeforeTaxAndDiscount($total, $taxPercentage, $discount, $discountMode/* 0 = percentage, 1 = fixed*/){
		if($discount > 0 && $total > 0){
			if($taxPercentage > 0){
				$total = self::stripTax($total, $taxPercentage);
			}
			if($discountMode){
				return $total - $discount;
			}
			$result = $total / ((100 - $discount) / 100);
			return $result;
		}
		return $total;
	}
	public static function timezone_string() {
		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return $timezone_string;
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return $tz_offset;
	}
	public static function reminder(){
		$bookedAvailabilityRepo = new Calendarista_BookedAvailabilityRepository();
		$projectRepo = new Calendarista_ProjectRepository();
		$projects = $projectRepo->readAll();
		//note timezone in WordPress is important. set it for accurate results.
		$timezone = get_option('timezone_string');
		$originalTimezone = null;
		if($timezone){
			$originalTimezone = Calendarista_TimeHelper::setTimezone($timezone);
		}
		foreach($projects as $project){
			if(!$project || !$project->reminder){
				continue;
			}
			$reminder = $project->reminder;
			$scheduleDate = new Calendarista_DateTime();
			$result = $bookedAvailabilityRepo->readAll(array(
				'projectId'=>$project->id
				, 'fromDate'=>$scheduleDate->format(CALENDARISTA_DATEFORMAT)
			));
			$bookedAvailabilityList = $result['resultset'];
			foreach($bookedAvailabilityList as $bookedAvailability){
				if((int)$bookedAvailability['status'] === Calendarista_AvailabilityStatus::CANCELLED){
					continue;
				}
				$orderId = (int)$bookedAvailability['orderId'];
				$appointmentBeginDate = new Calendarista_DateTime($bookedAvailability['fromDate']);
				$appointmentBeginDate->modify("-{$reminder} minutes");
				$reminderRepo = new Calendarista_RemindersRepository();
				$reminderResult = $reminderRepo->readByOrder($orderId);
				$diff = $appointmentBeginDate->diff($scheduleDate);
				$minutesToSchedule = self::getTotalMinutes($diff);
				if($reminderResult && count($reminderResult) > 0){
					continue;
				}
				if ($minutesToSchedule === 0)
				{
					$notification = new Calendarista_NotificationEmailer(array(
						'orderId'=>$orderId
						, 'emailType'=>Calendarista_EmailType::BOOKING_REMINDER
						, 'bookedAvailabilityId'=>(int)$bookedAvailability['id']
					));
					$notification->send();
					Calendarista_EmailReminderJob::sendSMS($notification);
					$reminderRepo->insert(new Calendarista_EmailReminder(array(
						'fullName'=>$notification->order->fullName
						, 'email'=>$notification->order->email
						, 'sentDate'=>$appointmentBeginDate
						, 'orderId'=>(int)$orderId
						, 'bookedAvailabilityId'=>(int)$bookedAvailability['id']
						, 'projectId'=>$notification->order->projectId
					)));
				}
			}
		}
		if($originalTimezone){
			Calendarista_TimeHelper::setTimezone($originalTimezone);
		}
	}
	public static function getTotalMinutes($interval){
		return ($interval->d * 24 * 60) + ($interval->h * 60) + $interval->i;
	}
}
?>