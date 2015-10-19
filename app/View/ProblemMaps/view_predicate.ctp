<?php
	/* print_r($ProblemMap);*/
	$ProblemMapId = $ProblemMap['ProblemMap'];
	// $localAddress = "C:\\Users\\mdinar\\Documents\\Research\\Creative IT\\Empirical material\\Fall 13\\D1\\Predicates\\";
	// $serverAddress = "domains/problemformulator.com/public_html/app/Predicates/";
	// $myFile = $serverAddress . strval($ProblemMapId['id']) . ".lp";
	// $fh = fopen($myFile, 'w') or die("can't open file");
	/*
	foreach($ProblemMap['Entities'] as &$e) {
		if($e['type'] == "function") {
			$stringData = "" . "fnction(" . $e['id'] . "," . substr(strtotime($e['created']),-9) . ").\n";
			echo $stringData.'<br>';
			// fwrite($fh, $stringData);
		}			
		else {
			$stringData = "" . $e['type'] . "(" . $e['id'] . "," . substr(strtotime($e['created']),-9) . ").\n";
			echo $stringData.'<br>'; 
			// fwrite($fh, $stringData);
		}
		if($e['decomposition_id'] != '')
			$stringData = "child_in(" . $e['id'] . "," . $e['decomposition_id'] . ").\n";
			echo $stringData.'<br>'; 
			// fwrite($fh, $stringData);
	}
	echo "<br><br>";*/
	
	$text_data = "";
	foreach($ProblemMap['Entities'] as &$e) {
		$newstr = preg_replace('/[^a-zA-Z0-9]/', '_', $e['name']);
		$newstr = '"'.strtolower(str_replace("'", '', $newstr)).'"';
		$stringData = "" . $e['type'] . "(" .$e['id']. ",". $newstr . "," . substr(strtotime($e['created']),-9) . ")";
		//echo $stringData.'<br>';
		$text_data .= $stringData.".\r\n";
		
		if($e['decomposition_id'] != ''){
			$stringData = "child_in(" . $e['id'] . "," . $e['decomposition_id'] . ")";
			$text_data .= $stringData.".\r\n";
			//echo $stringData.'<br>';
		}
	}

	// the third version of the tool does not have any attributes filled in
	/*
	$emparr=array(""," ");
	foreach($ProblemMap['Attributes'] as &$a) {
		$emptydesc=$a['description'];
		if ($emptydesc=="") $emptydesc="na";
		$stringData = "" . $a['type'] . "(" . $a['entity_id'] . "," . str_replace(" ","_",$emptydesc) . "," 
			. substr(strtotime($a['created']),-9)  . ")";
		echo $stringData; 
		// fwrite($fh, $stringData);
		// fwrite($fh, "\n");
	}
	*/
	
	//echo "<br>";
	foreach($ProblemMap['Links'] as &$l) {
		$stringData = "" . str_replace(" ","_",$l['type']) . "(" . $l['from_entity_id'] . "," . $l['to_entity_id']
			. "," . substr(strtotime($l['created']),-9) . ")";
		//echo $stringData.'<br>'; 
		$text_data .= $stringData.".\r\n";
		// fwrite($fh, $stringData);
	}
	foreach($ProblemMap['Decompositions'] as &$d) {
		$stringData = "parent_in(" . $d['entity_id'] . "," . $d['id'] . "," . substr(strtotime($d['created']),-9) . ")";
		//echo $stringData.'<br>'; 
		$text_data .= $stringData.".\r\n";
		// fwrite($fh, $stringData);
	}
	foreach($ProblemMap['PartialOrderings'] as &$p) {
		$stringData = "" . $p['type'] . "(" . $p['entity_id'] . "," . $p['other_entity_id'] . 
			 "," . substr(strtotime($p['created']),-9) . ")";
		//echo $stringData.'<br>'; 
		$text_data .= $stringData.".\r\n";
		// fwrite($fh, $stringData);
	}
	
	// fwrite($fh,"\n");
		//echo "<br><br>";
		
		
	App::import('Helper', 'Plaintext');
	$Plaintext = new PlaintextHelper(new View(null));
		
	$filename = $ProblemMap['ProblemMap']['id'].'.txt';
	Configure::write('debug', 0);
	$this->layout = 'ajax';
	$Plaintext->setFilename($filename);
	echo $Plaintext->render($text_data);
	
	//echo $text_data;
$stringGenralizationRules = "
entity(Any,T):-
requirement(Any,T).

entity(Any,T):-
fnction(Any,T).

entity(Any,T):-
artifact(Any,T).

entity(Any,T):-
behavior(Any,T).

entity(Any,T):-
issue(Any,T).


interrelate(Fnction,Requirement,T):-
satisfies(Fnction,Requirement,T).

interrelate(Requirement,Fnction,T):-
satisfies(Fnction,Requirement,T).

interrelate(Fnction,Artifact,T):-
realizes(Fnction,Artifact,T).

interrelate(Artifact,Fnction,T):-
realizes(Fnction,Artifact,T).

interrelate(Artifact,Requirement,T):-
fulfills(Artifact,Requirement,T).

interrelate(Requirement,Artifact,T):-
fulfills(Artifact,Requirement,T).

interrelate(Requirement,Behavior,T):-
manages(Behavior,Requirement,T).

interrelate(Requirement,Behavior,T):-
manages(Behavior,Requirement,T).

interrelate(Behavior,Artifact,T):-
parameterizes(Behavior,Artifact,T).

interrelate(Artifact,Behavior,T):-
manages(Behavior,Artifact,T).

interrelate(Issue,Any,T):-
is_related_to(Issue,Any,T).

interrelate(Any,Issue,T):-
is_related_to(Issue,Any,T).

interrelate_bi_directionally(Any,Other,T):-
interrelate(Any,Other,T).

interrelate_bi_directionally(Any,Other,T):-
interrelate(Other,Any,T).


parent_of(Ent_parent,Ent_child,Modified):-
parent_in(Ent_parent,Decomp,Modified),
child_in(Ent_child,Decomp).

parent_of(Ent_parent,Ent_grandchild,Modified_parent):-
entity(Ent_parent,T1),
entity(Ent_child,T2),
entity(Ent_grandchild,T3),
parent_of(Ent_parent,Ent_child,Modified_parent),
parent_in(Ent_parent,Decomp_parent,Modified_parent),
parent_of(Ent_child,Ent_grandchild,Modified_child),
parent_in(Ent_child,Decomp_child,Modified_child).\n";

$stringThreeStrategies = "\n
strategy(upward_abstraction,Ent_parent):-
entity(Ent_parent,T_parent),
entity(Ent_child,T_child),
parent_of(Ent_parent,Ent_child,Modified),
T_parent>T_child.


strategy(forward_processing,Requirement):-
satisfies(Fnction,Requirement,T_satisfies),
fulfills(Artifact,Requirement,T_fulfills),
manages(Behavior,Requirement,T_manages),
is_related_to(Issue,Requirement,T_relates),
T_fulfills>T_satisfies,
T_manages>T_satisfies,
T_relates>T_satisfies.

strategy(forward_processing,Requirement):-
satisfies(Fnction,Requirement,T_satisfies),
fulfills(Artifact,Requirement,T_fulfills),
manages(Behavior,Requirement,T_manages),
T_fulfills>T_satisfies,
T_manages>T_satisfies.

strategy(forward_processing,Requirement):-
satisfies(Fnction,Requirement,T_satisfies),
fulfills(Artifact,Requirement,T_fulfills),
is_related_to(Issue,Requirement,T_relates),
T_fulfills>T_satisfies,
T_relates>T_satisfies.

strategy(forward_processing,Requirement):-
satisfies(Fnction,Requirement,T_satisfies),
manages(Behavior,Requirement,T_manages),
is_related_to(Issue,Requirement,T_relates),
T_manages>T_satisfies,
T_relates>T_satisfies.

strategy(forward_processing,Requirement):- 
satisfies(Fnction,Requirement,T_satisfies),
fulfills(Artifact,Requirement,T_fulfills),
T_fulfills>T_satisfies.

strategy(forward_processing,Requirement):-
satisfies(Fnction,Requirement,T_satisfies),
manages(Behavior,Requirement,T_manages),
T_manages>T_satisfies.

strategy(forward_processing,Requirement):-
satisfies(Fnction,Requirement,T_satisfies),
is_related_to(Issue,Requirement,T_relates),
T_relates>T_satisfies.


strategy(depth_first_decomposition,Ent_parent):-
parent_of(Ent_parent,Ent_child,Modified_parent),
interrelate_bi_directionally(Ent_parent,Any,T_interrelate),
parent_in(Intermidiate_parent,Decomposition,Modified_child),
child_in(Ent_child,Decomposition),
T_interrelate>Modified_parent.


common(Ent_parent):-
strategy(depth_first_decomposition,Ent_parent),
strategy(upward_abstraction,Ent_parent).

#hide.
#show strategy(_,_).
#show common(_).";

	//echo $stringGenralizationRules;
	//echo $stringThreeStrategies;
	// fwrite($fh, $stringGenralizationRules);
	// fwrite($fh, $stringThreeStrategies);
	// fclose($fh);
?>