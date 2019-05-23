<?php

namespace App\DataFixtures;

use App\Entity\Training\Answer;
use App\Entity\Training\Attempt;
use App\Entity\Training\Course;
use App\Entity\Training\Lesson;
use App\Entity\Training\Question;
use App\Entity\Training\Test;
use App\Entity\Workflow;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class MailTemplateFixtures
 *
 * @package App\DataFixtures
 */
class TrainingFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * TrainingFixtures constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($index = 0; $index < 5; $index++) {
            $course = new Course();
            $properties = [
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText(),
                'workflow' => $this->getWorkflow($manager)
            ];

            foreach ($properties as $property => $value) {
                $course->{'set' . ucfirst($property)}($value);
            }

            $this->setLessons($manager, $course);
            $this->setTests($manager, $course);

            $manager->persist($course);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            WorkflowStatusFixtures::class,
            UserFixtures::class
        ];
    }

    /**
     * @param ObjectManager $manager
     * @param Course $course
     */
    private function setLessons(ObjectManager $manager, Course $course)
    {
        for ($index = 0; $index < 5; $index++) {
            $lesson = new Lesson();
            $properties = [
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText(),
                'workflow' => $this->getWorkflow($manager)
            ];

            foreach ($properties as $property => $value) {
                $lesson->{'set'.ucfirst($property)}($value);
            }

            $this->setQuestions($manager, $lesson);

            $course->addLesson($lesson);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param Lesson $lesson
     */
    private function setQuestions(ObjectManager $manager, Lesson $lesson)
    {
        for ($index = 0; $index < 5; $index++) {
            $question = new Question();
            $properties = [
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText(),
                'type' => Question::TYPE_MULTIPLE,
                'workflow' => $this->getWorkflow($manager)
            ];

            foreach ($properties as $property => $value) {
                $question->{'set'.ucfirst($property)}($value);
            }

            $this->setAnswers($question);

            $lesson->addQuestion($question);
        }
    }

    /**
     * @param Question $question
     */
    private function setAnswers(Question $question)
    {
        for ($index = 0; $index < 5; $index++) {
            $answer = new Answer();
            $properties = [
                'answer' => $this->faker->text(50),
                'isValid' => $this->faker->boolean()
            ];

            foreach ($properties as $property => $value) {
                $answer->{'set'.ucfirst($property)}($value);
            }

            $question->addAnswer($answer);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param Course $course
     */
    private function setTests(ObjectManager $manager, Course $course)
    {
        for ($index = 0; $index < 5; $index++) {
            $test = new Test();
            $properties = [
                'title' => $this->faker->text(50),
                'description' => $this->faker->realText(),
                'workflow' => $this->getWorkflow($manager)
            ];

            foreach ($properties as $property => $value) {
                $test->{'set'.ucfirst($property)}($value);
            }

            $course->addTest($test);
            $this->setAttempts($manager, $test);
        }
    }

    /**
     * @param ObjectManager $manager
     * @param Test $test
     */
    private function setAttempts(ObjectManager $manager, Test $test)
    {
        for ($index = 0; $index < 5; $index++) {
            $attempt = new Attempt();
            $date = $this->faker->dateTimeThisMonth;
            $properties = [
                'test' => $test,
                'user' => $manager->getRepository('App:User\User')->findOneByEmail('guest.user@email.com'),
                'isValid' => $this->faker->boolean(),
                'startedAt' => $date,
                'endedAt' => (new \DateTime())->setTimestamp(strtotime('+1 hour', $date->getTimestamp()))
            ];

            foreach ($properties as $property => $value) {
                $attempt->{'set'.ucfirst($property)}($value);
            }

            $test->addAttempt($attempt);
        }
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Workflow
     */
    private function getWorkflow(ObjectManager $manager)
    {
        $workflow = new Workflow();
        $workflow->setIsDeleted(false);
        $workflow->setStatus(
            $manager
                ->getRepository('App:WorkflowStatus')
                ->findOneBy(['isDefault' => true])
        );

        return $workflow;
    }
}