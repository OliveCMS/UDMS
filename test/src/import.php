<?php

$udms->school->student->insert(
  [
    'fname' => 'mehdi',
    'lname' => 'hosseinzade'
  ]
);
$udms->school->student->insert(
  [
    'fname' => 'mehrzad',
    'lname' => 'poureghbal'
  ]
);
$udms->school->student->insert(
  [
    'fname' => 'mohammad',
    'lname' => 'rezaei'
  ]
);
$udms->school->student->insert(
  [
    'fname' => 'abolfazl',
    'lname' => 'nazerpanah'
  ]
);
$udms->school->student->insert(
  [
    'fname' => 'alireza',
    'lname' => 'aghaeipour'
  ]
);
$t1 = $udms->school->teacher->insert(
  [
    'fname' => 'ali',
    'lname' => 'karimi'
  ]
);
$t2 = $udms->school->teacher->insert(
  [
    'fname' => 'reza',
    'lname' => 'torabi'
  ]
);
$t3 = $udms->school->teacher->insert(
  [
    'fname' => 'saeed',
    'lname' => 'mortazavi'
  ]
);
$c1 = $udms->school->course->insert(
  [
    'name' => 'ریاضی ۱'
  ]
);
$c2 = $udms->school->course->insert(
  [
    'name' => 'ریاضی ۲'
  ]
);
$c3 = $udms->school->course->insert(
  [
    'name' => 'دیفرانسیل'
  ]
);
$c4 = $udms->school->course->insert(
  [
    'name' => 'فیزیک ۱'
  ]
);
$c5 = $udms->school->course->insert(
  [
    'name' => 'فیزیک ۲'
  ]
);
$cr1 = $udms->school->course_rels->insert(
  [
    'c_id' => $udms->school->course->uidToColumn($c1, 'id'),
    'sub_id' => 0
  ]
);
$cr2 = $udms->school->course_rels->insert(
  [
    'c_id' => $udms->school->course->uidToColumn($c2, 'id'),
    'sub_id' => $udms->school->course->uidToColumn($c1, 'id')
  ]
);
$cr3 = $udms->school->course_rels->insert(
  [
    'c_id' => $udms->school->course->uidToColumn($c3, 'id'),
    'sub_id' => $udms->school->course->uidToColumn($c1, 'id')
  ]
);
$cr4 = $udms->school->course_rels->insert(
  [
    'c_id' => $udms->school->course->uidToColumn($c4, 'id'),
    'sub_id' => 0
  ]
);
$cr5 = $udms->school->course_rels->insert(
  [
    'c_id' => $udms->school->course->uidToColumn($c5, 'id'),
    'sub_id' => $udms->school->course->uidToColumn($c4, 'id')
  ]
);
$udms->school->class->insert(
  [
    't_id' => $udms->school->teacher->uidToColumn($t1, 'id'),
    'c_id' => $udms->school->course->uidToColumn($c1, 'id')
  ]
);
$udms->school->class->insert(
  [
    't_id' => $udms->school->teacher->uidToColumn($t1, 'id'),
    'c_id' => $udms->school->course->uidToColumn($c2, 'id')
  ]
);
$udms->school->class->insert(
  [
    't_id' => $udms->school->teacher->uidToColumn($t2, 'id'),
    'c_id' => $udms->school->course->uidToColumn($c3, 'id')
  ]
);
$udms->school->class->insert(
  [
    't_id' => $udms->school->teacher->uidToColumn($t3, 'id'),
    'c_id' => $udms->school->course->uidToColumn($c4, 'id')
  ]
);
$udms->school->class->insert(
  [
    't_id' => $udms->school->teacher->uidToColumn($t3, 'id'),
    'c_id' => $udms->school->course->uidToColumn($c5, 'id')
  ]
);
