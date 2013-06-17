<?php
	require_once('lib/common.php');

	if(user_is_not_logged_in()) // utente non autenticato
		header('location:login.php') || die(); 

	if(user_has_completed_the_survey()) // questionario già inviato
		header('location:questions.php') || die(); 

	if(!isset($_POST)) // nessuna risposta fornita
		header('location:questions.php');

	$id_user = get_user_id(); //ottengo codice utente

	// ...
	// omessa costruzione $answers_list, contenente le risposte date

	// filtraggio risposte invalide
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

	// inizio inserimento
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

	// salvataggio completamento questionario
	$query = 'UPDATE users
			  SET completed=1
			  WHERE id_user=?';
	$result = exec_query($query, 'i', $id_user);

	if($result === FALSE)
		$success = FALSE;
	
	$newlocation = "";
	if($success)
	{
		// nessun errore avvenuto, effettuo il commit
		commit();
		$newlocation = 'completed.php';
	}
	else
	{
		// si è verificato un errore, effettuo il rollback
		rollback();	
		$newlocation = 'questions.php';
	}

	
	enable_autocommit(); // riabilito l'autocommit

	header("Location:$newlocation") || die(); // effettuo redirect
?>