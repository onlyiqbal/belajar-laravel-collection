<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\LazyCollection;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1, 2, 3, 4, 5]);

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5], $collection->all());
    }

    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            $this->assertEquals($key + 1, $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
    }

    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEquals([2, 4, 6], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(['Iqbal']);
        $result = $collection->mapInto(Person::class);
        $this->assertEquals([new Person('Iqbal')], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect([
            ['Iqbal', 'Menggala'],
            ['Budi', 'Sentosa']
        ]);

        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = $firstName . ' ' . $lastName;
            return new Person($fullName);
        });

        $this->assertEquals([new Person('Iqbal Menggala'), new Person('Budi Sentosa')], $result->all());
    }

    public function testMapToGroups()
    {
        $collection = collect([
            [
                'name' => 'iqbal',
                'department' => 'IT'
            ],
            [
                'name' => 'menggala',
                'department' => 'IT'
            ],
            [
                'name' => 'budi',
                'department' => 'HR'
            ]
        ]);

        $result = $collection->mapToGroups(function ($person) {
            return [
                $person['department'] => $person['name']
            ];
        });

        $this->assertEquals([
            'IT' => collect(['iqbal', 'menggala']),
            'HR' => collect(['budi'])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6]),
        ], $collection3->all());
    }

    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection3->all());
    }

    public function testCombine()
    {
        $collection1 = collect(['name', 'country']);
        $collection2 = collect(['iqbal', 'indonesia']);
        $collection3 = $collection1->combine($collection2);

        $this->assertEquals([
            'name' => 'iqbal',
            'country' => 'indonesia'
        ], $collection3->all());
    }

    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);
        $result = $collection->collapse();

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }

    public function testFlatMap()
    {
        $collection = collect([
            [
                'name' => 'iqbal',
                'hobbies' => ['coding', 'gaming']
            ],
            [
                'name' => 'menggala',
                'hobbies' => ['reading', 'writeing']
            ]
        ]);

        $result = $collection->flatMap(function ($item) {
            $hobbies = $item['hobbies'];
            return $hobbies;
        });

        $this->assertEqualsCanonicalizing(['coding', 'gaming', 'reading', 'writeing'], $result->all());
    }

    public function testStringRepresentation()
    {
        $collection = collect(['iqbal', 'maulana', 'menggala']);

        $this->assertEquals('iqbal-maulana-menggala', $collection->join('-'));
        $this->assertEquals('iqbal-maulana_menggala', $collection->join('-', '_'));
        $this->assertEquals('iqbal, maulana and menggala', $collection->join(', ', ' and '));
    }

    public function testFilter()
    {
        $collection = collect([
            'iqbal' => 100,
            'budi' => 80,
            'eko' => 90
        ]);

        $result = $collection->filter(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            'iqbal' => 100,
            'eko' => 90
        ], $result->all());
    }

    public function testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collection->filter(function ($value, $key) {
            return $value % 2 == 0;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());
    }

    public function testPatition()
    {
        $collection = collect([
            'iqbal' => 100,
            'budi' => 80,
            'eko' => 90
        ]);

        [$result1, $result2] = $collection->partition(function ($value, $key) {
            return $value >= 90;
        });

        $this->assertEquals([
            'iqbal' => 100,
            'eko' => 90
        ], $result1->all());

        $this->assertEquals([
            'budi' => 80,
        ], $result2->all());
    }

    public function testTesting()
    {
        $collection = collect(['iqbal', 'maulana', 'menggala']);

        $this->assertTrue($collection->contains('iqbal'));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == 'maulana';
        }));
    }

    public function testGrouping()
    {
        $collection = collect([
            [
                "name" => "iqbal",
                "department" => "IT"
            ],
            [
                "name" => "menggala",
                "department" => "IT"
            ],
            [
                "name" => "budi",
                "department" => "HR"
            ]
        ]);

        $result = $collection->groupBy('department');

        $this->assertEquals([
            'IT' => collect([
                [
                    "name" => "iqbal",
                    "department" => "IT"
                ],
                [
                    "name" => "menggala",
                    "department" => "IT"
                ]
            ]),
            'HR' => collect([
                [
                    "name" => "budi",
                    "department" => "HR"
                ]
            ])
        ], $result->all());

        $result = $collection->groupBy(function ($value, $key) {
            return strtolower($value['department']);
        });

        $this->assertEquals([
            'it' => collect([
                [
                    "name" => "iqbal",
                    "department" => "IT"
                ],
                [
                    "name" => "menggala",
                    "department" => "IT"
                ]
            ]),
            'hr' => collect([
                [
                    "name" => "budi",
                    "department" => "HR"
                ]
            ])
        ], $result->all());
    }

    public function testSlicing()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->slice(3);

        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->slice(3, 2);

        $this->assertEqualsCanonicalizing([4, 5], $result->all());
    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->take(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->skip(3);

        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipUntil(function ($value, $key) {
            return $value == 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($value, $key) {
            return $value < 3;
        });
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());
    }

    public function testChunk()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $colection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $colection->first();

        $this->assertEquals(1, $result);

        $result = $colection->first(function ($value, $key) {
            return $value > 5;
        });
        $this->assertEquals(6, $result);

    }

    public function testLast()
    {
        $colection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $colection->last();

        $this->assertEquals(9, $result);

        $result = $colection->last(function ($value, $key) {
            return $value < 5;
        });
        $this->assertEquals(4, $result);

    }

    public function testRandom()
    {
        $colection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $colection->random();

        $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
    }

    public function testCheckingExistance()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(3));
        $this->assertFalse($collection->contains(10));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == 5;
        }));
    }

    public function testOrdering()
    {
        $collection = collect([1, 3, 2, 4, 6, 5, 7, 9, 8]);
        $result = $collection->sort();

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collection->sortDesc();
        $this->assertEqualsCanonicalizing([9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());
    }

    public function testAggregate()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->sum();

        $this->assertEquals(45, $result);

        $result = $collection->avg();
        $this->assertEquals(5, $result);

        $result = $collection->min();
        $this->assertEquals(1, $result);

        $result = $collection->max();
        $this->assertEquals(9, $result);
    }

    public function testReduce()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->reduce(function ($carrry, $item) {
            return $carrry + $item;
        });

        $this->assertEquals(45, $result);
    }

    public function testLazyCollection()
    {
        $collection = LazyCollection::make(function () {
            $value = 0;
            while (true) {
                yield $value;
                $value++;
            }
        });
        $result = $collection->take(10);

        $this->assertEqualsCanonicalizing([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }
}