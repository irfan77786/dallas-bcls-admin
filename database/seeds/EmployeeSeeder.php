<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        Employee::insert([
            ['name' => 'ANNIEBELL MUCHINDO', 'position' => 'Call Center'],
            ['name' => 'ANGELA ZIMBA', 'position' => 'Receptionist'],
            ['name' => 'ANTHONY BANDA', 'position' => 'Receptionist'],
            ['name' => 'BRENDA SOKO', 'position' => 'Dental Assistant'],
            ['name' => 'BUUMBA HAAMUNTEKA', 'position' => 'Laboratory Technician'],
            ['name' => 'CHAMA CHILESHE', 'position' => 'Receptionist'],
            ['name' => 'CHISELA MWENYA', 'position' => 'Nurse/Midwife'],
            ['name' => 'DAVIS MKANDAWIRE', 'position' => 'Driver'],
            ['name' => 'DIANA BANDA', 'position' => 'General Worker'],
            ['name' => 'EMMA PHIRI', 'position' => 'Receptionist/ Social Worker'],
            ['name' => 'ELIZABETH TEMBO', 'position' => 'Receptionist'],
            ['name' => 'IDAH TEMBO', 'position' => 'Laboratory Technician'],
            ['name' => 'JOEL MILAMBO', 'position' => 'Laboratory Technician'],
            ['name' => 'KENNEDY MWAMBA', 'position' => 'General Worker'],
            ['name' => 'LISTER SONTWA', 'position' => 'Nurse/Midwife'],
            ['name' => 'LETISHA CHABALA', 'position' => 'Receptionist'],
            ['name' => 'Malitdah Tembo', 'position' => 'General Worker'],
            ['name' => 'MARY MBEWE', 'position' => 'Nurse/Midwife'],
            ['name' => 'MASHILI MOYO', 'position' => 'Receptionist'],
            ['name' => 'MERCY MUKELABAI', 'position' => 'Social Worker'],
            ['name' => 'NANCY CHISANGA', 'position' => 'General Worker'],
            ['name' => 'NANCY KUNSANAMA', 'position' => 'Administration Secretary'],
            ['name' => 'NASON CHANDA', 'position' => 'Guard'],
            ['name' => 'OLINESS MUDENDA', 'position' => 'Nurse/Midwife'],
            ['name' => 'OMEGA FWAMBO', 'position' => 'Pharmacist'],
            ['name' => 'PAULINE MBEBA', 'position' => 'General Worker'],
            ['name' => 'PELE BANDA', 'position' => 'Nurse/Midwife'],
            ['name' => 'PRINCE CHIRWA', 'position' => 'Guard'],
            ['name' => 'SANGU PHIRI', 'position' => 'Pharmacy Technologist'],
            ['name' => 'TENDAI NDOMA', 'position' => 'Receptionist'],
            ['name' => 'THANDIWE MWANZA', 'position' => 'Nurse/Midwife'],
            ['name' => 'THERESA MUSONDA', 'position' => 'General Worker'],
            ['name' => 'ANNE BWALYA', 'position' => 'Nurse/Midwife'],
            ['name' => 'ANGELA KAPATA', 'position' => 'Nurse/Midwife'],
            ['name' => 'CHRISTABEL MBULO', 'position' => 'Nurse/Midwife'],
            ['name' => 'CYNTHIA BWALYA', 'position' => 'Nurse/Midwife'],
            ['name' => 'DEBORAH NAYAME', 'position' => 'Nurse/Midwife'],
            ['name' => 'GLENDA KAZABU', 'position' => 'Nurse/Midwife'],
            ['name' => 'JUDITH DINGANI', 'position' => 'Nurse/Midwife'],
            ['name' => 'LAVENDAH CHIPASHA', 'position' => 'Nurse/Midwife'],
            ['name' => 'MONICA NALWAMBA', 'position' => 'Nurse/Midwife'],
            ['name' => 'RUTH MWILA MUKUPO', 'position' => 'Nurse/Midwife'],
            ['name' => 'CHARLES CHAFWILISHO', 'position' => 'Theater Nurse'],
            ['name' => 'BOYD MUYNDA', 'position' => 'Radiography'],
        ]);
    }
}
