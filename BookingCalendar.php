<?php
require_once 'DatabaseConnection.php';

class BookingCalendar extends DatabaseConnection
{
    public function buildCalendar($month, $year)
    {
        $startOfMonth = new DateTime("$year-$month-01");
        $endOfMonth = (clone $startOfMonth)->modify('last day of this month');
        $availableSlots = [];

        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $formattedDate = $currentDate->format('Y-m-d');
            $availableSlots[$formattedDate] = 6; // Assume 6 slots per day
            $currentDate->modify('+1 day');
        }

        // Prepare the query to count booked slots
        $stmt = $this->getConnection()->prepare("SELECT date, COUNT(*) as booked_count FROM bookings WHERE MONTH(date) = ? AND YEAR(date) = ? GROUP BY date");
        $stmt->execute([$month, $year]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $date = $row['date'];
            $bookedCount = $row['booked_count'];
            $availableSlots[$date] = max(0, 6 - $bookedCount); // Subtract booked slots from total slots
        }

        return [
            'availableSlots' => $availableSlots
        ];
    }

    public function checkSlots($date)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM bookings WHERE date = ?");
        $stmt->execute([$date]);
        return $stmt->rowCount(); // Get total bookings
    }

    public function getBookings($date)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM bookings WHERE date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return all bookings
    }

    public function addBooking($name, $email, $phone, $vehicleModel, $vehicleNumber, $timeslot, $date)
    {
        $stmt = $this->getConnection()->prepare("INSERT INTO bookings (name, email, phone, vehicle_model, vehicle_number, timeslot, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $vehicleModel, $vehicleNumber, $timeslot, $date]);
    }

    public function getTimeslotStatus($date)
    {
        $timeslots = [
            "09:00AM-10:30AM",
            "10:30AM-12:00PM",
            "12:00PM-01:30PM",
            "01:30PM-03:00PM",
            "03:00PM-04:30PM",
            "04:30PM-06:00PM"
        ];

        $status = array_fill_keys($timeslots, 'available');

        $stmt = $this->getConnection()->prepare("SELECT timeslot FROM bookings WHERE date = ?");
        $stmt->execute([$date]);
        $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($bookedSlots as $timeslot) {
            $status[$timeslot] = 'booked';
        }

        return $status;
    }
}