<?php
	require_once('lib/common.php');


	if(user_is_not_logged_in())
		header('location:login.php') || die(); //user not logged in

	if(user_has_completed_the_survey())
		header('location:questions.php') || die(); //questionario già inviato

	if(!isset($_POST))
		header('location:questions.php');

	$id_user = get_user_id();

	$answers_list = array();
	foreach($_POST as $id_question => $id_answer)
	{
		if(is_array($id_answer))
			foreach($id_answer as $id)
				$answers_list[] = $id;
		else
			$answers_list[] = $id_answer;
	}

	if(count($answers_list) == 0)
		header('location:questions.php');

	//filtering out invalid answers
	$n = count($answers_list);

	$question_marks_string = build_question_marks_string($n);

	$query = "SELECT answers.id_answer
			  FROM answers, questions
			  WHERE answers.id_question = questions.id_question
			  AND answers.id_answer IN ({$question_marks_string}) 
			  AND ( questions.dependency IS NULL 
			  		OR questions.dependency IN ({$question_marks_string}))";
	$types = str_repeat('i', $n * 2);
	$args = array_merge(array($query, $types), $answers_list, $answers_list);
	$result = call_user_func_array('exec_query_many_results', $args);

	disable_autocommit();

	$success = True;
	foreach($result as $row)
	{
		$answer = $row->id_answer;

		$query = 'INSERT INTO given_answers
			      (id_given_answer, id_user, id_answer)
			      VALUES
			      (DEFAULT, ?, ?)';
		$result = exec_query($query, 'ii', $id_user, $answer);	

		if($result === FALSE)
			$success = FALSE;
	}

	//user has completed the survey
	$query = 'UPDATE users
			  SET completed=1
			  WHERE id_user=?';
	$result = exec_query($query, 'i', $id_user);

	if($result === FALSE)
		$success = FALSE;
	
	$newlocation = "";
	if($success)
	{
		commit();
		$newlocation = 'completed.php';
	}
	else
	{
		rollback();	
		$newlocation = 'questions.php';
	}

	enable_autocommit();
	header("Location:$newlocation") || die();
?>