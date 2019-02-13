<?php
require_once "session.php";
require_once "database.php";

if ($type < 1) {
	header("location: index.php");
	die();
}

function getSearchResults() {
	$con = Connection::connect();

	$stmt = $con->prepare("SELECT * FROM `UserAddressGenderJobsDegreesView` WHERE active = 1");
	$stmt->execute();
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$con = null;
	return $list;
}

function formatSearch() {
	$users = getSearchResults();
	global $type;
	if ($type == 1) {
		$tableColumns = array('country', 'state_name', 'city', 'post_code', 'first_name', 'middle_name', 'last_name', 'gender_desc', 'status', 'facebook', 'linkedin', 'twitter', 'schools', 'majors', 'degrees', 'employers', 'profession_fields');
		$tableNames = array('Country', 'State', 'City', 'Post Code', 'First Name', 'Middle Name', 'Last Name', 'Gender', 'Status', 'Facebook', 'Linkedin', 'Twitter', 'Institutions', 'Majors', 'Degrees', 'Employers', 'Professional Fields');
	} elseif ($type > 1) {
		$tableColumns = array('account_ID', 'country', 'state_name', 'city', 'post_code', 'street_address', 'street_address2', 'username', 'frozen', 'first_name', 'middle_name' ,'last_name', 'dob', 'gender_desc', 'status', 'email_address', 'phone_number', 'facebook', 'linkedin', 'twitter', 'schools', 'majors', 'degrees', 'employers', 'profession_fields', 'registration_date');
        $tableNames = array('account_ID', 'Country', 'State', 'City', 'Post Code', 'Street Address 1', 'Street Address 2', 'Username', 'Frozen', 'First Name', 'Middle Name', 'Last Name', 'Birth Date', 'Gender', 'Status', 'Email Address', 'Phone Number', 'Facebook', 'Linkedin', 'Twitter', 'Institutions', 'Majors', 'Degrees', 'Employers', 'Professional Fields', 'Registration Date');
	}
	$result = "<table id='searchResults' class='display'><thead><tr>";
	foreach($tableNames as $column) {
		$result .= "<th>" . $column . "</th>";
	}
	$result .= "</tr></thead><tbody>";
	foreach($users as $user) {
		$result .= '<tr>';
		foreach($tableColumns as $column) {
		    if ($column == 'status') {
		        $status = "Student";
		        if ($user[$column] == 1) {
                    $status = "Working Professional";
                }
                $result .= '<th><h6><a href="profile.php?user=' . $user['account_ID'] . '">' . $status . '</a></h6></th>';
            } else {
                $result .= '<th><h6><a href="profile.php?user=' . $user['account_ID'] . '">' . $user[$column] . '</a></h6></th>';
            }
		}
		$result .= '</tr>';
	}
	$result .= "</tbody>";
	$result .= "<tfoot><tf>";
	foreach($tableNames as $column) {
		$result .= "<th>" . $column . "</th>";
	}
	$result .= "</tf></tfoot>";

	$result .= '</table>';

	return $result;
}
?>
	<!-- name, email, location stuff, mentor/mentee, gender, working professional/student, phone number, username -->
<!-- template from: https://www.w3schools.com/w3css/w3css_templates.asp -->
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAConnect Admin</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>

    <script src="js/registration.js"></script>
    <script src="js/closeModals.js"></script>
	<script>
		$(document).ready(function() {
			$('#searchResults tfoot th').each( function() {
				var title = $(this).text();
				$(this).html( '<input type="text" placeholder="Search '+title+'" />' );
			} );

			//$('#searchResults tfoot tr').appendTo('#searchResults thead');

			var d = new Date($.now());
			var table = $('#searchResults').DataTable({
				dom: 'Bfrtip',
				"scrollX": true,
				buttons: [<?php if ($type >= 2) print '
					{
						extend: "csv",
						text: "Download as CSV",
						filename: "SearchResults_"+d.getDate()+"-"+(d.getMonth()+1)+"-"+d.getFullYear()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds(),
					}' ?>
				]
			});

			table.columns().every( function () {
				var that = this;

				$( 'input', this.footer() ).on( 'keyup change', function() {
					if ( that.search() !== this.value ) {
						that
							.search( this.value )
							.draw();
					}
				} );
			} );
		});
			</script>
		</head>

<body class="w3-light-grey" onload="init();">
<?php include "header.php"; ?>

<div class="w3-content w3-display-container" style="max-width:1400px;">
	<div class="w3-container w3-left"><h1 class="">Advanced Search</h1></div>
</div>
<div class="w3-content" style="max-width:1400px;">
	<div id="table_container" class="w3-container w3-card w3-white w3-padding-large">
		<?php echo formatSearch() ?>
	</div>
</div>

</body>
</html>
