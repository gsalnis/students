<?php
declare(strict_types=1);

namespace App\Controller;

use PhpParser\Node\Scalar\String_;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller;

class Students extends Controller\AbstractController
{
    const STUDENT_ID = 'studentId';
    const STUDENT_FIRST_NAME = 'firstName';
    const STUDENT_LAST_NAME = 'lastName';
    const STUDENT_FULL_NAME = 'fullName';
    const UNIVERSITY_ID = 'universityId';
    const UNIVERSITY_NAME = 'univeristyName';

    const SUBJECT_ID = 'subjectId';
    const MARK = 'mark';
    const SUBJECT_NAME = 'subjectName';

    const MARK_AVERAGE = 'markAverage';

    /**
     * @Route("/")
     * @Method({"GET"})
     */

    public function getStudentsMarksAverage()
    {
        $students = $this->getStudentsInfo();
echo "<pre>";
        $allStudentsAverages = [];

        foreach ($students as $student) {
            $studentMarks = $this->getStudentMarks((int)$student[self::STUDENT_ID]);

            $studentAverages = $this->getStudentMarksAverageBySubject($studentMarks);

            $allStudentsAverages = array_merge($allStudentsAverages, $studentAverages);
        }

        $normalizedData = $this->normalizeData($allStudentsAverages, $students);
//print_r($normalizedData);exit;
        return $this->render('students/index.html.twig', array('normalizedData' => $normalizedData));
    }

    private function getStudentsInfo(): array
    {
        $conn = $this->getDoctrine()->getConnection();

        $sql = '
            SELECT
                s.id as ' . self::STUDENT_ID . ',
                s.university_id as ' . self::UNIVERSITY_ID. ',
                s.first_name as ' . self::STUDENT_FIRST_NAME . ',
                s.last_name as ' . self::STUDENT_LAST_NAME . ',
                u.name as ' . self::UNIVERSITY_NAME . '
            FROM 
                marks.student s
                INNER JOIN marks.university u
                    ON s.university_id = u.id
            ;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function getStudentMarks(int $studentId): array
    {
        $conn = $this->getDoctrine()->getConnection();

        $sql = '
            SELECT
                m.student_id as ' . self::STUDENT_ID . ',
                m.mark as ' . self::MARK . ',
                m.subject_id as ' . self::SUBJECT_ID . ',
                s.name as ' . self::SUBJECT_NAME . '
            FROM 
                marks.mark m
                INNER JOIN marks.subject s
                    ON m.subject_id = s.id
            WHERE
                m.student_id = :studentId
            ;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('studentId' => $studentId));

        return $stmt->fetchAll();
    }

    private function getAllSubjects(): array
    {
        $conn = $this->getDoctrine()->getConnection();

        $sql = '
            SELECT name as ' . self::SUBJECT_NAME . ' FROM marks.subject;
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function getStudentMarksAverageBySubject(array $marks): array
    {
        $marksAverage = [];

        $subjects = array_unique(array_column($marks, self::SUBJECT_ID));

        foreach ($subjects as $subject) {
            $subjectMarks = $this->multiArraySearch($marks, $subject, self::SUBJECT_ID);
            $marksCount = count($subjectMarks);
            $marksSum = array_sum(array_column($subjectMarks, self::MARK));

            $marksAverage[] = [
                self::STUDENT_ID => $subjectMarks[0][self::STUDENT_ID],
                self::SUBJECT_NAME => $subjectMarks[0][self::SUBJECT_NAME],
                self::MARK_AVERAGE => number_format((float)$marksSum / $marksCount, 1, ',')
            ];
        }

        return $marksAverage;
    }

    private function multiArraySearch(array $array, string $search, $elementKey)
    {
        $elements = array_column($array, $elementKey);
        $keys     = array_keys($elements, $search);
        $values   = array_flip($keys);

        $filteredArray = array_intersect_key($array, $values);

        return array_values($filteredArray);
    }

    private function normalizeData(array $dataToNormalize, array $students)
    {
        $allSubjects = $this->getAllSubjects();


        $header = [];

        foreach ($allSubjects as $subject) {
            $header[] = $subject[self::SUBJECT_NAME];
        }

        $header = [
            self::UNIVERSITY_NAME => 'Universiteto pavadinimas',
            self::STUDENT_FULL_NAME => 'Vardas Pavardė'
            ] + $header;

        $normalizedData = [];

        foreach ($students as $student) {
            $normalizedData[$student[self::STUDENT_ID]] = [
                self::UNIVERSITY_NAME => $student[self::UNIVERSITY_NAME],
                self::STUDENT_FULL_NAME => $student[self::STUDENT_FIRST_NAME] . ' ' . $student[self::STUDENT_LAST_NAME],
            ];

            foreach ($allSubjects as $subject) {
                $studentMarks = $this->multiArraySearch(
                    $dataToNormalize,
                    $student[self::STUDENT_ID],
                    self::STUDENT_ID
                );

                $subjectAverage = $this->multiArraySearch(
                    $studentMarks,
                    $subject[self::SUBJECT_NAME],
                    self::SUBJECT_NAME
                );
                $normalizedData[$student[self::STUDENT_ID]][] = $subjectAverage[0][self::MARK_AVERAGE];
            }
        }

         array_unshift($normalizedData, $header);

         return $normalizedData;
    }
}
